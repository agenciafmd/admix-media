<?php

namespace Agenciafmd\Media\Support\PathGenerator;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class DefaultPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->customPath($media->id) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        return $this->customPath($media->id);
    }

    private function customPath(string $key)
    {
        return 'media/' . implode('/', str_split(substr(md5($key), 0, 6), 2)) . '/' . $key;
    }
}