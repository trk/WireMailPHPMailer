<?php

declare(strict_types=1);

namespace ProcessWire\Tests\Support;

if (!class_exists('\\League\\OAuth2\\Client\\Provider\\Google', false)) {
	class GoogleOAuthTestStub
	{
		public array $params;

		public function __construct(array $params = [])
		{
			$this->params = $params;
		}

		public function getAuthorizationUrl(array $options = []): string
		{
			return 'https://accounts.google.com/o/oauth2/v2/auth?scope=mail';
		}

		public function getState(): string
		{
			return 'test-oauth-state-123';
		}
	}
	class_alias(GoogleOAuthTestStub::class, '\\League\\OAuth2\\Client\\Provider\\Google');
}

if (!class_exists('\\Greew\\OAuth2\\Client\\Provider\\Azure', false)) {
	class AzureOAuthTestStub
	{
		public array $params;

		public function __construct(array $params = [])
		{
			$this->params = $params;
		}

		public function getAuthorizationUrl(array $options = []): string
		{
			return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
		}

		public function getState(): string
		{
			return 'test-azure-state-456';
		}
	}
	class_alias(AzureOAuthTestStub::class, '\\Greew\\OAuth2\\Client\\Provider\\Azure');
}
