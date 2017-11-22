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
        Jpegoptim::class => ['--strip-all', '--all-progressive', '-m85', '--quiet'],
        Pngquant::class  => ['--force', '--speed 1', '--strip'],
        Optipng::class   => ['-i0', '-o7', '-strip all', '-quiet'],
        Svgo::class      => ['--disable=cleanupIDs'],
        Gifsicle::class  => ['-b', '-O3'],
    ],

    /**
     * The maximum time in seconds each optimizer is allowed to run separately.
     */
    'timeout'                => 60,

    /**
     * If set to `true` all output of the optimizer binaries will be appended to the default log.
     * You can also set this to a class that implements `Psr\Log\LoggerInterface`.
     */
    'log_optimizer_activity' => true,
];