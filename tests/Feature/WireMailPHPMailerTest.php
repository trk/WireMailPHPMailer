<?php

declare(strict_types=1);

namespace ProcessWire\Tests\Feature;

use PHPMailer\PHPMailer\PHPMailer;
use ProcessWire\ConfigurableModule;
use ProcessWire\Module;
use ProcessWire\Tests\Support\FakePHPMailer;
use ProcessWire\WireException;
use ProcessWire\WireMail;
use ProcessWire\WireMailPHPMailer;

require_once dirname(__DIR__) . '/bootstrap.php';

function makeTestMail(?FakePHPMailer &$fake = null): WireMailPHPMailer
{
	/** @var WireMailPHPMailer $mail */
	$mail = \ProcessWire\wire('modules')->get('WireMailPHPMailer');
	$fake = new FakePHPMailer();
	$mail->setMailer($fake);
	return $mail;
}

test('WireMailPHPMailer satisfies ProcessWire Module and WireMail contracts', function (): void {
	$info = WireMailPHPMailer::getModuleInfo();

	expect($info['version'])->toBeInt()->toBeGreaterThanOrEqual(151)
		->and($info['singular'])->toBeFalse()
		->and($info['autoload'])->toBeFalse()
		->and($info['requires'])->toContain('ProcessWire>=3.0.210')
		->and($info['requires'])->toContain('PHP>=8.4.0');

	$mail = makeTestMail();

	expect($mail)->toBeInstanceOf(WireMail::class)
		->and($mail)->toBeInstanceOf(Module::class)
		->and($mail)->toBeInstanceOf(ConfigurableModule::class)
		->and($mail->className())->toBe('WireMailPHPMailer');

	// Verify transparent CMS provider resolution
	$cmsMail = \ProcessWire\wireMail();
	expect($cmsMail)->toBeInstanceOf(WireMailPHPMailer::class);

	$serviceMail = \ProcessWire\wire('mail')->new();
	expect($serviceMail)->toBeInstanceOf(WireMailPHPMailer::class);
});

test('WireMailPHPMailer parses recipients via single, comma-separated, RFC, and array formats', function (): void {
	$mail = makeTestMail($fake);

	// 1. Single email without name
	$mail->to('alice@example.com');
	$mail->send();
	expect($fake->getToAddresses())->toHaveCount(1)
		->and($fake->getToAddresses()[0][0])->toBe('alice@example.com')
		->and($fake->getToAddresses()[0][1])->toBe('');

	// 2. Single email with name
	$mail->to('bob@example.com', 'Bob Smith');
	$mail->send();
	expect($fake->getToAddresses())->toHaveCount(1)
		->and($fake->getToAddresses()[0][0])->toBe('bob@example.com')
		->and($fake->getToAddresses()[0][1])->toBe('Bob Smith');

	// 3. Comma-separated list
	$mail->to('carol@example.com, dave@example.com');
	$mail->send();
	expect($fake->getToAddresses())->toHaveCount(2)
		->and($fake->getToAddresses()[0][0])->toBe('carol@example.com')
		->and($fake->getToAddresses()[1][0])->toBe('dave@example.com');

	// 4. RFC 2822 format "Name <email>"
	$mail->to('Eve Adams <eve@example.com>');
	$mail->send();
	expect($fake->getToAddresses())->toHaveCount(1)
		->and($fake->getToAddresses()[0][0])->toBe('eve@example.com')
		->and($fake->getToAddresses()[0][1])->toBe('Eve Adams');

	// 5. Indexed array of emails
	$mail->to(['frank@example.com', 'grace@example.com']);
	$mail->send();
	expect($fake->getToAddresses())->toHaveCount(2)
		->and($fake->getToAddresses()[0][0])->toBe('frank@example.com')
		->and($fake->getToAddresses()[1][0])->toBe('grace@example.com');

	// 6. Associative array [email => name]
	$mail->to([
		'heidi@example.com' => 'Heidi White',
		'ivan@example.com' => 'Ivan Black',
	]);
	$mail->send();
	expect($fake->getToAddresses())->toHaveCount(2)
		->and($fake->getToAddresses()[0][0])->toBe('heidi@example.com')
		->and($fake->getToAddresses()[0][1])->toBe('Heidi White')
		->and($fake->getToAddresses()[1][0])->toBe('ivan@example.com')
		->and($fake->getToAddresses()[1][1])->toBe('Ivan Black');
});

