<?php

declare(strict_types=1);

namespace ProcessWire\Tests;

use ProcessWire\Tests\Support\FakePHPMailer;
use ProcessWire\WireMailPHPMailer;

require_once __DIR__ . '/bootstrap.php';

/**
 * Instantiate a WireMailPHPMailer wired to a FakePHPMailer test double
 */
function createFakeMailer(?FakePHPMailer &$fake = null): WireMailPHPMailer
{
	/** @var WireMailPHPMailer $mail */
	$mail = \ProcessWire\wire('modules')->get('WireMailPHPMailer');
	$fake = new FakePHPMailer();
	$mail->setMailer($fake);
	return $mail;
}
