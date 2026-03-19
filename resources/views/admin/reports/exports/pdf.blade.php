<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports PDF</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #2f241f;
            background: #ffffff;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .subtitle {
            margin-top: 6px;
            color: #6b7280;
            font-size: 13px;
        }

        .meta {
            text-align: right;
            font-size: 12px;
            color: #6b7280;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 18px;
        }

        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
        }

        .summary-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            font-weight: 700;
        }

        .summary-value {
            margin-top: 5px;
            font-size: 18px;
            font-weight: 800;
            color: #2f241f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        thead th {
            background: #faf7f4;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody td {
            font-size: 12px;
            padding: 9px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .text-right {
            text-align: right;
        }

        .print-actions {
            margin-bottom: 14px;
        }

        .print-button {
            background: #2f241f;
            color: #ffffff;
            border: 0;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        @media print {
            body {
                padding: 12px;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="print-actions">
        <button class="print-button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="header">
        <div>
            <h1 class="title">Admin Sales Report</h1>
            <p class="subtitle">{{ $rangeLabel }} ({{ $startDate }} to {{ $endDate }})</p>
        </div>
        <div class="meta">
            <p>Generated: {{ now()->format('M d, Y H:i') }}</p>
            <p>Purr's Coffee</p>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <p class="summary-label">Orders</p>
            <p class="summary-value">{{ number_format((int) $ordersCount) }}</p>
        </div>
        <div class="summary-card">
            <p class="summary-label">Revenue</p>
            <p class="summary-value">${{ number_format((float) $revenue, 2) }}</p>
        </div>
        <div class="summary-card">
            <p class="summary-label">Items Sold</p>
            <p class="summary-value">{{ number_format((int) $itemsSold) }}</p>
        </div>
        <div class="summary-card">
            <p class="summary-label">Discount</p>
            <p class="summary-value">${{ number_format((float) $discountTotal, 2) }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Payment</th>
                <th>Status</th>
                <th class="text-right">Items</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @php
                    $orderDateValue = $order->{$reportDateColumn} ?? $order->created_at;
                    $formattedOrderDate =
                        $orderDateValue instanceof \Carbon\Carbon
                            ? $orderDateValue->format('M d, Y H:i')
                            : ($orderDateValue ? \Carbon\Carbon::parse((string) $orderDateValue)->format('M d, Y H:i') : '-');
                @endphp
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $formattedOrderDate }}</td>
                    <td>{{ $order->cashier?->name ?? 'Unknown Cashier' }}</td>
                    <td>{{ str((string) ($order->payment_method ?? 'unknown'))->replace('_', ' ')->headline() }}</td>
                    <td>{{ str((string) ($order->status ?? 'completed'))->replace('_', ' ')->headline() }}</td>
                    <td class="text-right">{{ number_format((int) ($order->items_count ?? 0)) }}</td>
                    <td class="text-right">${{ number_format((float) ($order->subtotal ?? 0), 2) }}</td>
                    <td class="text-right">${{ number_format((float) ($order->discount ?? 0), 2) }}</td>
                    <td class="text-right">${{ number_format((float) ($order->total ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No records found for this report range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
