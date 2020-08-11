@php
    $formControl = 'form-control custom-select';

    if (strpos(request()->route()->getName(), 'show') !== false) {
      $formControl = 'form-control-plaintext';
      $attributes['disabled'] = true;
    }

    $attributes['class'] = $formControl . ' ' . ($errors->admix->has($name) ? 'is-invalid ' : '') . (($attributes['class']) ?? '');
    $attributes['id'] = $attributes['id'] ?? Str::slug($name);

    $modelName = strtolower(class_basename($value));
    $field = config("upload-configs.{$modelName}.{$name}.sources.0");

    if(isset($attributes['config'])) {
      $field = $attributes['config'];
      unset($attributes['config']);
    }

    $width = $field['width'] ?? 800;
    $height = $field['height'] ?? 600;
    $quality = $field['quality'] ?? 95;
    $crop = $field['crop'] ?? false;
    $conversion = $field['conversion'] . '@2x';

    /*
     * TODO: warning de que vai dar merda a largura e altura
     * */
@endphp

<li class="list-group-item">
    <div class="row gutters-sm single-upload">
        {{ Form::label("{$label} ({$width}x{$height})", null, ['class' => 'col-xl-3 col-form-label pt-0 pt-xl-2']) }}
        <div class="col-xl-5">
            {{ Form::file("file", $attributes) }}
            @include('agenciafmd/form::partials.invalid-feedback')
        </div>
        @include('agenciafmd/form::partials.helper')
    </div>
</li>

@push('scripts')
    <script>
        $(function () {
            var el = $("#{{ $attributes['id'] }}");
            el.fileinput({
                theme: "fe",
                language: "pt-BR",
                uploadExtraData: function (previewId, index) {
                    return {
                        key: index,
                        collection: '{{ $name }}',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };
                },
                maxImageWidth: '{{ $width*1.2 }}', /* aceita 20% além do máximo permitido */
                maxImageHeight: '{{ $height*1.2 }}',
                resizeImage: true,
                resizeImageQuality: '{{ number_format($quality/100, 2, '.', '') }}',
                @if (isset($value) && $value->getFirstMedia($name))
                    initialPreview: ['{{ $value->getFirstMediaUrl($name, $conversion) }}'],
                    initialPreviewAsData: true,
                    initialPreviewConfig: [
                        {
                            caption: '{{ $value->getFirstMedia($name)->name }}',
                            downloadUrl: '{{ $value->getFirstMediaUrl($name, $conversion) }}',
                            size: '{{ $value->getFirstMedia($name)->size }}',
                            key: '{{ $value->getFirstMedia($name)->getCustomProperty('uuid') }}',
                        },
                    ],
                @endif
            }).on("filebatchselected", function (event, files) {
                el.fileinput("upload");
            }).on('filebatchuploadsuccess', function (event, data) {
                el.parents('form').append('<input type="hidden" name="media[' + data.response[0].uuid + '][name]" value="' + data.response[0].name + '" />');
                el.parents('form').append('<input type="hidden" name="media[' + data.response[0].uuid + '][collection]" value="' + data.response[0].collection + '" />');
            });
        });
    </script>
@endpush
