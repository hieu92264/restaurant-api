<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiredReservationAutoCanceledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo instance mới.
     */
    public function __construct(public Reservation $reservation) {}

    /**
     * Lấy phong bì thư (Envelope).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo: Đơn đặt bàn quá hạn đã được tự động hủy'
        );
    }

    /**
     * Lấy nội dung thư (Content).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.expired-reservation-auto-canceled'
        );
    }

    /**
     * Lấy các tệp đính kèm.
     */
    public function attachments(): array
    {
        return [];
    }
}
