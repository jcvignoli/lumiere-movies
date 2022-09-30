<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2660170a07cc3fb36d97d6c17f332311
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'L' => 
        array (
            'Lumiere\\' => 8,
        ),
        'I' => 
        array (
            'Imdb\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Lumiere\\' => 
        array (
            0 => __DIR__ . '/../..' . '/class',
        ),
        'Imdb\\' => 
        array (
            0 => __DIR__ . '/..' . '/imdbphp/imdbphp/src/Imdb',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2660170a07cc3fb36d97d6c17f332311::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2660170a07cc3fb36d97d6c17f332311::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2660170a07cc3fb36d97d6c17f332311::$classMap;

        }, null, ClassLoader::class);
    }
}