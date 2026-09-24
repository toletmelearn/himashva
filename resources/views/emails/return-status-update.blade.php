@extends('emails.layout')

@section('subject', 'Return Update — #' . $return->order->order_number)

@php
    $badgeClass = match ($return->status) {
        'approved', 'refunded' => 'badge-success',
        'rejected' => 'badge-warning',
        'received' => 'badge-info',
        default => 'badge-info',
    };
@endphp

@section('content')
    <h2>Update on Your Return</h2>
    <p>There's an update on your return request for order <strong>#{{ $return->order->order_number }}</strong>.</p>

    <div class="card">
        <span class="badge {{ $badgeClass }}">{{ ucfirst($return->status) }}</span>

        @if ($return->admin_notes)
            <p style="margin: 12px 0 0;"><strong>Note from our team:</strong> {{ $return->admin_notes }}</p>
        @endif

        @if ($return->status === 'approved' && settings('return_pickup_address'))
            <p style="margin: 12px 0 0;"><strong>Pickup Address:</strong> {{ settings('return_pickup_address') }}</p>
        @endif

        @if ($return->status === 'refunded')
            <p style="margin: 12px 0 0;">Your refund has been initiated and should reflect in your original payment method shortly.</p>
        @endif
    </div>
@endsection
