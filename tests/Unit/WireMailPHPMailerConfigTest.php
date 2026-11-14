<?php

declare(strict_types=1);

namespace ProcessWire\Tests\Unit;

use ProcessWire\HookEvent;
use ProcessWire\InputfieldWrapper;
use ProcessWire\Tests\Support\FakePHPMailer;
use ProcessWire\WireMailPHPMailer;
use ProcessWire\WireMailPHPMailerConfig;

require_once dirname(__DIR__) . '/bootstrap.php';

test('WireMailPHPMailerConfig defines all expected configuration tabs and default fields', function (): void {
	$config = new WireMailPHPMailerConfig();
	$inputfields = $config->getInputfields();

	expect($inputfields)->toBeInstanceOf(InputfieldWrapper::class);

	// Verify primary tabs are declared
	$tabGeneral = $inputfields->getChildByName('TabGeneral');
	$tabRouting = $inputfields->getChildByName('TabRouting');
	$tabTransport = $inputfields->getChildByName('TabTransport');
	$tabDkim = $inputfields->getChildByName('TabDKIM');
	$tabAdvanced = $inputfields->getChildByName('TabAdvanced');
	$tabTest = $inputfields->getChildByName('TabTest');

	expect($tabGeneral)->not->toBeNull()
		->and($tabRouting)->not->toBeNull()
		->and($tabTransport)->not->toBeNull()
		->and($tabDkim)->not->toBeNull()
		->and($tabAdvanced)->not->toBeNull()
		->and($tabTest)->not->toBeNull();

	// Verify TabGeneral child fields
	$tabGeneralChildren = [];
	foreach ($tabGeneral->children() as $child) {
		$tabGeneralChildren[$child->name] = true;
	}
	expect($tabGeneralChildren)->toHaveKeys([
		'Priority',
		'CharSet',
		'ContentType',
		'Encoding',
		'WordWrap',
		'AllowEmpty',
	]);

	// Verify TabRouting child fields
	$tabRoutingChildren = [];
	foreach ($tabRouting->children() as $child) {
		$tabRoutingChildren[$child->name] = true;
	}
	expect($tabRoutingChildren)->toHaveKeys([
		'Sender',
		'FromName',
		'From',
		'Subject',
	]);

	// Verify TabTransport child fields & nested fieldsets
	$tabTransportChildren = [];
	foreach ($tabTransport->children() as $child) {
		$tabTransportChildren[$child->name] = true;
	}
	expect($tabTransportChildren)->toHaveKeys([
		'Mailer',
		'SendmailSettings',
		'SMTP',
	]);

	$smtpFieldset = $tabTransport->getChildByName('SMTP');
	$smtpChildren = [];
	foreach ($smtpFieldset->children() as $child) {
		$smtpChildren[$child->name] = true;
	}
	expect($smtpChildren)->toHaveKeys([
		'dsn',
		'Host',
		'Helo',
		'SMTPSecure',
		'Port',
		'SMTPAutoTLS',
		'SMTPAuth',
		'Username',
		'Password',
		'AuthType',
		'OAuth2',
		'SMTPKeepAlive',
		'do_verp',
	]);

	$oauthFieldset = $smtpFieldset->getChildByName('OAuth2');
	$oauthChildren = [];
	foreach ($oauthFieldset->children() as $child) {
		$oauthChildren[$child->name] = true;
	}
	expect($oauthChildren)->toHaveKeys([
		'OAuthProvider',
		'OAuthEmail',
		'OAuthClientId',
		'OAuthClientSecret',
		'OAuthTenantId',
		'OAuthRefreshToken',
	]);

	// Verify TabDKIM child fields
	$tabDkimChildren = [];
	foreach ($tabDkim->children() as $child) {
		$tabDkimChildren[$child->name] = true;
	}
	expect($tabDkimChildren)->toHaveKeys([
		'DKIM_domain',
		'DKIM_identity',
		'DKIM_selector',
		'DKIM_passphrase',
		'DKIM_private',
		'DKIM_private_string',
		'DKIM_copyHeaderFields',
	]);

	// Verify TabAdvanced child fields
	$tabAdvancedChildren = [];
	foreach ($tabAdvanced->children() as $child) {
		$tabAdvancedChildren[$child->name] = true;
	}
	expect($tabAdvancedChildren)->toHaveKeys([
		'Hostname',
		'XMailer',
		'UseSMTPUTF8',
		'Timeout',
		'SMTPDebug',
		'Debugoutput',
	]);

	// Verify TabTest child fields
	$tabTestChildren = [];
	foreach ($tabTest->children() as $child) {
		$tabTestChildren[$child->name] = true;
	}
	expect($tabTestChildren)->toHaveKeys([
		'_test_email',
	]);
});

