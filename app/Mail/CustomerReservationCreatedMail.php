<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class CustomerReservationCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo instance mới.
     * Sử dụng public property để Blade có thể truy cập trực tiếp.
     */
    public function __construct(public Reservation $reservation) {}

    /**
     * Định nghĩa tiêu đề email (Envelope).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Thông báo: Có yêu cầu đặt bàn mới từ khách hàng'
        );
    }

    /**
     * Định nghĩa view và dữ liệu (Content).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-reservation-created'
        );
    }

    /**
     * Đính kèm file nếu cần.
     */
    public function attachments(): array
    {
        return [];
    }
}
