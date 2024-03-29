<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5172d61586eb1ec71a4752927b451320
{
    public static $prefixLengthsPsr4 = array (
        'a' => 
        array (
            'appsaloon\\woo_pvt\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'appsaloon\\woo_pvt\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5172d61586eb1ec71a4752927b451320::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5172d61586eb1ec71a4752927b451320::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
