<?php

namespace App\Http\Controllers\Api\Table;

use App\Common\Constants\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\interfaces\ITableStatusService;
use App\Http\Requests\StoreReservationByCustomerRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Mail\CustomerReservationCreatedMail;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    public function __construct(protected ITableStatusService $tableStatusService) {}

    public function index(): JsonResponse
    {
        $reservations = Reservation::all();

        return $this->success($reservations);
    }

    public function show(string $reservationCode): JsonResponse
    {
        $reservation = Reservation::where('reservation_code', $reservationCode)->first();

        if (! $reservation) {
            return $this->error(null, 'Không tìm thấy yêu cầu đặt bàn', Response::HTTP_NOT_FOUND);
        }

        return $this->success($reservation);
    }

    public function storeByCustomer(StoreReservationByCustomerRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['reservation_code'] = $this->generateUniqueCode($payload['customer_name']);
        $payload['is_active'] = true;
        $payload['status'] = ReservationStatus::PENDING;
        $payload['deposit_amount'] = 0;
        $payload['hold_start_time'] = Carbon::parse($payload['reservation_time'])
            ->subHour()
            ->format('Y-m-d H:i:s');
        $payload['hold_end_time'] = Carbon::parse($payload['reservation_time'])
            ->addMinute(15)
            ->format('Y-m-d H:i:s');

        $result = Reservation::create($payload);
        $this->notifyManagersAboutCustomerReservation($result);

        return $this->success($result, 'Tạo đặt bàn thành công.', Response::HTTP_CREATED);
    }

    protected function generateUniqueCode(string $customerName): string
    {
        $code = now()->format('YmdHis') . '-' . strtoupper($customerName);

        $exists = Reservation::where('reservation_code', $code)->first();

        if ($exists) {
            return $this->generateUniqueCode($customerName);
        }

        return $code;
    }

    protected function notifyManagersAboutCustomerReservation(Reservation $reservation): void
    {
        $managerEmails = User::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->whereHas('role', fn ($query) => $query->where('code', 'MANAGER'))
            ->pluck('email')
            ->filter(fn (?string $email) => is_string($email) && $email !== '')
            ->unique()
            ->values()
            ->all();

        if ($managerEmails === []) {
            return;
        }

        try {
            Mail::to($managerEmails)->queue(
                (new CustomerReservationCreatedMail($reservation))->onQueue('mail')
            );
        } catch (\Throwable $e) {
            Log::warning('Khong the gui email thong bao dat ban moi cho quan ly.', [
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'manager_emails' => $managerEmails,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['reservation_code'] = $this->generateUniqueCode($payload['customer_name']);
        $payload['is_active'] = true;
        $payload['hold_start_time'] = $payload['hold_start_time'] ?? Carbon::parse($payload['reservation_time'])
            ->subHour()
            ->format('Y-m-d H:i:s');
        $payload['hold_end_time'] = $payload['hold_end_time'] ?? Carbon::parse($payload['reservation_time'])
            ->addMinute(15)
            ->format('Y-m-d H:i:s');
        $payload['created_by_employee'] = $this->getUserName();

        if (
            !empty($payload['table_code'])
            && $this->checkAbleTable(
                $payload['table_code'],
                $payload['hold_start_time'],
                $payload['hold_end_time']
            )
        ) {
            return $this->error(
                null,
                'Bàn hiện tại đang được chờ duyệt cho một yêu cầu đặt bàn trước hoặc đã được đặt trước',
                Response::HTTP_BAD_REQUEST
            );
        }

        $result = Reservation::create($payload);

        if (!empty($payload['table_code'])) $this->tableStatusService->syncTableStatus($payload['table_code']);

        return $this->success($result, 'Tạo đặt bàn thành công.', Response::HTTP_CREATED);
    }

    protected function checkAbleTable(
        string  $tableCode,
        mixed   $holdStartTime,
        mixed   $holdEndTime,
        ?string $ignoreReservationCode = null
    ): bool {
        $holdStartTime = Carbon::parse($holdStartTime)->format('Y-m-d H:i:s');
        $holdEndTime = Carbon::parse($holdEndTime)->format('Y-m-d H:i:s');

        $result = Reservation::where('table_code', $tableCode)
            ->where('is_active', true)
            ->whereIn('status', [ReservationStatus::PENDING, ReservationStatus::CONFIRMED])
            ->when(
                $ignoreReservationCode,
                fn($query) => $query->where('reservation_code', '!=', $ignoreReservationCode)
            )
            ->where('hold_start_time', '<', $holdEndTime)
            ->where('hold_end_time', '>', $holdStartTime)
            ->first();

        return $result !== null;
    }

    public function destroy(string $reservationCode): JsonResponse
    {
        $reservation = Reservation::where('reservation_code', $reservationCode)->first();

        if (! $reservation) {
            return $this->error(null, 'Không tìm thấy yêu cầu đặt bàn', Response::HTTP_NOT_FOUND);
        }

        $tableCode = $reservation->table_code;

        $reservation->update([
            'is_active' => false,
            'status' => ReservationStatus::CANCELED,
            'cancelled_at' => Carbon::now(),
            'cancelled_by_employee' => $this->getUserName(),
        ]);

        if ($tableCode) {
            $this->tableStatusService->syncTableStatus($tableCode);
        }

        return $this->success($reservation, 'Xóa đặt bàn thành công.', Response::HTTP_OK);
    }

    public function update(UpdateReservationRequest $request, string $reservationCode): JsonResponse
    {
        $payload = $request->validated();
        $exists = Reservation::where('reservation_code', $reservationCode)->first();

        if (!$exists) {
            return $this->error(null, 'Không tìm thấy yêu cầu đặt bàn', Response::HTTP_NOT_FOUND);
        }

        $oldTableCode = $exists->table_code;

        if (array_key_exists('reservation_time', $payload)) {
            $payload['hold_start_time'] = $payload['hold_start_time'] ?? Carbon::parse($payload['reservation_time'])
                ->subHour()
                ->format('Y-m-d H:i:s');
            $payload['hold_end_time'] = $payload['hold_end_time'] ?? Carbon::parse($payload['reservation_time'])
                ->addMinute(15)
                ->format('Y-m-d H:i:s');
        }

        if (($payload['isConfirmed'] ?? false) === true) {
            $payload['status'] = ReservationStatus::CONFIRMED;
            $payload['confirmed_at'] = Carbon::now();
            $payload['confirmed_by_employee'] = $this->getUserName();
        }

        $targetTableCode = $payload['table_code'] ?? $exists->table_code;
        $targetHoldStartTime = $payload['hold_start_time'] ?? $exists->hold_start_time;
        $targetHoldEndTime = $payload['hold_end_time'] ?? $exists->hold_end_time;

        if (
            $targetTableCode !== null
            && $targetHoldStartTime !== null
            && $targetHoldEndTime !== null
            && (
                (array_key_exists('table_code', $payload) && $payload['table_code'] != $exists->table_code)
                || array_key_exists('hold_start_time', $payload)
                || array_key_exists('hold_end_time', $payload)
                || array_key_exists('reservation_time', $payload)
            )
        ) {
            if ($this->checkAbleTable(
                $targetTableCode,
                $targetHoldStartTime,
                $targetHoldEndTime,
                $exists->reservation_code
            )) {
                return $this->error(
                    null,
                    'Bàn hiện tại đang được chờ duyệt cho một yêu cầu đặt bàn trước hoặc đã được đặt trước',
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        if (($payload['isCancelled'] ?? false) === true) {
            $payload['status'] = ReservationStatus::CANCELED;
            $payload['cancelled_at'] = Carbon::now();
            $payload['cancelled_by_employee'] = $this->getUserName();
        }

        $exists->update($payload);

        if ($oldTableCode) $this->tableStatusService->syncTableStatus($oldTableCode);

        if ($targetTableCode && $targetTableCode !== $oldTableCode) $this->tableStatusService->syncTableStatus($targetTableCode);

        return $this->success($exists->fresh(), 'Cập nhật đặt bàn thành công.', Response::HTTP_OK);
    }
}
