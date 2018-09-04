<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitda55e3c14e6c3d9f00bf7dad1c942911
{
    public static $files = array (
        '7b351e4cba4e2e5fd7f67d5c31305865' => __DIR__ . '/../..' . '/lib/func/exp.php',
        '3691070d8839d2511aa8886d85d1e33a' => __DIR__ . '/../..' . '/lib/func/callback.php',
    );

    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'mdocker\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'mdocker\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitda55e3c14e6c3d9f00bf7dad1c942911::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitda55e3c14e6c3d9f00bf7dad1c942911::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
