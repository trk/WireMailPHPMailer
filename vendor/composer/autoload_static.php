<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit61d4952492eece8b88f55ac9c5df45cb
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit61d4952492eece8b88f55ac9c5df45cb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit61d4952492eece8b88f55ac9c5df45cb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit61d4952492eece8b88f55ac9c5df45cb::$classMap;

        }, null, ClassLoader::class);
    }
}
