<?php

namespace App\Http\Controllers\Api\Table;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\OrderLineStatus;
use App\Common\Constants\RestaurantTableStatus;
use App\Common\Constants\ReservationStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableSessionRequest;
use App\Http\Requests\UpdateTableSessionRequest;
use App\Models\CartOrder;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TableSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tableSessions = TableSession::query()
            ->with($this->relations())
            ->where('status', TableSessionStatus::OPEN)
            ->when(
                $request->filled('table_id'),
                fn (Builder $query) => $query->where('table_id', (int) $request->input('table_id'))
            )
            ->when(
                $request->filled('reservation_code'),
                fn (Builder $query) => $query->where('reservation_code', (string) $request->input('reservation_code'))
            )
            ->when(
                $request->has('is_active'),
                fn (Builder $query) => $query->where('is_active', $request->boolean('is_active'))
            )
            ->orderByDesc('opened_at')
            ->orderByDesc('id')
            ->get();

        return $this->success($tableSessions);
    }

    public function show(int $tableSessionId): JsonResponse
    {
        $tableSession = $this->findTableSession($tableSessionId);

        if (! $tableSession) {
            return $this->error(null, 'Không tìm thấy phiên bàn', Response::HTTP_NOT_FOUND);
        }

        return $this->success($tableSession);
    }

    public function store(StoreTableSessionRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $table = RestaurantTable::query()
            ->withComputedStatus()
            ->findOrFail($payload['table_id']);
        $reservation = $this->resolveReservation($payload['reservation_code'] ?? null);

        if ($error = $this->validateReservationForTable($table, $reservation)) {
            return $error;
        }

        if ($this->hasLiveSession($table->id)) {
            return $this->error(null, 'Bàn đang có phiên phục vụ chưa kết thúc', Response::HTTP_BAD_REQUEST);
        }

        $isValidReservedTable = $table->status === RestaurantTableStatus::RESERVED
            && $reservation !== null
            && $reservation->table_code === $table->slug;

        if ($table->status !== RestaurantTableStatus::AVAILABLE && ! $isValidReservedTable) {
            return $this->error(null, 'Bàn hiện tại không khả dụng', Response::HTTP_BAD_REQUEST);
        }

        $payload['reservation_code'] = $reservation?->reservation_code ?? null;
        $payload['status'] = TableSessionStatus::OPEN;
        $payload['opened_at'] = now();
        $payload['opened_by_employee'] = $this->getUserName();
        $payload['is_active'] = true;

        $tableSession = DB::transaction(function () use ($payload, $reservation) {
            $tableSession = TableSession::query()->create($payload);
            $this->createInitialCartOrder($tableSession);

            $this->completeReservationOnArrival($reservation);

            return $tableSession;
        });

        return $this->success(
            $tableSession->fresh()->load($this->relations()),
            'Mở phiên bàn thành công',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateTableSessionRequest $request, int $tableSessionId): JsonResponse
    {
        $tableSession = TableSession::query()
            ->with(['table', 'reservation'])
            ->find($tableSessionId);

        if (! $tableSession) {
            return $this->error(null, 'Không tìm thấy phiên bàn', Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validated();
        $shouldCancelSession = false;

        if (array_key_exists('reservation_code', $payload)) {
            $reservation = $this->resolveReservation($payload['reservation_code']);

            if ($error = $this->validateReservationForTable($tableSession->table, $reservation)) {
                return $error;
            }

            $payload['reservation_code'] = $reservation?->reservation_code;
        }

        if (array_key_exists('status', $payload)) {
            if ($error = $this->guardManualStatusUpdate($payload['status'])) {
                return $error;
            }

            if ($error = $this->validateStatusTransition($tableSession, $payload['status'])) {
                return $error;
            }

            $shouldCancelSession = $payload['status'] === TableSessionStatus::CANCELLED
                && $payload['status'] !== $tableSession->status;

            if (
                $this->isTerminalStatus($payload['status'])
                && $payload['status'] !== $tableSession->status
            ) {
                if (! $shouldCancelSession && $this->hasOpenCartOrders($tableSession)) {
                    return $this->error(
                        null,
                        'Phiên bàn vẫn còn đơn đang mở, không thể kết thúc',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $payload['closed_at'] = now();
                $payload['closed_by_employee'] = $this->getUserName();
            }
        }

        DB::transaction(function () use ($tableSession, $payload, $shouldCancelSession): void {
            $tableSession->update($payload);

            if (! $shouldCancelSession) {
                return;
            }

            $this->cancelSessionCartOrders($tableSession);
            $tableSession->load('reservation');
            $this->cancelReservation($tableSession->reservation);
        });

        return $this->success(
            $tableSession->fresh()->load($this->relations()),
            'Cập nhật phiên bàn thành công.'
        );
    }

    protected function relations(): array
    {
        return [
            'table' => fn ($query) => $query->withComputedStatus(),
            'reservation',
            'cartOrders',
            'invoices',
        ];
    }

    protected function findTableSession(int $tableSessionId): ?TableSession
    {
        return TableSession::query()
            ->with($this->relations())
            ->find($tableSessionId);
    }

    protected function resolveReservation(?string $reservationCode): ?Reservation
    {
        if (! is_string($reservationCode) || $reservationCode === '') {
            return null;
        }

        return Reservation::query()
            ->where('reservation_code', $reservationCode)
            ->first();
    }

    protected function validateReservationForTable(
        RestaurantTable $table,
        ?Reservation $reservation
    ): ?JsonResponse {
        if (! $reservation) {
            return null;
        }

        if (
            ! $reservation->is_active
            || in_array($reservation->status, [ReservationStatus::COMPLETED, ReservationStatus::CANCELED], true)
        ) {
            return $this->error(null, 'Yêu cầu đặt bàn không còn hiệu lực', Response::HTTP_BAD_REQUEST);
        }

        if ($reservation->table_code !== null && $reservation->table_code !== $table->slug) {
            return $this->error(null, 'Yêu cầu đặt bàn không thuộc bàn này', Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    protected function hasLiveSession(int $tableId, ?int $ignoreSessionId = null): bool
    {
        return TableSession::query()
            ->where('table_id', $tableId)
            ->where('is_active', true)
            ->whereIn('status', [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
            ])
            ->when(
                $ignoreSessionId,
                fn (Builder $query) => $query->where('id', '!=', $ignoreSessionId)
            )
            ->exists();
    }

    protected function validateStatusTransition(TableSession $tableSession, string $nextStatus): ?JsonResponse
    {
        $allowedTransitions = [
            TableSessionStatus::OPEN => [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
                TableSessionStatus::PAID,
                TableSessionStatus::CLOSED,
                TableSessionStatus::CANCELLED,
            ],
            TableSessionStatus::PAYMENT_PENDING => [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
                TableSessionStatus::PAID,
                TableSessionStatus::CLOSED,
                TableSessionStatus::CANCELLED,
            ],
            TableSessionStatus::PAID => [
                TableSessionStatus::PAID,
                TableSessionStatus::CLOSED,
            ],
            TableSessionStatus::CLOSED => [
                TableSessionStatus::CLOSED,
            ],
            TableSessionStatus::CANCELLED => [
                TableSessionStatus::CANCELLED,
            ],
        ];

        if (! in_array($nextStatus, $allowedTransitions[$tableSession->status] ?? [], true)) {
            return $this->error(null, 'Không thể chuyển trạng thái phiên bàn', Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    protected function isTerminalStatus(string $status): bool
    {
        return in_array($status, [
            TableSessionStatus::PAID,
            TableSessionStatus::CLOSED,
            TableSessionStatus::CANCELLED,
        ], true);
    }

    protected function hasOpenCartOrders(TableSession $tableSession): bool
    {
        return $tableSession->cartOrders()
            ->where('is_active', true)
            ->whereIn('status', [
                CartOrderStatus::OPEN,
                CartOrderStatus::LOCKED_FOR_PAYMENT,
            ])
            ->whereHas('items', fn (Builder $query) => $query->where('is_active', true))
            ->exists();
    }

    protected function cancelSessionCartOrders(TableSession $tableSession): void
    {
        $cartOrders = $tableSession->cartOrders()
            ->where('is_active', true)
            ->whereIn('status', [
                CartOrderStatus::OPEN,
                CartOrderStatus::LOCKED_FOR_PAYMENT,
            ])
            ->get();

        foreach ($cartOrders as $cartOrder) {
            $cartOrder->items()
                ->where('is_active', true)
                ->update([
                    'line_status' => OrderLineStatus::CANCELLED,
                    'is_active' => false,
                ]);

            $cartOrder->update([
                'status' => CartOrderStatus::CANCELLED,
                'is_active' => false,
            ]);
        }
    }

    protected function createInitialCartOrder(TableSession $tableSession): CartOrder
    {
        return CartOrder::query()->create([
            'session_id' => $tableSession->id,
            'table_id' => $tableSession->table_id,
            'order_no' => $this->generateOrderNo(),
            'created_by_employee' => $tableSession->opened_by_employee,
            'status' => CartOrderStatus::OPEN,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'service_charge_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'remark' => 'Giỏ mặc định khi mở phiên bàn',
            'is_active' => true,
        ]);
    }

    protected function generateOrderNo(): string
    {
        do {
            $orderNo = 'ORD-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (CartOrder::query()->where('order_no', $orderNo)->exists());

        return $orderNo;
    }

    protected function guardManualStatusUpdate(string $nextStatus): ?JsonResponse
    {
        if (in_array($nextStatus, [
            TableSessionStatus::PAYMENT_PENDING,
            TableSessionStatus::PAID,
        ], true)) {
            return $this->error(
                null,
                'Trạng thái thanh toán của phiên bàn chỉ được cập nhật thông qua nghiệp vụ hóa đơn',
                Response::HTTP_BAD_REQUEST
            );
        }

        return null;
    }

    protected function completeReservationOnArrival(?Reservation $reservation): void
    {
        if (! $reservation || ! $reservation->is_active) {
            return;
        }

        if (in_array($reservation->status, [ReservationStatus::COMPLETED, ReservationStatus::CANCELED], true)) {
            return;
        }

        $reservation->update([
            'status' => ReservationStatus::COMPLETED,
            'is_active' => false,
        ]);
    }

    protected function cancelReservation(?Reservation $reservation): void
    {
        if (! $reservation || $reservation->status === ReservationStatus::CANCELED) {
            return;
        }

        $reservation->update([
            'is_active' => false,
            'status' => ReservationStatus::CANCELED,
            'cancelled_at' => $reservation->cancelled_at ?? now(),
            'cancelled_by_employee' => $reservation->cancelled_by_employee ?? $this->getUserName(),
        ]);
    }
}
