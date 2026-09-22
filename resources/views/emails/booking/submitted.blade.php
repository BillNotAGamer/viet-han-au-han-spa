<!DOCTYPE html>
<html lang="{{ $booking->locale ?? 'vi' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ sprintf('[Việt Hàn Âu Hàn Spa] Yêu cầu đặt lịch %s', $booking->reference) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f7f5f2;
            color: #2b1e1b;
            margin: 0;
            padding: 24px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e8dfc8;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2b080c;
            color: #ffffff;
            padding: 24px 32px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #ffffff;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #c5a880;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .body {
            padding: 32px;
        }
        .reference-badge {
            display: inline-block;
            background-color: #faf2eb;
            border: 1px solid #c5a880;
            color: #5b1121;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .data-table th, .data-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #f0e9df;
            font-size: 14px;
            vertical-align: top;
        }
        .data-table th {
            width: 38%;
            color: #736965;
            font-weight: 600;
        }
        .data-table td {
            color: #2b1e1b;
            font-weight: 500;
        }
        .notes-box {
            background-color: #fdfbf7;
            border: 1px solid #e8dfc8;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            font-size: 14px;
            line-height: 1.6;
            color: #554d4a;
            white-space: pre-wrap;
        }
        .footer {
            background-color: #faf7f2;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #8c827a;
            border-top: 1px solid #e8dfc8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Việt Hàn Âu Hàn Spa</h1>
            <p>Thông Báo Yêu Cầu Đặt Lịch Mới</p>
        </div>

        <div class="body">
            <div class="reference-badge">
                Mã đặt lịch: {{ $booking->reference }}
            </div>

            <table class="data-table">
                <tr>
                    <th>Khách hàng</th>
                    <td>{{ $booking->customer_name }}</td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td>{{ $booking->phone }}</td>
                </tr>
                @if($booking->email)
                    <tr>
                        <th>Email</th>
                        <td>{{ $booking->email }}</td>
                    </tr>
                @endif
                <tr>
                    <th>Dịch vụ yêu cầu</th>
                    <td>
                        <strong>{{ $booking->service_name_snapshot ?? 'Chưa xác định' }}</strong>
                        @if($booking->service_price_label_snapshot)
                            <br><span style="color: #736965; font-size: 13px;">Gói: {{ $booking->service_price_label_snapshot }}</span>
                        @endif
                        @if($booking->price_amount_snapshot)
                            <br><span style="color: #5b1121; font-size: 13px;">Giá: {{ number_format((int) $booking->price_amount_snapshot, 0, ',', '.') }} ₫</span>
                        @endif
                        @if($booking->duration_minutes_snapshot)
                            <br><span style="color: #736965; font-size: 13px;">Thời lượng: {{ $booking->duration_minutes_snapshot }} phút</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Ngày hẹn mong muốn</th>
                    <td>{{ $booking->preferred_date }}</td>
                </tr>
                <tr>
                    <th>Giờ hẹn mong muốn</th>
                    <td>{{ $booking->preferred_time }}</td>
                </tr>
                <tr>
                    <th>Ngôn ngữ giao diện</th>
                    <td>{{ strtoupper($booking->locale) }}</td>
                </tr>
                <tr>
                    <th>Thời gian gửi</th>
                    <td>{{ $booking->created_at ? $booking->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') : now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</td>
                </tr>
            </table>

            @if($booking->customer_note)
                <div style="margin-top: 20px;">
                    <strong style="font-size: 14px; color: #5b1121;">Ghi chú từ khách hàng:</strong>
                    <div class="notes-box">{{ $booking->customer_note }}</div>
                </div>
            @endif
        </div>

        <div class="footer">
            Email thông báo tự động từ hệ thống quản lý đặt lịch Việt Hàn Âu Hàn Spa.<br>
            Vui lòng liên hệ khách hàng để xác nhận lịch hẹn theo quy trình.
        </div>
    </div>
</body>
</html>
