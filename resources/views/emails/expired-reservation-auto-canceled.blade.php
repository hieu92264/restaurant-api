<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt bàn quá hạn đã tự động hủy</title>
</head>

<body style="margin:0; padding:24px; background:#f3f4f6; font-family:Arial, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:24px 28px; background:#991b1b; color:#ffffff;">
                <h1 style="margin:0; font-size:24px; line-height:1.3;">Đơn đặt bàn quá hạn đã được tự động hủy</h1>
                <p style="margin:10px 0 0; font-size:14px; line-height:1.6; color:#fee2e2;">
                    Hệ thống vừa tự động hủy một đơn đặt bàn đã quá mốc giữ bàn.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:28px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding:10px 0; color:#6b7280; width:220px;">Mã đặt bàn (Reservation)</td>
                        <td style="padding:10px 0; color:#111827; font-weight:700;">
                            #{{ $reservation->reservation_code }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Khách hàng</td>
                        <td style="padding:10px 0; color:#111827;">{{ $reservation->customer_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Số điện thoại</td>
                        <td style="padding:10px 0; color:#111827;">{{ $reservation->customer_phone ?: 'Không có' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Số khách</td>
                        <td style="padding:10px 0; color:#111827;">{{ $reservation->guest_count }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Thời gian đặt</td>
                        <td style="padding:10px 0; color:#111827;">
                            {{ optional($reservation->reservation_time)->format('H:i - d/m/Y') ?? $reservation->reservation_time }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Hạn giữ bàn</td>
                        <td style="padding:10px 0; color:#111827;">
                            {{ optional($reservation->hold_end_time)->format('H:i - d/m/Y') ?? 'Không xác định' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Bàn</td>
                        <td style="padding:10px 0; color:#111827;">{{ $reservation->table_code ?: 'Chưa gán bàn' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Trạng thái mới</td>
                        <td style="padding:10px 0;">
                            <span
                                style="display:inline-block; padding:6px 10px; border-radius:999px; background:#fee2e2; color:#991b1b; font-size:13px; font-weight:700;">
                                {{ $reservation->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:#6b7280;">Thời gian hủy</td>
                        <td style="padding:10px 0; color:#111827;">
                            {{ optional($reservation->cancelled_at)->format('H:i:s d/m/Y') ?? now()->format('H:i:s d/m/Y') }}
                        </td>
                    </tr>
                </table>

                <p style="margin:24px 0 0; font-size:14px; line-height:1.7; color:#374151;">
                    Vui lòng kiểm tra lại đơn đặt bàn nếu cần liên hệ với khách hàng hoặc sắp xếp lại bàn.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 28px; background:#f9fafb; color:#6b7280; font-size:12px; line-height:1.6;">
                Đây là email tự động từ hệ thống quản lý đặt bàn. Vui lòng không phản hồi email này.
            </td>
        </tr>
    </table>
</body>

</html>