test('WireMailPHPMailerConfig getInputfields builds wrapper with OAuth dynamic status markup', function (): void {
	$config = new WireMailPHPMailerConfig();
	$inputfields = $config->getInputfields();

	expect($inputfields)->toBeInstanceOf(InputfieldWrapper::class);

	// By default, OAuthClientId and OAuthClientSecret are empty, so instruction markup is shown
	$oauthFieldset = $inputfields->getChildByName('OAuth2');
	expect($oauthFieldset)->not->toBeNull();

	$markupField = $oauthFieldset->getChildByName('_oauth_markup');
	expect($markupField)->not->toBeNull()
		->and($markupField->markupText)->toContain('Please enter your Client ID')
		->and($markupField->markupText)->toContain('save the settings, and then a button will appear');
});

test('WireMailPHPMailerConfig renders uninstalled library notice when credentials are provided for missing provider', function (): void {
	$config = new WireMailPHPMailerConfig();
	$config->set('OAuthProvider', 'yahoo');
	$config->set('OAuthClientId', 'yahoo_client_id_test');
	$config->set('OAuthClientSecret', 'yahoo_secret_test');

	$inputfields = $config->getInputfields();
	$oauthFieldset = $inputfields->getChildByName('OAuth2');
	$markupField = $oauthFieldset->getChildByName('_oauth_markup');

	expect($markupField)->not->toBeNull()
		->and($markupField->markupText)->toContain('OAuth library for YAHOO is not installed!')
		->and($markupField->markupText)->toContain('composer require hayageek/oauth2-yahoo');
});

test('WireMailPHPMailerConfig renders authorization button and callback URL note when provider is installed', function (): void {
	$config = new WireMailPHPMailerConfig();
	$config->set('OAuthProvider', 'google');
	$config->set('OAuthClientId', 'client_123');
	$config->set('OAuthClientSecret', 'secret_abc');

	$inputfields = $config->getInputfields();
	$oauthFieldset = $inputfields->getChildByName('OAuth2');
	$markupField = $oauthFieldset->getChildByName('_oauth_markup');

	expect($markupField)->not->toBeNull()
		->and($markupField->markupText)->toContain('Authorize via Google')
		->and($markupField->markupText)->toContain('https://accounts.google.com/o/oauth2/v2/auth?scope=mail')
		->and($markupField->markupText)->toContain('as an authorized Redirect URI');
});

test('WireMailPHPMailerConfig test email hook executes on valid post request', function (): void {
	$wire = \ProcessWire\wire();
	$session = $wire->session;
	$modules = $wire->modules;
	$input = $wire->input;

	// Prepare mock FakePHPMailer
	$fakeMailer = new FakePHPMailer();
	$hookId = $wire->addHookBefore('WireMailPHPMailer::send', function (HookEvent $event) use ($fakeMailer): void {
		/** @var WireMailPHPMailer $mail */
		$mail = $event->object;
		$mail->setMailer($fakeMailer);
	});

	// Temporarily simulate POST request with CSRF token and _test_email in WireInputData
	$tokenName = $session->CSRF->getTokenName();
	$tokenValue = $session->CSRF->getTokenValue();

	$_SERVER['REQUEST_METHOD'] = 'POST';
	$input->post->set('_test_email', 'admin@example.org');
	$input->post->set($tokenName, $tokenValue);

	$config = new WireMailPHPMailerConfig();
	$config->getInputfields(); // This triggers registerTestEmailHook()

	// Calling saveConfig triggers the hook
	$modules->saveConfig('WireMailPHPMailer', ['Mailer' => 'mail']);

	// Clean up simulated POST data and hook
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$input->post->removeAll();
	$wire->removeHook($hookId);

	// Assert that FakePHPMailer received the test email
	expect($fakeMailer->sendCallCount)->toBeGreaterThanOrEqual(1)
		->and($fakeMailer->getToAddresses())->toHaveCount(1)
		->and($fakeMailer->getToAddresses()[0][0])->toBe('admin@example.org')
		->and($fakeMailer->Subject)->toBe('ProcessWire: WireMailPHPMailer Test')
		->and($fakeMailer->Body)->toContain('Test successful!')
		->and($fakeMailer->AltBody)->toContain('Test successful!');
});