test('WireMailPHPMailer manages CC and BCC recipients and supports reset via null', function (): void {
	$mail = makeTestMail($fake);

	// Add CC and BCC recipients using various formats
	$mail->to('recipient@example.com')
		->cc('cc1@example.com', 'CC One')
		->cc('CC Two <cc2@example.com>')
		->bcc(['bcc1@example.com' => 'BCC One', 'bcc2@example.com' => 'BCC Two']);

	expect($mail->get('cc'))->toHaveKeys(['cc1@example.com', 'cc2@example.com'])
		->and($mail->get('bcc'))->toHaveKeys(['bcc1@example.com', 'bcc2@example.com']);

	$arrayData = $mail->getArray();
	expect($arrayData['cc'])->toBeArray()
		->and($arrayData['bcc'])->toBeArray()
		->and($arrayData['cc'])->toHaveKey('cc1@example.com')
		->and($arrayData['bcc'])->toHaveKey('bcc1@example.com');

	$mail->send();

	expect($fake->getCcAddresses())->toHaveCount(2)
		->and($fake->getCcAddresses()[0][0])->toBe('cc1@example.com')
		->and($fake->getCcAddresses()[0][1])->toBe('CC One')
		->and($fake->getCcAddresses()[1][0])->toBe('cc2@example.com')
		->and($fake->getCcAddresses()[1][1])->toBe('CC Two')
		->and($fake->getBccAddresses())->toHaveCount(2)
		->and($fake->getBccAddresses()[0][0])->toBe('bcc1@example.com')
		->and($fake->getBccAddresses()[0][1])->toBe('BCC One')
		->and($fake->getBccAddresses()[1][0])->toBe('bcc2@example.com')
		->and($fake->getBccAddresses()[1][1])->toBe('BCC Two');

	// Test discrete reset with cc(null) and bcc(null)
	$mail->to('user@example.com')
		->cc('cc@example.com')
		->bcc('bcc@example.com');
	expect($mail->get('cc'))->not->toBeEmpty()
		->and($mail->get('bcc'))->not->toBeEmpty();

	$mail->cc(null);
	expect($mail->get('cc'))->toBe([]);

	$mail->bcc(null);
	expect($mail->get('bcc'))->toBe([]);

	// Test that to(null) resets to, cc, and bcc
	$mail->to('primary@example.com')
		->cc('audit@example.com')
		->bcc('archive@example.com');

	$mail->to(null);
	expect($mail->get('to'))->toBe([])
		->and($mail->get('cc'))->toBe([])
		->and($mail->get('bcc'))->toBe([]);

	// Test property setter set('cc', ...) and set('bcc', ...)
	$mail->set('cc', 'team@example.com');
	$mail->set('bcc', 'logs@example.com');
	expect($mail->get('cc'))->toHaveKey('team@example.com')
		->and($mail->get('bcc'))->toHaveKey('logs@example.com');

	$mail->set('cc', null);
	$mail->set('bcc', null);
	expect($mail->get('cc'))->toBe([])
		->and($mail->get('bcc'))->toBe([]);
});

test('WireMailPHPMailer deduplicates CC and BCC addresses specified via custom headers', function (): void {
	$mail = makeTestMail($fake);

	$mail->to('main@example.com')
		->cc('audit@example.com', 'Audit Desk')
		->header('Cc', 'Audit Desk <audit@example.com>, another@example.com')
		->bcc('archive@example.com')
		->header('Bcc', 'archive@example.com, bcc2@example.com');

	$mail->send();

	// CC list should have audit@example.com only ONCE and another@example.com
	$ccEmails = array_column($fake->getCcAddresses(), 0);
	expect($ccEmails)->toHaveCount(2)
		->and($ccEmails)->toContain('audit@example.com')
		->and($ccEmails)->toContain('another@example.com');

	// BCC list should have archive@example.com only ONCE and bcc2@example.com
	$bccEmails = array_column($fake->getBccAddresses(), 0);
	expect($bccEmails)->toHaveCount(2)
		->and($bccEmails)->toContain('archive@example.com')
		->and($bccEmails)->toContain('bcc2@example.com');
});

