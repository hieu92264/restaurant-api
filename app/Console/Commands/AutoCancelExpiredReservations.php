<?php

namespace App\Console\Commands;

use App\Common\Constants\ReservationStatus;
use App\Http\interfaces\ITableStatusService;
use App\Models\Reservation;
use App\Support\ReservationManagerNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCancelExpiredReservations extends Command
{
    /**
     * Tên và chữ ký của lệnh console.
     *
     * @var string
     */
    protected $signature = 'reservations:auto-cancel-expired';

    /**
     * Mô tả của lệnh console.
     *
     * @var string
     */
    protected $description = 'Tự động hủy các đơn đặt bàn đã quá hạn giữ bàn và thông báo cho quản lý.';

    public function __construct(
        protected ITableStatusService $tableStatusService,
        protected ReservationManagerNotifier $reservationManagerNotifier
    ) {
        parent::__construct();
    }

    /**
     * Thực hiện lệnh console.
     */
    public function handle(): int
    {
        $now = now();
        $canceledReservations = [];

        Reservation::query()
            ->where('is_active', true)
            ->whereIn('status', [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
            ])
            ->whereNotNull('hold_end_time')
            ->where('hold_end_time', '<', $now)
            ->whereDoesntHave('sessions')
            ->orderBy('id')
            ->chunkById(100, function ($reservations) use (&$canceledReservations, $now) {
                foreach ($reservations as $reservation) {
                    $canceledReservation = DB::transaction(function () use ($reservation, $now) {
                        $lockedReservation = Reservation::query()
                            ->whereKey($reservation->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedReservation) {
                            return null;
                        }

                        // Kiểm tra lại điều kiện sau khi khóa bản ghi để tránh tranh chấp dữ liệu (Race Condition)
                        if (
                            ! $lockedReservation->is_active
                            || ! in_array($lockedReservation->status, [
                                ReservationStatus::PENDING,
                                ReservationStatus::CONFIRMED,
                            ], true)
                            || $lockedReservation->hold_end_time === null
                            || $lockedReservation->hold_end_time->greaterThanOrEqualTo($now)
                            || $lockedReservation->sessions()->exists()
                        ) {
                            return null;
                        }

                        $lockedReservation->update([
                            'is_active' => false,
                            'status' => ReservationStatus::CANCELED,
                            'cancelled_at' => $now,
                            'cancelled_by_employee' => null,
                        ]);

                        return $lockedReservation->fresh();
                    });

                    if (! $canceledReservation) {
                        continue;
                    }

                    $canceledReservations[] = $canceledReservation;
                }
            });

        // Xử lý hậu kỳ: Đồng bộ trạng thái bàn và gửi thông báo
        foreach ($canceledReservations as $reservation) {
            if ($reservation->table_code) {
                $this->tableStatusService->syncTableStatus($reservation->table_code);
            }

            $this->reservationManagerNotifier->notifyExpiredReservationAutoCanceled($reservation);
        }

        $this->info(sprintf(
            'Đã tự động hủy %d đơn đặt bàn quá hạn.',
            count($canceledReservations)
        ));

        return self::SUCCESS;
    }
}
