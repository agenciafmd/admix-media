<?php

namespace Agenciafmd\Media\Traits;

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

            if (!$request->media) {
                return false;
            }

            foreach ($request->media as $media) {
                if (is_array($media['collection'])) {
                    $collection = reset($media['collection']);
                    $file = storage_path('admix/tmp') . "/" . reset($media['name']);

                    $model->doUploadMultiple($file, $collection);

                } else {
                    $collection = $media['collection'];
                    $file = storage_path('admix/tmp') . "/{$media['name']}";

                    $model->doUpload($file, $collection);
                }
            }

            return true;
        });
    }

    public function picture($collection = 'image', $class = 'img-fluid')
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];
        $media = $this->getFirstMedia($collection);

        return $this->pictureHtml($media, $sources, $class);
    }

    public function fancyPicture($collection = 'image', $class = 'img-fluid', $aClass = '')
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];
        $media = $this->getFirstMedia($collection);

        $view['dataFancybox'] = $collection;
        $view['picture'] = $this->pictureHtml($media, $sources, $class);
        $view['media'] = $media;
        $view['aClass'] = $aClass;

        return view(config('admix-media.fancy_picture_view'), $view);
    }

    public function pictures($collection = 'images', $class = 'img-fluid')
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];

        return $this->getMedia($collection)
            ->map(function ($media) use ($sources, $class) {

                return $this->pictureHtml($media, $sources, $class);
            });
    }

    public function fancyPictures($collection = 'images', $class = 'img-fluid', $aClass = '')
    {
        $sources = $this->fieldsToConversion()[$collection]['sources'];

        return $this->getMedia($collection)
            ->map(function ($media) use ($collection, $sources, $class, $aClass) {
                $view['dataFancybox'] = $collection;
                $view['picture'] = $this->pictureHtml($media, $sources, $class);
                $view['media'] = $media;
                $view['aClass'] = $aClass;

                return view(config('admix-media.fancy_picture_view'), $view);
            });
    }

    private function pictureHtml($media, $sources, $class)
    {
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

        return view(config('admix-media.picture_view'), $view);
    }

    public function file($collection = 'file')
    {
        return $this->getFirstMedia($collection)
            ->getUrl();
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

        $this->clearMediaCollection($collection)
            ->addMedia($file)
            ->usingName($name)
            ->usingFileName($fileName)
            ->withCustomProperties(array_merge(['uuid' => Str::uuid()], $customProperties))
            ->toMediaCollection($collection);
    }

    public function doUploadMultiple($file, $collection = 'images', $customProperties = [])
    {
        $name = Str::slug($this->attributes['name'] . '-' . date('YmdHisv'));
        $fileName = $name . '.' . Str::lower(pathinfo($file)['extension']);

        $this->addMedia($file)
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
                    $convertionName = $source['conversion'];
                    $width = $source['width'] ?? 800;
                    $height = $source['height'] ?? 600;
                    $crop = ($source['crop']) ?? true;
                    $optimize = ($source['optimize']) ?? true;
                    $quality = ($source['quality']) ?? 100;
                    $webp = Str::contains($convertionName, '-webp') ? true : false;

                    $conversion = $this->addMediaConversion($convertionName);

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

    public function conversionsCollection($collection)
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
}