test('WireMailPHPMailer neutralizes RFC header injections in names, subjects, and custom headers', function (): void {
	$mail = makeTestMail($fake);

	// Injected CRLF in recipient name and subject
	$mail->to('target@example.com', "Injected\r\nBcc: evil@attacker.com")
		->from('sender@example.com', "Sender\nName\rInjection")
		->subject("Clean Subject\r\nInjected-Header: evil");

	$mail->send();

	// Newlines should be stripped
	expect($fake->getToAddresses()[0][1])->not->toContain("\r")
		->and($fake->getToAddresses()[0][1])->not->toContain("\n")
		->and($fake->FromName)->not->toContain("\r")
		->and($fake->FromName)->not->toContain("\n")
		->and($fake->Subject)->not->toContain("\r")
		->and($fake->Subject)->not->toContain("\n");
});

test('WireMailPHPMailer manages Senders, Reply-To, and envelope return-path sender', function (): void {
	$mail = makeTestMail($fake);

	// Explicit sender
	$mail->to('user@example.com')
		->from('orders@example.com', 'Orders Dept')
		->replyTo('help@example.com', 'Customer Support')
		->param('-f bounce-handler@example.com');

	$mail->send();

	$replyTos = $fake->getReplyToAddresses();
	expect($fake->From)->toBe('orders@example.com')
		->and($fake->FromName)->toBe('Orders Dept')
		->and($fake->Sender)->toBe('bounce-handler@example.com')
		->and($replyTos)->toHaveCount(1)
		->and($replyTos[0][0])->toBe('help@example.com')
		->and($replyTos[0][1])->toBe('Customer Support');

	// Reply-To header fallback when explicit replyTo is not set
	$mail2 = makeTestMail($fake2);
	$mail2->to('user@example.com')
		->from('info@example.com')
		->header('Reply-To', 'Direct Help <direct@example.com>');

	$mail2->send();
	$replyTos2 = $fake2->getReplyToAddresses();
	expect($replyTos2)->toHaveCount(1)
		->and($replyTos2[0][0])->toBe('direct@example.com')
		->and($replyTos2[0][1])->toBe('Direct Help');
});

test('WireMailPHPMailer handles plain text only, HTML only, and multipart message bodies', function (): void {
	// 1. Plain text only
	$mail1 = makeTestMail($fake1);
	$mail1->to('user@example.com')
		->body('Hello plain world.');
	$mail1->send();

	expect($fake1->ContentType)->toBe(PHPMailer::CONTENT_TYPE_PLAINTEXT)
		->and($fake1->Body)->toBe('Hello plain world.')
		->and($fake1->AltBody)->toBe('');

	// 2. HTML only: auto-generates text AltBody via html2text()
	$mail2 = makeTestMail($fake2);
	$mail2->to('user@example.com')
		->bodyHTML('<h2>Important Update</h2><p>Here is your <b>content</b>.</p>');
	$mail2->send();

	expect($fake2->ContentType)->toBe(PHPMailer::CONTENT_TYPE_TEXT_HTML)
		->and($fake2->Body)->toBe('<h2>Important Update</h2><p>Here is your <b>content</b>.</p>')
		->and($fake2->AltBody)->toContain('Important Update')
		->and($fake2->AltBody)->toContain('content');

	// 3. Multipart HTML + explicit Plain text
	$mail3 = makeTestMail($fake3);
	$mail3->to('user@example.com')
		->body('Plain text fallback version')
		->bodyHTML('<h1>Rich HTML version</h1>');
	$mail3->send();

	expect($fake3->ContentType)->toBe(PHPMailer::CONTENT_TYPE_TEXT_HTML)
		->and($fake3->Body)->toBe('<h1>Rich HTML version</h1>')
		->and($fake3->AltBody)->toBe('Plain text fallback version');
});

