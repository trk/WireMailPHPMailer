<?php

declare(strict_types=1);

namespace ProcessWire\Tests\Unit;

use ProcessWire\OAuthProvider;

require_once dirname(__DIR__) . '/bootstrap.php';

test('OAuthProvider enum defines expected cases and backed values', function (): void {
	$cases = OAuthProvider::cases();

	expect($cases)->toHaveCount(4)
		->and(OAuthProvider::Google->value)->toBe('google')
		->and(OAuthProvider::Yahoo->value)->toBe('yahoo')
		->and(OAuthProvider::Microsoft->value)->toBe('microsoft')
		->and(OAuthProvider::Azure->value)->toBe('azure')
		->and(OAuthProvider::tryFrom('google'))->toBe(OAuthProvider::Google)
		->and(OAuthProvider::tryFrom('nonexistent'))->toBeNull();
});

test('OAuthProvider returns correct human-readable labels', function (): void {
	expect(OAuthProvider::Google->label())->toBe('Google')
		->and(OAuthProvider::Yahoo->label())->toBe('Yahoo')
		->and(OAuthProvider::Microsoft->label())->toBe('Microsoft')
		->and(OAuthProvider::Azure->label())->toBe('Microsoft Azure (Greew)');
});

test('OAuthProvider maps to correct composer package names and provider classes', function (): void {
	expect(OAuthProvider::Google->packageName())->toBe('league/oauth2-google')
		->and(OAuthProvider::Google->providerClass())->toBe('\\League\\OAuth2\\Client\\Provider\\Google')
		->and(OAuthProvider::Yahoo->packageName())->toBe('hayageek/oauth2-yahoo')
		->and(OAuthProvider::Yahoo->providerClass())->toBe('\\Hayageek\\OAuth2\\Client\\Provider\\Yahoo')
		->and(OAuthProvider::Microsoft->packageName())->toBe('stevenmaguire/oauth2-microsoft')
		->and(OAuthProvider::Microsoft->providerClass())->toBe('\\Stevenmaguire\\OAuth2\\Client\\Provider\\Microsoft')
		->and(OAuthProvider::Azure->packageName())->toBe('greew/oauth2-azure-provider')
		->and(OAuthProvider::Azure->providerClass())->toBe('\\Greew\\OAuth2\\Client\\Provider\\Azure');
});

test('OAuthProvider returns provider-specific authorization options', function (): void {
	expect(OAuthProvider::Google->authorizationOptions())->toBe([
		'prompt' => 'consent',
		'scope' => ['https://mail.google.com/'],
	])
		->and(OAuthProvider::Azure->authorizationOptions())->toBe([
			'scope' => [
				'https://outlook.office.com/SMTP.Send',
				'offline_access',
			],
		])
		->and(OAuthProvider::Yahoo->authorizationOptions())->toBe([])
		->and(OAuthProvider::Microsoft->authorizationOptions())->toBe([]);
});

test('OAuthProvider enforces credential validation rules correctly per provider', function (): void {
	// Standard providers require both clientId and clientSecret
	expect(OAuthProvider::Google->hasRequiredCredentials('client_id_123', 'client_secret_abc'))->toBeTrue()
		->and(OAuthProvider::Google->hasRequiredCredentials('client_id_123', ''))->toBeFalse()
		->and(OAuthProvider::Google->hasRequiredCredentials('', 'client_secret_abc'))->toBeFalse()
		->and(OAuthProvider::Google->hasRequiredCredentials('', ''))->toBeFalse()
		->and(OAuthProvider::Yahoo->hasRequiredCredentials('y_id', 'y_secret'))->toBeTrue()
		->and(OAuthProvider::Yahoo->hasRequiredCredentials('y_id', ''))->toBeFalse()
		->and(OAuthProvider::Microsoft->hasRequiredCredentials('m_id', 'm_secret'))->toBeTrue()
		->and(OAuthProvider::Microsoft->hasRequiredCredentials('m_id', ''))->toBeFalse();

	// Azure allows empty clientSecret if clientId is non-empty
	expect(OAuthProvider::Azure->hasRequiredCredentials('azure_id', ''))->toBeTrue()
		->and(OAuthProvider::Azure->hasRequiredCredentials('azure_id', 'azure_secret'))->toBeTrue()
		->and(OAuthProvider::Azure->hasRequiredCredentials('', 'azure_secret'))->toBeFalse()
		->and(OAuthProvider::Azure->hasRequiredCredentials('', ''))->toBeFalse();
});

test('OAuthProvider handles package installation checks and returns null when package is uninstalled', function (): void {
	// Provider class that doesn't exist
	expect(OAuthProvider::Yahoo->isInstalled())->toBeBool()
		->and(OAuthProvider::Yahoo->createProvider(['clientId' => '', 'clientSecret' => '']))->toBeNull()
		->and(OAuthProvider::Yahoo->createProvider(['clientId' => 'id', 'clientSecret' => 'secret']))->toBeNull();
});

test('OAuthProvider instantiates provider object with merged arguments when class exists', function (): void {
	// Verify Google instantiation
	expect(OAuthProvider::Google->isInstalled())->toBeTrue();
	$googleProvider = OAuthProvider::Google->createProvider([
		'clientId' => 'google_client_id',
		'clientSecret' => 'google_secret',
		'redirectUri' => 'https://example.com/oauth/callback',
		'extra' => ['custom_opt' => 'value123'],
	]);

	expect($googleProvider)->not->toBeNull()
		->and($googleProvider->params['clientId'])->toBe('google_client_id')
		->and($googleProvider->params['clientSecret'])->toBe('google_secret')
		->and($googleProvider->params['redirectUri'])->toBe('https://example.com/oauth/callback')
		->and($googleProvider->params['accessType'])->toBe('offline')
		->and($googleProvider->params['custom_opt'])->toBe('value123');

	// Verify Azure instantiation
	expect(OAuthProvider::Azure->isInstalled())->toBeTrue();
	$azureProvider = OAuthProvider::Azure->createProvider([
		'clientId' => 'azure_client_id',
		'clientSecret' => '',
		'redirectUri' => 'https://example.com/azure/callback',
		'tenantId' => 'custom-tenant-uuid',
	]);

	expect($azureProvider)->not->toBeNull()
		->and($azureProvider->params['clientId'])->toBe('azure_client_id')
		->and($azureProvider->params['tenantId'])->toBe('custom-tenant-uuid')
		->and($azureProvider->params['defaultEndPointVersion'])->toBe('2.0');
});
