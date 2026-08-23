@if($popup)
    @php($popupUrl = $popup->safeButtonUrl())
    <div
        class="tht-popup"
        data-tht-popup
        data-popup-id="{{ $popup->id }}"
        data-delay="{{ max(0, (int) $popup->display_delay) * 1000 }}"
        data-show-once="{{ $popup->show_once ? '1' : '0' }}"
        hidden
    >
        <button class="tht-popup__backdrop" type="button" data-popup-dismiss aria-label="Đóng popup"></button>
        <div class="tht-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="tht-popup-title-{{ $popup->id }}">
            <button class="tht-popup__close" type="button" data-popup-dismiss aria-label="Đóng popup">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>

            @if($popup->image?->url)
                <div class="tht-popup__media">
                    <img src="{{ $popup->image->url }}" alt="{{ $popup->image->alt ?: $popup->title }}">
                </div>
            @endif

            <div class="tht-popup__body">
                @if($popup->subtitle)

                @endif
                <h2 id="tht-popup-title-{{ $popup->id }}">{{ $popup->title }}</h2>
                @if($popup->content)
                    <div class="tht-popup__content">{!! $popup->content !!}</div>
                @endif
                @if($popupUrl && $popup->button_text)
                    <a
                        class="tht-popup__cta"
                        href="{{ $popupUrl }}"
                        @if($popup->button_target_blank) target="_blank" rel="noopener" @endif
                    >
                        {{ $popup->button_text }}
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
