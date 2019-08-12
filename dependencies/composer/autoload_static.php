<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit329e16705b60bd2b58b2375e8c17afaa
{
    public static $files = array (
        'decc78cc4436b1292c6c0d151b19445c' => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'p' => 
        array (
            'phpseclib\\' => 10,
        ),
        'I' => 
        array (
            'IU\\RedCapEtlModule\\' => 19,
            'IU\\REDCapETL\\' => 13,
            'IU\\PHPCap\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'phpseclib\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib',
        ),
        'IU\\RedCapEtlModule\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
            1 => __DIR__ . '/../..' . '/classes',
        ),
        'IU\\REDCapETL\\' => 
        array (
            0 => __DIR__ . '/..' . '/iu-redcap/redcap-etl/src',
        ),
        'IU\\PHPCap\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpcap/phpcap/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit329e16705b60bd2b58b2375e8c17afaa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit329e16705b60bd2b58b2375e8c17afaa::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
