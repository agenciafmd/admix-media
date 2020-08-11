@php
    $formControl = 'form-control custom-select';

    if (strpos(request()->route()->getName(), 'show') !== false) {
      $formControl = 'form-control-plaintext';
      $attributes['disabled'] = true;
    }

    $attributes['class'] = $formControl . ' ' . ($errors->admix->has($name) ? 'is-invalid ' : '') . (($attributes['class']) ?? '');
    $attributes['id'] = $attributes['id'] ?? Str::slug($name);
    $attributes['multiple'] = true;
@endphp

<li class="list-group-item">
    <div class="row gutters-sm multiple-upload">
        {{ Form::label("{$label}", null, ['class' => 'col-xl-3 col-form-label pt-0 pt-xl-2']) }}
        <div class="col-xl-9">
            {{ Form::file("file[]", $attributes) }}
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
                overwriteInitial: false,
                allowedFileExtensions: null,
                resizeImage: false,
                preferIconicPreview: true,
                previewFileIcon: '<i class="icon fe-file"></i>',
                fileActionSettings: {
                    showDrag: true,
                    showZoom: false,
                },
                uploadExtraData: function (previewId, index) {
                    return {
                        key: index,
                        collection: '{{ $name }}',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };
                },
                @if (isset($value) && $value->getMedia($name)->count() > 0)
                initialPreview: ["{!! $value->getMedia($name)->map(function($item) { return asset($item->getUrl()); })->implode('", "') !!}"],
                initialPreviewAsData: true,
                initialPreviewConfig: [
                        @foreach($value->getMedia($name) as $item)
                    {
                        caption: '{{ $item->name }}',
                        downloadUrl: '{{ asset($item->getUrl()) }}',
                        size: '{{ $item->size }}',
                        key: '{{ $item->getCustomProperty('uuid') }}'
                    },
                    @endforeach
                ],
                @endif
            }).on('filesorted', function (e, params) {
                var _token = $('meta[name="csrf-token"]').attr('content');
                $.post('{{ route('admix.upload.sort') }}', {_token: _token, stack: params.stack});
            }).on("filebatchselected", function (event, files) {
                el.fileinput("upload");
            }).on('filebatchuploadsuccess', function (event, data) {
                for (i = 0; i < data.response.length; i++) {
                    el.parents('form').append('<input type="hidden" name="media[' + data.response[i].uuid + '][name][' + i + ']" value="' + data.response[i].name + '" />');
                    el.parents('form').append('<input type="hidden" name="media[' + data.response[i].uuid + '][collection][' + i + ']" value="' + data.response[i].collection + '" />');
                }
            });
        });
    </script>
@endpush
