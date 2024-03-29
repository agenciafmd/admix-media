<?php

namespace Agenciafmd\Media\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait MediaTrait
{
    use InteractsWithMedia;

    public static function bootMediaTrait()
    {
        static::saved(function ($model) {
            $request = request();

            if ($request->media) {
                foreach ($request->media as $media) {
                    if (is_array($media['collection'])) {
                        $collection = reset($media['collection']);
                        $model->doUploadMultiple(reset($media['name']), $collection);

                    } else {
                        $collection = $media['collection'];
                        $model->doUpload($media['name'], $collection);
                    }
                }
            }
        });
    }

    public function picture($collection = 'image', $class = 'img-fluid', $pictureClass = '', $lazy = true)
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];
        $media = $this->getFirstMedia($collection);

        return $this->pictureHtml($media, $sources, $class, $pictureClass, $lazy);
    }

    public function fancyPicture($collection = 'image', $class = 'img-fluid', $aClass = '', $pictureClass = '', $lazy = true)
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];
        $media = $this->getFirstMedia($collection);

        $view['dataFancybox'] = $collection;
        $view['picture'] = $this->pictureHtml($media, $sources, $class, $pictureClass, $lazy);
        $view['media'] = $media;
        $view['aClass'] = $aClass;

        return view(config('admix-media.fancy_picture_view'), $view);
    }

    public function pictures($collection = 'images', $class = 'img-fluid', $pictureClass = '', $lazy = true)
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];

        return $this->getMedia($collection)
            ->map(function ($media) use ($sources, $class, $pictureClass, $lazy) {

                return $this->pictureHtml($media, $sources, $class, $pictureClass, $lazy);
            });
    }

    public function picturesWithMeta($collection = 'images', $class = 'img-fluid', $pictureClass = '', $lazy = true)
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];

        return $this->getMedia($collection)
            ->map(function ($media) use ($sources, $class, $pictureClass, $lazy) {

                return (object) [
                    'picture' => $this->pictureHtml($media, $sources, $class, $pictureClass, $lazy),
                    'meta' => optional($media->getCustomProperty('meta')),
                ];
            });
    }

    public function fancyPictures($collection = 'images', $class = 'img-fluid', $aClass = '', $pictureClass = '', $lazy = true)
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];

        return $this->getMedia($collection)
            ->map(function ($media) use ($collection, $sources, $class, $aClass, $pictureClass, $lazy) {
                $view['dataFancybox'] = $collection;
                $view['picture'] = $this->pictureHtml($media, $sources, $class, $pictureClass, $lazy);
                $view['media'] = $media;
                $view['aClass'] = $aClass;

                return view(config('admix-media.fancy_picture_view'), $view);
            });
    }

    public function fancyPicturesWithMeta($collection = 'images', $class = 'img-fluid', $aClass = '', $pictureClass = '', $lazy = true)
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];

        return $this->getMedia($collection)
            ->map(function ($media) use ($collection, $sources, $class, $aClass, $pictureClass, $lazy) {
                $view['dataFancybox'] = $collection;
                $view['picture'] = $this->pictureHtml($media, $sources, $class, $pictureClass, $lazy);
                $view['media'] = $media;
                $view['aClass'] = $aClass;

                return (object) [
                    'picture' => view(config('admix-media.fancy_picture_view'), $view),
                    'meta' => optional($media->getCustomProperty('meta')),
                ];
            });
    }

    public function file($collection = 'file')
    {
        return $this->getFirstMediaUrl($collection);
    }

    public function files($collection = 'files')
    {
        return $this->getMedia($collection)
            ->map(function ($media) {

                return $media->getUrl();
            });
    }

    public function doUpload($file, $collection = 'image', $customProperties = [])
    {
        $name = Str::slug($this->attributes['name'] . '-' . date('YmdHisv'));
        $fileName = $name . '.' . Str::lower(pathinfo($file)['extension']);
        $contents = Storage::get($file);

        $this->clearMediaCollection($collection)
            ->addMediaFromString($contents)
            ->usingName($name)
            ->usingFileName($fileName)
            ->withCustomProperties(array_merge(['uuid' => Str::uuid()], $customProperties))
            ->toMediaCollection($collection);
    }

    public function doUploadMultiple($file, $collection = 'images', $customProperties = [])
    {
        $name = Str::slug($this->attributes['name'] . '-' . date('YmdHisv'));
        $fileName = $name . '.' . Str::lower(pathinfo($file)['extension']);
        $contents = Storage::get($file);

        $this->addMediaFromString($contents)
            ->usingName($name)
            ->usingFileName($fileName)
            ->withCustomProperties(array_merge(['uuid' => Str::uuid()], $customProperties))
            ->toMediaCollection($collection);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $fields = $this->fieldsToConversion();
        foreach ($fields as $collection => $field) {
            foreach ($this->conversionsCollection($collection) as $sources) {
                foreach ($sources as $source) {
                    $conversionName = $source['conversion'];
                    $width = $source['width'] ?? 800;
                    $height = $source['height'] ?? 600;
                    $crop = ($source['crop']) ?? true;
                    $optimize = ($source['optimize']) ?? true;
                    $quality = ($source['quality']) ?? 100;
                    $webp = Str::contains($conversionName, '-webp');

                    $conversion = $this->addMediaConversion($conversionName);

                    if ($crop) {
                        $conversion->fit(Manipulations::FIT_CROP, $width, $height);
                    } else {
                        $conversion->width($width)
                            ->height($height);
                    }

                    if ($optimize) {
                        $conversion->optimize(config('admix-media.image_optimizers'));
                    } else {
                        $conversion->nonOptimized();
                    }

                    if ($quality < 100) {
                        $conversion->quality($quality);
                    }

                    if ($webp) {
                        $conversion->format(Manipulations::FORMAT_WEBP);
                    } else {
                        $conversion->keepOriginalImageFormat();
                    }

                    $conversion->performOnCollections($collection);

                    if (!config('admix-media.queue')) {
                        $conversion->nonQueued();
                    }
                }
            }
        }
    }

    public function conversionsCollection($collection): Collection
    {
        $fields = collect($this->fieldsToConversion()[$collection]['sources']);
        return $fields->map(function ($field) {

            $field2x = $field;
            $field2x['conversion'] = $field['conversion'] . '@2x';

            $fieldWebp2x = $field;
            $fieldWebp2x['conversion'] = $field['conversion'] . '-webp@2x';

            $fieldWebp = $field;
            $fieldWebp['conversion'] = $field['conversion'] . '-webp';
            $fieldWebp['width'] = (int)round($field['width'] / 2);
            $fieldWebp['height'] = (int)round($field['height'] / 2);

            $field['width'] = (int)round($field['width'] / 2);
            $field['height'] = (int)round($field['height'] / 2);

            return collect([
                $field,
                $field2x,
                $fieldWebp,
                $fieldWebp2x,
            ]);
        });
    }

    public function fieldsToConversion()
    {
        $modelName = strtolower(class_basename($this));

        return config("upload-configs.{$modelName}");
    }

    private function pictureHtml($media, $sources, $class, $pictureClass, $lazy = true)
    {
        if (! $media) {
            return false;
        }

        foreach ($sources as $source) {
            $conversions[$source['media']] = [
                'conversion' => $media->getUrl($source['conversion']),
                'conversion2x' => str_replace('%40', '@', $media->getUrl($source['conversion'] . '@2x')),
                'conversionWebp' => $media->getUrl($source['conversion'] . '-webp'),
                'conversionWebp2x' => str_replace('%40', '@', $media->getUrl($source['conversion'] . '-webp@2x')),
            ];
        }

        $view['conversions'] = $conversions;
        $view['title'] = optional($media->getCustomProperty('meta'))[app()->getLocale()] ?? $media->name;
        $view['class'] = $class;
        $view['pictureClass'] = $pictureClass;
        $view['width'] = $sources[0]['width'] / 2;
        $view['height'] = $sources[0]['height'] / 2;
        $view['lazy'] = $lazy;

        return view(config('admix-media.picture_view'), $view);
    }
}
