<picture class="{{ $pictureClass }}">
    @foreach($conversions as $mediaQuery => $conversion)
        <source media="{{ $mediaQuery }}"
                srcset="{{ $conversion['conversionWebp'] }}, {{ $conversion['conversionWebp2x'] }} 2x">
        <source media="{{ $mediaQuery }}"
                srcset="{{ $conversion['conversion'] }}, {{ $conversion['conversion2x'] }} 2x">
    @endforeach
    <img src="data:image/png;base64, R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=="
         loading="lazy"
         decoding="async"
         width="{{ $width }}"
         height="{{ $height }}"
         class="{{ $class }}"
         title="{{ $title }}"
         alt="{{ $title }}"></picture>