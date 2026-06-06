<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
        }
        
        .receipt {
            padding: 10mm;
            background: white;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 11px;
        }
        
        .items-section {
            margin: 10px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-qty {
            width: 30px;
            text-align: center;
        }
        
        .item-price {
            width: 40px;
            text-align: right;
        }
        
        .totals {
            margin: 10px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .payment-info {
            margin: 5px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="logo">
            The Dosage Drip System
        </div>
        
        <div class="header">
            <p><strong>RECEIPT</strong></p>
            <p>{{ $transaction->transaction_number }}</p>
            <p>{{ $transaction->created_at->format('m/d/Y H:i:s') }}</p>
            <p>Cashier: {{ $transaction->user->name }}</p>
        </div>
        
        <div class="items-section">
            <div class="item" style="font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px;">
                <div class="item-name">Item</div>
                <div class="item-qty">Qty</div>
                <div class="item-price">Total</div>
            </div>
            
            @foreach($transaction->transactionItems as $item)
            <div class="item">
                <div class="item-name">{{ substr($item->product->name, 0, 25) }}</div>
                <div class="item-qty">{{ $item->quantity }}</div>
                <div class="item-price">₱{{ number_format($item->subtotal, 2) }}</div>
            </div>
            <div class="item" style="font-size: 10px; color: #666;">
                <div class="item-name">@ ₱{{ number_format($item->price, 2) }} each</div>
            </div>
            @endforeach
        </div>
        
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₱{{ number_format($transaction->total_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Cash Received:</span>
                <span>₱{{ number_format($transaction->cash_received, 2) }}</span>
            </div>
            <div class="total-row" style="font-weight: bold; font-size: 13px;">
                <span>CHANGE:</span>
                <span>₱{{ number_format($transaction->change, 2) }}</span>
            </div>
        </div>
        
        <div class="payment-info">
            <p><strong>Payment Method:</strong></p>
            <p>{{ strtoupper($transaction->payment_method) }}</p>
        </div>
        
        <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>Visit us again!</p>
            <p style="margin-top: 10px;">{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
