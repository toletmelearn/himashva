@props(['order'])

@php
    $steps = [
        'pending' => ['label' => 'Order Placed', 'icon' => '📦'],
        'confirmed' => ['label' => 'Confirmed', 'icon' => '✅'],
        'processing' => ['label' => 'Processing', 'icon' => '⚙️'],
        'shipped' => ['label' => 'Shipped', 'icon' => '🚚'],
        'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => '🏃'],
        'delivered' => ['label' => 'Delivered', 'icon' => '🎉'],
    ];

    $statusOrder = array_keys($steps);
    $currentIndex = array_search($order->order_status, $statusOrder);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }

    $isCancelled = in_array($order->order_status, ['cancelled', 'returned', 'refunded']);
    $history = $order->statusHistory->keyBy('to_status');
@endphp

<div class="overflow-x-auto py-4">
    <div class="flex items-start" style="min-width: 500px;">
        @foreach ($steps as $status => $step)
            @php
                $stepIndex = array_search($status, $statusOrder);
                $isCompleted = $stepIndex <= $currentIndex;
                $isCurrent = $stepIndex === $currentIndex;
                $timestamp = $history->get($status)?->created_at?->format('d M, g:i A');
            @endphp

            <div class="flex-1 text-center relative">
                @if (!$loop->first)
                    <div class="absolute top-[18px] h-[3px] z-0" style="left: -50%; right: 50%; background: {{ $isCompleted && !$isCancelled ? '#10B981' : '#E5E7EB' }};"></div>
                @endif

                <div class="w-9 h-9 rounded-full mx-auto mb-2 flex items-center justify-center relative z-10 text-base"
                    style="
                        {{ $isCancelled && $isCurrent ? 'background:#FEE2E2;border:2px solid #EF4444;' : '' }}
                        {{ $isCompleted && !$isCancelled ? 'background:#D1FAE5;border:2px solid #10B981;' : '' }}
                        {{ !$isCompleted && !$isCancelled ? 'background:#F3F4F6;border:2px solid #D1D5DB;' : '' }}
                        {{ $isCurrent && !$isCancelled ? 'box-shadow:0 0 0 4px rgba(16,185,129,0.2);' : '' }}
                    ">
                    {{ $isCancelled && $isCurrent ? '❌' : $step['icon'] }}
                </div>

                <div class="text-[0.7rem] mb-0.5 {{ $isCurrent ? 'font-bold' : 'font-medium' }} {{ $isCompleted ? 'text-brand-900' : 'text-gray-400' }}">
                    {{ $step['label'] }}
                </div>

                @if ($timestamp)
                    <div class="text-[0.6rem] text-brand-400">{{ $timestamp }}</div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($order->order_status === 'cancelled')
        <div class="text-center mt-4 px-4 py-2 rounded-lg text-sm" style="background:#FEE2E2;color:#991B1B;">
            Order was cancelled{{ $order->cancellation_reason ? ': '.$order->cancellation_reason : '' }}
        </div>
    @endif
</div>
