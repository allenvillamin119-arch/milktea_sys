@extends('layouts.main')

@section('title', 'Receipt')
@section('page-title', 'Transaction Receipt - ' . $transaction->transaction_number)

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-receipt"></i> Receipt</h4>
                    <small class="text-muted">{{ $transaction->transaction_number }}</small>
                </div>

                <div class="row mb-4 pb-4 border-bottom">
                    <div class="col-6">
                        <small class="text-muted">Transaction Date</small>
                        <p class="mb-0 fw-bold">{{ $transaction->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Transaction Time</small>
                        <p class="mb-0 fw-bold">{{ $transaction->created_at->format('H:i:s') }}</p>
                    </div>
                </div>

                <div class="mb-4 pb-4 border-bottom">
                    <small class="text-muted d-block mb-2">Cashier</small>
                    <p class="mb-0">{{ $transaction->user->name }}</p>
                </div>

                <div class="mb-4">
                    <table class="table table-sm table-borderless">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                            <tr>
                                <td colspan="4" class="p-0"><hr class="m-0"></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->transactionItems as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format($item->price, 2) }}</td>
                                <td class="text-end">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-4 pb-4 border-top border-bottom">
                    <div class="row g-0 mb-2">
                        <div class="col-9 text-end">Total:</div>
                        <div class="col-3 text-end fw-bold">₱{{ number_format($transaction->total_amount, 2) }}</div>
                    </div>
                    <div class="row g-0 mb-2">
                        <div class="col-9 text-end">Cash Received:</div>
                        <div class="col-3 text-end">₱{{ number_format($transaction->cash_received, 2) }}</div>
                    </div>
                    <div class="row g-0">
                        <div class="col-9 text-end fw-bold">Change:</div>
                        <div class="col-3 text-end fw-bold text-success">₱{{ number_format($transaction->change, 2) }}</div>
                    </div>
                </div>

                <div class="mb-4 pb-4 border-bottom">
                    <div class="row g-0">
                        <div class="col-6">
                            <small class="text-muted">Payment Method</small>
                            <p class="mb-0 fw-bold text-uppercase">{{ $transaction->payment_method }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">Items</small>
                            <p class="mb-0 fw-bold">{{ $transaction->transactionItems->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="{{ route('pos.print-receipt', $transaction) }}" class="btn btn-sm btn-primary me-2" target="_blank">
                        <i class="fas fa-print"></i> Print Receipt
                    </a>
                    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to POS
                    </a>
                </div>

                <div class="text-center mt-4 text-muted">
                    <small>Thank you for your purchase!</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
