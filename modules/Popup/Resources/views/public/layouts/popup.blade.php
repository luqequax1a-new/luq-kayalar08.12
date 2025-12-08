@if (!empty($activePopup))
    @php
        $config = [
            'id' => $activePopup->id,
            'trigger_type' => $activePopup->trigger_type,
            'trigger_value' => $activePopup->trigger_value,
            'frequency_type' => $activePopup->frequency_type,
            'frequency_value' => $activePopup->frequency_value,
        ];
    @endphp

    <div x-data="{ imgLoaded: {{ $activePopup->image_url ? 'false' : 'true' }} }">
        <div
            x-data='MarketingPopup(@json($config))'
            x-init="init()"
            x-show="isOpen && imgLoaded"
            x-cloak
            class="popup-overlay"
            @click.self="close()"
            @keydown.escape.window="close()"
        >
            <div class="popup-modal" x-transition.opacity.duration.200ms>
            <button type="button" class="popup-close" @click="close()" aria-label="Kapat">
                &times;
            </button>

                @if ($activePopup->image_url)
                    <div class="popup-image">
                        <img
                            src="{{ $activePopup->image_url }}"
                            alt="{{ $activePopup->headline }}"
                            @load="imgLoaded = true"
                        >
                    </div>
                @endif

                <div class="popup-content">
                @if ($activePopup->headline)
                    <h3 class="popup-title">{{ $activePopup->headline }}</h3>
                @endif

                @if ($activePopup->subheadline)
                    <p class="popup-subtitle">{{ $activePopup->subheadline }}</p>
                @endif

                @if ($activePopup->body)
                    <div class="popup-body">{!! nl2br(e($activePopup->body)) !!}</div>
                @endif

                <div class="popup-actions">
                    @if ($activePopup->cta_label && $activePopup->cta_url)
                        <a href="{{ $activePopup->cta_url }}" class="btn btn-primary popup-cta">
                            {{ $activePopup->cta_label }}
                        </a>
                    @endif

                    <button type="button" class="btn btn-link popup-close-link" @click="close()">
                        {{ $activePopup->close_label ?: 'Tıkla Pencereyi Kapat' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
