<?php

namespace App\Support;

use App\Mail\CustomerReservationCreatedMail;
use App\Mail\ExpiredReservationAutoCanceledMail;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationManagerNotifier
{
    /**
     * Thông báo cho quản lý khi có khách hàng tạo đặt bàn mới.
     */
    public function notifyCustomerReservationCreated(Reservation $reservation): void
    {
        $this->queueToManagers(
            reservation: $reservation,
            mailable: new CustomerReservationCreatedMail($reservation),
            logMessage: 'Không thể gửi email thông báo đặt bàn mới cho quản lý.'
        );
    }

    /**
     * Thông báo cho quản lý khi đơn đặt bàn quá hạn bị tự động hủy.
     */
    public function notifyExpiredReservationAutoCanceled(Reservation $reservation): void
    {
        $this->queueToManagers(
            reservation: $reservation,
            mailable: new ExpiredReservationAutoCanceledMail($reservation),
            logMessage: 'Không thể gửi email thông báo hủy đặt bàn quá hạn cho quản lý.'
        );
    }

    /**
     * Lấy danh sách email của tất cả quản lý đang hoạt động.
     *
     * @return array<int, string>
     */
    protected function managerEmails(): array
    {
        return User::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->whereHas('role', fn($query) => $query->where('code', 'MANAGER'))
            ->pluck('email')
            ->filter(fn(?string $email) => is_string($email) && $email !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Đưa email vào hàng đợi để gửi cho các quản lý.
     */
    protected function queueToManagers(
        Reservation $reservation,
        Mailable $mailable,
        string $logMessage
    ): void {
        $managerEmails = $this->managerEmails();

        if ($managerEmails === []) {
            return;
        }

        try {
            Mail::to($managerEmails)->queue($mailable->onQueue('mail'));
        } catch (\Throwable $e) {
            Log::warning($logMessage, [
                'reservation_id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'manager_emails' => $managerEmails,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
