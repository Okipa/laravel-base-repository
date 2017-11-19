<?php

use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

return [
    /**
     * When calling `optimize` the package will automatically determine which optimizers
     * should run for the given image.
     */
    'optimizers'             => [

        Jpegoptim::class => [
            '--strip-all', // progressive image
            '--all-progressive' // remove metadata
        ],

        Pngquant::class => [
            '--force', // required parameter for this package
            '--speed 1', // slowest treatment but best results
            '--strip', // remove metadata
            '--skip-if-larger', // post-treatment larger image compared to pre-treatment not saved
        ],

        Optipng::class => [
            '-i0', // non-interlaced and progressive image
            '-o7',  // slowest treatment but best results
            '-strip all', // remove metadata
            '-quiet' // required parameter for this package
        ],

        Svgo::class => [
            '--disable=cleanupIDs' // disabling because it is know to cause troubles
        ],

        Gifsicle::class => [
            '-b', // required parameter for this package
            '-O3' // slowest treatment but best results
        ],
    ],

    /**
     * The maximum time in seconds each optimizer is allowed to run separately.
     */
    'timeout'                => 60,

    /**
     * If set to `true` all output of the optimizer binaries will be appended to the default log.
     * You can also set this to a class that implements `Psr\Log\LoggerInterface`.
     */
    'log_optimizer_activity' => false,
];