<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu đặt bàn mới</title>
</head>

<body
    style="font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; color: #1f2937;">
    <div
        style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e5e7eb;">

        <div style="background-color: #111827; padding: 32px; text-align: center;">
            <h1
                style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Đặt Bàn Mới
            </h1>
        </div>

        <div style="padding: 32px;">
            <p style="font-size: 16px; line-height: 1.5; color: #4b5563; margin-bottom: 24px;">
                Chào Quản lý, bạn vừa nhận được một yêu cầu đặt bàn từ hệ thống. Vui lòng kiểm tra và liên hệ sớm với
                khách hàng để xác nhận đơn đặt.
            </p>

            <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px; width: 40%;"><strong>Mã đặt
                                bàn:</strong></td>
                        <td style="padding: 10px 0; color: #111827; font-size: 14px; font-weight: bold;">
                            #{{ $reservation->reservation_code }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong>Tên khách hàng:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #111827; font-size: 14px;">{{ $reservation->customer_name }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong>Số điện thoại:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #2563eb; font-size: 14px; text-decoration: none;">
                            <strong>{{ $reservation->customer_phone }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong>Số lượng khách:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #111827; font-size: 14px;">{{ $reservation->guest_count }}
                            người</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong>Thời gian đặt:</strong>
                        </td>
                        <td style="padding: 10px 0; color: #dc2626; font-size: 14px; font-weight: bold;">
                            {{ optional($reservation->reservation_time)->format('H:i - d/m/Y') ?? $reservation->reservation_time }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px;"><strong>Trạng thái:</strong></td>
                        <td style="padding: 10px 0;">
                            <span
                                style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                {{ $reservation->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6b7280; font-size: 14px; vertical-align: top;"><strong>Ghi
                                chú:</strong></td>
                        <td style="padding: 10px 0; color: #111827; font-size: 14px; font-style: italic;">
                            "{{ $reservation->remark ?: 'Không có ghi chú nào' }}"
                        </td>
                    </tr>
                </table>
            </div>

            <div style="margin-top: 32px; text-align: center;">
                <p style="font-size: 12px; color: #9ca3af;">
                    Yêu cầu được gửi lúc:
                    {{ optional($reservation->created_at)->format('H:i:s d/m/Y') ?? now()->format('H:i:s d/m/Y') }}
                </p>
            </div>
        </div>

        <div style="background-color: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                Đây là email tự động từ hệ thống quản lý đặt bàn.<br>
                Vui lòng không phản hồi lại email này.
            </p>
        </div>
    </div>
</body>

</html>