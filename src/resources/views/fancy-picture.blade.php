<a href="{{ $media->getUrl() }}"
   data-fancybox="{{ $dataFancybox }}"
   data-caption="{{ optional($media->getCustomProperty('meta'))[app()->getLocale()] ?? $media->name }}"
   title="{{ optional($media->getCustomProperty('meta'))[app()->getLocale()] ?? $media->name }}"
   aria-label="{{ optional($media->getCustomProperty('meta'))[app()->getLocale()] ?? $media->name }}"
   class="{{ $aClass }}">
    {!! $picture !!}</a>
