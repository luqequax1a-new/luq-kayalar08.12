@php
    /** @var \Modules\Product\Entities\Product $product */
    $context = $context ?? 'listing';
    $badges = $product->badgeVisualsFor($context);
    $grouped = $badges->groupBy(function ($badge) use ($context) {
        return $badge->positionFor($context);
    });
@endphp

@if ($badges->isNotEmpty())
    @foreach (['top_left', 'top_right', 'bottom_left', 'bottom_right'] as $pos)
        @php $items = $grouped->get($pos, collect()); @endphp
        @if ($items->isNotEmpty())
            <div class="product-badge-labels product-badge-labels--{{ $context }} product-badge-labels--{{ $pos }}">
                @foreach ($items as $badge)
                    <div class="product-badge-label">
                        @if ($badge->image_url)
                            <img src="{{ $badge->image_url }}" alt="{{ $badge->name }}" loading="lazy">
                        @else
                            <span>{{ $badge->name }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
@endif
