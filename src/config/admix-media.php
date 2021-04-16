<?php

return [
    'queue' => true,

    'disk_name' => env('FILESYSTEM_DRIVER', 'local'),

    'path_generator' => \Agenciafmd\Media\Support\PathGenerator\DefaultPathGenerator::class,

    'image_optimizers' => [

        //https://webmasters.stackexchange.com/questions/102094/google-pagespeed-how-to-satisfy-the-new-image-compression-rules
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '-m95',
            '--strip-all', // this strips out all text information such as comments and EXIF data
            '--all-progressive', // this will make sure the resulting image is a progressive one
        ],

        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--quality=90-95',
            '--force', // required parameter for this package
        ],

        Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0', // this will result in a non-interlaced, progressive scanned image
            '-o2', // this set the optimization level to two (multiple IDAT compression trials)
            '-quiet', // required parameter for this package
        ],

        Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--disable=cleanupIDs', // disabling because it is known to cause troubles
        ],

        Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b', // required parameter for this package
            '-O3', // this produces the slowest but best results
        ],

        // https://medium.com/vinh-rocks/how-i-apply-webp-for-optimizing-images-9b11068db349
        Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6', // for the slowest compression method in order to get the best compression.
            '-pass 10', // for maximizing the amount of analysis pass.
            '-mt', // multithreading for some speed improvements.
            '-q 95', // quality factor that brings the least noticeable changes.
        ],
    ],

    'picture_view' => 'agenciafmd/media::picture',

    'fancy_picture_view' => 'agenciafmd/media::fancy-picture',
];