test('WireMailPHPMailer manages file attachments safely', function (): void {
	$mail = makeTestMail($fake);

	// Create real temporary test files
	$tmpFile1 = tempnam(sys_get_temp_dir(), 'wm_test_1_');
	$tmpFile2 = tempnam(sys_get_temp_dir(), 'wm_test_2_');
	file_put_contents($tmpFile1, 'Report content 1');
	file_put_contents($tmpFile2, 'Invoice content 2');

	try {
		// Valid file with custom basename
		$mail->to('user@example.com')
			->attachment($tmpFile1, 'QuarterlyReport.pdf')
			->attachment($tmpFile2);

		// Non-existent file path should be ignored
		$mail->attachment('/tmp/nonexistent_file_path_' . uniqid() . '.txt');

		$mail->send();

		$attachments = $fake->getAttachments();
		expect($attachments)->toHaveCount(2)
			->and($attachments[0][0])->toBe($tmpFile1)
			->and($attachments[0][2])->toBe('QuarterlyReport.pdf')
			->and($attachments[1][0])->toBe($tmpFile2);

		// Reset attachments via attachment(null)
		$mail->attachment($tmpFile1);
		expect($mail->get('attachments'))->not->toBeEmpty();
		$mail->attachment(null);
		expect($mail->get('attachments'))->toBe([]);

		// Set attachments via set('attachments', ...)
		$mail->set('attachments', [
			'CustomDoc.pdf' => $tmpFile1,
			$tmpFile2,
		]);
		expect($mail->get('attachments'))->toHaveCount(2);

		$mail->set('attachments', null);
		expect($mail->get('attachments'))->toBe([]);
	} finally {
		@unlink($tmpFile1);
		@unlink($tmpFile2);
	}
});

test('WireMailPHPMailer switches transports between mail, sendmail, and smtp', function (): void {
	// 1. Mail transport
	$mail1 = makeTestMail($fake1);
	$mail1->setArray(['Mailer' => 'mail']);
	$mail1->to('user@example.com')->send();
	expect($fake1->Mailer)->toBe('mail');

	// 2. Sendmail transport
	$mail2 = makeTestMail($fake2);
	$mail2->setArray([
		'Mailer' => 'sendmail',
		'Sendmail' => '/usr/local/bin/sendmail',
		'UseSendmailOptions' => true,
	]);
	$mail2->to('user@example.com')->send();
	expect($fake2->Mailer)->toBe('sendmail')
		->and($fake2->Sendmail)->toBe('/usr/local/bin/sendmail')
		->and($fake2->UseSendmailOptions)->toBeTrue();

	// 3. SMTP transport with authentication and TLS
	$mail3 = makeTestMail($fake3);
	$mail3->setArray([
		'Mailer' => 'smtp',
		'Host' => 'smtp.testmail.com',
		'Port' => 587,
		'SMTPSecure' => 'tls',
		'SMTPAuth' => true,
		'Username' => 'app_mailer',
		'Password' => 'secret_pass_123',
		'SMTPAutoTLS' => true,
		'SMTPKeepAlive' => true,
		'Timeout' => 45,
	]);
	$mail3->to('user@example.com')->send();

	expect($fake3->Mailer)->toBe('smtp')
		->and($fake3->Host)->toBe('smtp.testmail.com')
		->and($fake3->Port)->toBe(587)
		->and($fake3->SMTPSecure)->toBe('tls')
		->and($fake3->SMTPAuth)->toBeTrue()
		->and($fake3->Username)->toBe('app_mailer')
		->and($fake3->Password)->toBe('secret_pass_123')
		->and($fake3->SMTPAutoTLS)->toBeTrue()
		->and($fake3->SMTPKeepAlive)->toBeTrue()
		->and($fake3->Timeout)->toBe(45);
});

test('WireMailPHPMailer parses SMTP DSN strings via DSNConfigurator and overrides discrete settings', function (): void {
	$mail = makeTestMail($fake);
	$mail->setArray([
		'Mailer' => 'smtp',
		'Host' => 'discrete.host.com', // Should be overridden by DSN
		'Port' => 25,                 // Should be overridden by DSN
		'dsn' => 'smtps://myuser:mypassword@smtp.sendgrid.net:465',
	]);

	$mail->to('user@example.com')->send();

	expect($fake->Mailer)->toBe('smtp')
		->and($fake->Host)->toBe('smtp.sendgrid.net')
		->and($fake->Port)->toBe(465)
		->and($fake->Username)->toBe('myuser')
		->and($fake->Password)->toBe('mypassword')
		->and($fake->SMTPSecure)->toBeIn(['tls', 'ssl']);
});

