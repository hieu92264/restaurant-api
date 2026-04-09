<?php

namespace App\Http\Controllers\Api\Table;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\OrderLineStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartOrderRequest;
use App\Http\Requests\UpdateCartOrderRequest;
use App\Models\CartOrder;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CartOrderController extends Controller
{
    /**
     * Lấy danh sách đơn tạm tính với các bộ lọc
     */
    public function index(Request $request): JsonResponse
    {
        $cartOrders = CartOrder::query()
            ->with($this->relations())
            ->when(
                $request->filled('session_id'),
                fn(Builder $query) => $query->where('session_id', (int) $request->input('session_id'))
            )
            ->when(
                $request->filled('table_id'),
                fn(Builder $query) => $query->where('table_id', (int) $request->input('table_id'))
            )
            ->when(
                $request->filled('status'),
                fn(Builder $query) => $query->where('status', (string) $request->input('status'))
            )
            ->when(
                $request->has('is_active'),
                fn(Builder $query) => $query->where('is_active', $request->boolean('is_active'))
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return $this->success($cartOrders);
    }

    /**
     * Chi tiết một đơn tạm tính
     */
    public function show(int $cartOrderId): JsonResponse
    {
        $cartOrder = $this->findCartOrder($cartOrderId);

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính', Response::HTTP_NOT_FOUND);
        }

        return $this->success($cartOrder);
    }

    /**
     * Tạo mới đơn tạm tính kèm danh sách món
     */
    public function store(StoreCartOrderRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $tableSession = $this->resolveLiveSession($payload['session_id']);

        if (! $tableSession) {
            return $this->error(null, 'Phiên bản không hợp lệ hoặc đã kết thúc', Response::HTTP_BAD_REQUEST);
        }

        if ($tableSession->table_id !== $payload['table_id']) {
            return $this->error(null, 'Bàn không thuộc phiên bản này', Response::HTTP_BAD_REQUEST);
        }

        $cartOrder = DB::transaction(function () use ($payload): CartOrder {
            // 1. Tạo đơn hàng chính
            $cartOrder = CartOrder::query()->create([
                'session_id' => $payload['session_id'],
                'table_id' => $payload['table_id'],
                'order_no' => $this->generateOrderNo(),
                'created_by_employee' => $this->getUserName(),
                'status' => CartOrderStatus::OPEN,
                'subtotal_amount' => $payload['subtotal_amount'],
                'discount_amount' => $payload['discount_amount'] ?? 0,
                'service_charge_amount' => $payload['service_charge_amount'] ?? 0,
                'tax_amount' => $payload['tax_amount'] ?? 0,
                'total_amount' => $payload['total_amount'],
                'remark' => $payload['remark'] ?? null,
                'is_active' => true,
            ]);

            // 2. Tạo các món ăn trong đơn (cart_order_id tự động được gán)
            foreach ($payload['items'] as $item) {
                $cartOrder->items()->create([
                    'combo_id' => $item['combo_id'] ?? null,
                    'item_name_snapshot' => $item['item_name_snapshot'],
                    'variant_name_snapshot' => $item['variant_name_snapshot'] ?? null,
                    'quantity' => $item['quantity'],
                    'base_unit_price' => $item['base_unit_price'],
                    'option_total_price' => $item['option_total_price'] ?? 0,
                    'unit_final_price' => $item['unit_final_price'],
                    'line_total' => $item['line_total'],
                    'item_note' => $item['item_note'] ?? null,
                    'line_status' => OrderLineStatus::ACTIVE,
                    'is_active' => true,
                ]);
            }

            return $cartOrder;
        });

        return $this->success(
            $cartOrder->fresh()->load($this->relations()),
            'Tạo đơn tạm tính thành công.',
            Response::HTTP_CREATED
        );
    }

    /**
     * Cập nhật thông tin đơn hàng và danh sách món
     */
    public function update(UpdateCartOrderRequest $request, int $cartOrderId): JsonResponse
    {
        $cartOrder = CartOrder::query()
            ->with(['session', 'items'])
            ->find($cartOrderId);

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính', Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validated();

        // Kiểm tra logic chuyển đổi trạng thái
        if (
            array_key_exists('status', $payload)
            && ! $this->isAllowedStatusTransition($cartOrder->status, $payload['status'])
        ) {
            return $this->error(null, 'Không thể chuyển trạng thái đơn tạm tính này', Response::HTTP_BAD_REQUEST);
        }

        // Kiểm tra tính hợp lệ của session nếu có thay đổi bàn/phiên
        if (
            array_key_exists('session_id', $payload)
            || array_key_exists('table_id', $payload)
        ) {
            $nextSessionId = $payload['session_id'] ?? $cartOrder->session_id;
            $nextTableId = $payload['table_id'] ?? $cartOrder->table_id;
            $tableSession = $this->resolveLiveSession($nextSessionId);

            if (! $tableSession) {
                return $this->error(null, 'Phiên bản mới không hợp lệ hoặc đã kết thúc', Response::HTTP_BAD_REQUEST);
            }

            if ($tableSession->table_id !== $nextTableId) {
                return $this->error(null, 'Bàn không thuộc phiên bản được chọn', Response::HTTP_BAD_REQUEST);
            }
        }

        DB::transaction(function () use ($cartOrder, $payload): void {
            // Cập nhật danh sách món nếu có truyền lên
            if (array_key_exists('items', $payload)) {
                $cartOrder->items()->delete();

                foreach ($payload['items'] as $item) {
                    $cartOrder->items()->create([
                        'combo_id' => $item['combo_id'] ?? null,
                        'item_name_snapshot' => $item['item_name_snapshot'],
                        'variant_name_snapshot' => $item['variant_name_snapshot'] ?? null,
                        'quantity' => $item['quantity'],
                        'base_unit_price' => $item['base_unit_price'],
                        'option_total_price' => $item['option_total_price'] ?? 0,
                        'unit_final_price' => $item['unit_final_price'],
                        'line_total' => $item['line_total'],
                        'item_note' => $item['item_note'] ?? null,
                        'line_status' => $item['line_status'] ?? OrderLineStatus::ACTIVE,
                        'is_active' => $item['is_active'] ?? true,
                    ]);
                }
            }

            // Cập nhật các thông tin còn lại của đơn hàng
            unset($payload['items']);
            $cartOrder->update($payload);
        });

        return $this->success(
            $cartOrder->fresh()->load($this->relations()),
            'Cập nhật đơn tạm tính thành công.'
        );
    }

    /**
     * Xóa mềm (ẩn) đơn tạm tính
     */
    public function destroy(int $cartOrderId): JsonResponse
    {
        $cartOrder = CartOrder::query()->find($cartOrderId);

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính', Response::HTTP_NOT_FOUND);
        }

        $cartOrder->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Ẩn đơn tạm tính thành công.');
    }

    /**
     * Định nghĩa các quan hệ cần load kèm theo
     */
    protected function relations(): array
    {
        return [
            'session',
            'table',
            'items',
            'invoice',
        ];
    }

    /**
     * Tìm đơn tạm tính kèm theo các quan hệ
     */
    protected function findCartOrder(int $cartOrderId): ?CartOrder
    {
        return CartOrder::query()
            ->with($this->relations())
            ->find($cartOrderId);
    }

    /**
     * Kiểm tra phiên hoạt động hợp lệ
     */
    protected function resolveLiveSession(int $sessionId): ?TableSession
    {
        return TableSession::query()
            ->where('id', $sessionId)
            ->where('is_active', true)
            ->whereIn('status', [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
            ])
            ->first();
    }

    /**
     * Quản lý logic chuyển đổi trạng thái đơn hàng (State Machine)
     */
    protected function isAllowedStatusTransition(string $currentStatus, string $nextStatus): bool
    {
        $allowedTransitions = [
            CartOrderStatus::OPEN => [
                CartOrderStatus::OPEN,
                CartOrderStatus::LOCKED_FOR_PAYMENT,
                CartOrderStatus::CANCELLED,
            ],
            CartOrderStatus::LOCKED_FOR_PAYMENT => [
                CartOrderStatus::LOCKED_FOR_PAYMENT,
                CartOrderStatus::OPEN,
                CartOrderStatus::CANCELLED,
                CartOrderStatus::CONVERTED_TO_INVOICE,
            ],
            CartOrderStatus::CANCELLED => [
                CartOrderStatus::CANCELLED,
            ],
            CartOrderStatus::CONVERTED_TO_INVOICE => [
                CartOrderStatus::CONVERTED_TO_INVOICE,
            ],
        ];

        return in_array($nextStatus, $allowedTransitions[$currentStatus] ?? [], true);
    }

    /**
     * Tạo mã đơn hàng duy nhất
     */
    protected function generateOrderNo(): string
    {
        do {
            $orderNo = 'ORD-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (CartOrder::query()->where('order_no', $orderNo)->exists());

        return $orderNo;
    }
}
