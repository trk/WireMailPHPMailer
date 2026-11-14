<?php

declare(strict_types=1);

namespace ProcessWire;

$moduleRoot = dirname(__DIR__);
$projectRoot = dirname($moduleRoot, 3);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

if (is_file($autoloadPath)) {
	require_once $autoloadPath;
}

if (!function_exists('ProcessWire\\wire') || ProcessWire::getCurrentInstance() === null) {
	require_once $projectRoot . '/index.php';
}

$moduleVendorAutoload = $moduleRoot . '/vendor/autoload.php';
if (is_file($moduleVendorAutoload)) {
	require_once $moduleVendorAutoload;
}

require_once $moduleRoot . '/WireMailPHPMailer.module.php';
require_once $moduleRoot . '/WireMailPHPMailerConfig.php';
require_once __DIR__ . '/Support/FakePHPMailer.php';
require_once __DIR__ . '/Support/OAuthStubs.php';