test('WireMailPHPMailer applies DKIM and priority configuration properties', function (): void {
	$mail = makeTestMail($fake);
	$mail->setArray([
		'DKIM_domain' => 'example.org',
		'DKIM_selector' => 'mail2026',
		'DKIM_identity' => 'bounce@example.org',
		'DKIM_passphrase' => 'dkim_pass_123',
		'DKIM_private_string' => '-----BEGIN RSA PRIVATE KEY-----TEST-----END RSA PRIVATE KEY-----',
		'DKIM_copyHeaderFields' => true,
		'Priority' => 1,
		'WordWrap' => 78,
	]);

	$mail->to('user@example.com')->send();

	expect($fake->DKIM_domain)->toBe('example.org')
		->and($fake->DKIM_selector)->toBe('mail2026')
		->and($fake->DKIM_identity)->toBe('bounce@example.org')
		->and($fake->DKIM_passphrase)->toBe('dkim_pass_123')
		->and($fake->DKIM_private_string)->toBe('-----BEGIN RSA PRIVATE KEY-----TEST-----END RSA PRIVATE KEY-----')
		->and($fake->DKIM_copyHeaderFields)->toBeTrue()
		->and($fake->Priority)->toBe(1)
		->and($fake->WordWrap)->toBe(78);
});

test('WireMailPHPMailer handles custom headers and priority variations', function (): void {
	$mail = makeTestMail($fake);

	$mail->to('user@example.com')
		->header('X-Custom-Tracking-ID', 'TRK-987654')
		->header('X-Campaign', 'SpringLaunch');

	$mail->send();

	$headers = $fake->getCustomHeaders();
	$foundTracking = false;
	$foundCampaign = false;
	foreach ($headers as $h) {
		if ($h[0] === 'X-Custom-Tracking-ID' && $h[1] === 'TRK-987654') {
			$foundTracking = true;
		}
		if ($h[0] === 'X-Campaign' && $h[1] === 'SpringLaunch') {
			$foundCampaign = true;
		}
	}
	expect($foundTracking)->toBeTrue()
		->and($foundCampaign)->toBeTrue();

	// Setting header to null removes it
	$mail->header('X-Campaign', null);
	expect($mail->get('header'))->not->toHaveKey('X-Campaign');
});

test('WireMailPHPMailer guarantees exception safety and accurate return counts', function (): void {
	// 1. Successful send with single recipient returns 1
	$mail1 = makeTestMail($fake1);
	$mail1->to('user1@example.com');
	$sentCount1 = $mail1->send();
	expect($sentCount1)->toBe(1);

	// 2. Successful send with multiple recipients returns recipient count
	$mail2 = makeTestMail($fake2);
	$mail2->to(['user1@example.com', 'user2@example.com', 'user3@example.com']);
	$sentCount2 = $mail2->send();
	expect($sentCount2)->toBe(3);

	// 3. Successful send with only BCC returns 1
	$mail3 = makeTestMail($fake3);
	$mail3->bcc('bcc_only@example.com');
	$sentCount3 = $mail3->send();
	expect($sentCount3)->toBe(1);

	// 4. Send failure returns 0 and logs error
	$mail4 = makeTestMail($fake4);
	$fake4->mockSendResult = false;
	$fake4->mockFailureError = 'Simulated SMTP connection timeout';
	$mail4->to('user@example.com');
	$sentCount4 = $mail4->send();
	expect($sentCount4)->toBe(0);

	// 5. Exception during send is caught, logged, and returns 0 without crashing
	$mail5 = makeTestMail($fake5);
	$fake5->throwOnSend = new \RuntimeException('Fatal network socket error during transmission');
	$mail5->to('user@example.com');
	$sentCount5 = $mail5->send();
	expect($sentCount5)->toBe(0);
});

test('WireMailPHPMailer resets message state in finally block after send', function (): void {
	$mail = makeTestMail($fake);

	$mail->to('user@example.com')
		->cc('cc@example.com')
		->bcc('bcc@example.com')
		->from('sender@example.com')
		->subject('Reset Test Subject')
		->body('Plain Body')
		->bodyHTML('<p>HTML Body</p>');

	$mail->send();

	// In finally block, resetMessageState(true) was invoked
	expect($mail->get('to'))->toBe([])
		->and($mail->get('cc'))->toBe([])
		->and($mail->get('bcc'))->toBe([])
		->and($mail->get('from'))->toBe('')
		->and($mail->get('subject'))->toBe('')
		->and($mail->get('body'))->toBe('')
		->and($mail->get('bodyHTML'))->toBe('')
		->and($mail->get('attachments'))->toBe([]);
});
