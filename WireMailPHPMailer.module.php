<?php

declare(strict_types=1);

namespace ProcessWire;

use PHPMailer\PHPMailer\DSNConfigurator;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Supported OAuth2 Providers for XOAUTH2 SMTP authentication
 */
enum OAuthProvider: string
{
	case Google = 'google';
	case Yahoo = 'yahoo';
	case Microsoft = 'microsoft';
	case Azure = 'azure';

	/**
	 * Translated human-readable provider label
	 */
	public function label(): string
	{
		return match ($this) {
			self::Google => __('Google'),
			self::Yahoo => __('Yahoo'),
			self::Microsoft => __('Microsoft'),
			self::Azure => __('Microsoft Azure (Greew)'),
		};
	}

	/**
	 * Composer package name required for this provider
	 */
	public function packageName(): string
	{
		return match ($this) {
			self::Google => 'league/oauth2-google',
			self::Yahoo => 'hayageek/oauth2-yahoo',
			self::Microsoft => 'stevenmaguire/oauth2-microsoft',
			self::Azure => 'greew/oauth2-azure-provider',
		};
	}

	/**
	 * Fully-qualified provider class name
	 */
	public function providerClass(): string
	{
		return match ($this) {
			self::Google => '\\League\\OAuth2\\Client\\Provider\\Google',
			self::Yahoo => '\\Hayageek\\OAuth2\\Client\\Provider\\Yahoo',
			self::Microsoft => '\\Stevenmaguire\\OAuth2\\Client\\Provider\\Microsoft',
			self::Azure => '\\Greew\\OAuth2\\Client\\Provider\\Azure',
		};
	}

	/**
	 * Options passed to getAuthorizationUrl()
	 *
	 * @return array<string, mixed>
	 */
	public function authorizationOptions(): array
	{
		return match ($this) {
			self::Google => [
				'prompt' => 'consent',
				'scope' => ['https://mail.google.com/'],
			],
			self::Azure => [
				'scope' => [
					'https://outlook.office.com/SMTP.Send',
					'offline_access',
				],
			],
			self::Yahoo, self::Microsoft => [],
		};
	}

	/**
	 * Whether the underlying Composer package for this provider is installed
	 */
	public function isInstalled(): bool
	{
		return class_exists($this->providerClass());
	}

	/**
	 * Whether the required client credentials are provided
	 */
	public function hasRequiredCredentials(string $clientId, string $clientSecret): bool
	{
		return $clientId !== '' && ($clientSecret !== '' || $this === self::Azure);
	}

	/**
	 * Instantiate the underlying OAuth2 provider if its package is installed
	 *
	 * @param array{clientId?: string, clientSecret?: string, redirectUri?: string, tenantId?: string, extra?: array<string, mixed>} $config
	 */
	public function createProvider(array $config): ?object
	{
		$clientId = $config['clientId'] ?? '';
		$clientSecret = $config['clientSecret'] ?? '';
		$redirectUri = $config['redirectUri'] ?? '';
		$tenantId = $config['tenantId'] ?? 'common';
		$extra = $config['extra'] ?? [];

		if (!$this->hasRequiredCredentials($clientId, $clientSecret) || !$this->isInstalled()) {
			return null;
		}

		$class = $this->providerClass();

		$baseArgs = [
			'clientId' => $clientId,
			'clientSecret' => $clientSecret,
			'redirectUri' => $redirectUri,
		];

		$providerArgs = match ($this) {
			self::Google => ['accessType' => 'offline'],
			self::Azure => [
				'tenantId' => $tenantId !== '' ? $tenantId : 'common',
				'defaultEndPointVersion' => '2.0',
			],
			self::Yahoo, self::Microsoft => [],
		};

		$args = array_replace($baseArgs, $providerArgs, $extra);

		return new $class($args);
	}
}

/**
 * Class WireMailPHPMailer
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.totoglu.com
 * @projectWebsite	: https://github.com/trk/WireMailPHPMailer
 */
class WireMailPHPMailer extends WireMail implements Module, ConfigurableModule
{
	/**
	 * Allowed PHPMailer string properties mapped from module configuration
	 *
	 * @var list<string>
	 */
	private const PHPMAILER_STRING_PROPS = [
		'CharSet',
		'ContentType',
		'Encoding',
		'XMailer',
		'Hostname',
		'Sender',
		'FromName',
		'From',
		'Subject',
		'Sendmail',
		'Host',
		'Helo',
		'SMTPSecure',
		'Username',
		'Password',
		'AuthType',
		'DKIM_domain',
		'DKIM_identity',
		'DKIM_selector',
		'DKIM_passphrase',
		'DKIM_private',
		'DKIM_private_string',
		'Debugoutput',
	];

	/**
	 * PHPMailer string properties that may be explicitly set to an empty string
	 *
	 * @var list<string>
	 */
	private const PHPMAILER_EMPTY_ALLOWED_PROPS = [
		'CharSet',
		'ContentType',
		'Encoding',
		'XMailer',
		'Hostname',
	];

	/**
	 * PHPMailer integer properties mapped from module configuration
	 *
	 * @var list<string>
	 */
	private const PHPMAILER_INT_PROPS = [
		'WordWrap',
		'Port',
		'Timeout',
		'SMTPDebug',
	];

	/**
	 * PHPMailer boolean properties mapped from module configuration
	 *
	 * @var list<string>
	 */
	private const PHPMAILER_BOOL_PROPS = [
		'SMTPAutoTLS',
		'SMTPAuth',
		'SMTPKeepAlive',
		'do_verp',
		'UseSendmailOptions',
		'AllowEmpty',
		'UseSMTPUTF8',
		'DKIM_copyHeaderFields',
	];

	/**
	 * CC recipients indexed by email => name
	 *
	 * @var array<string, string>
	 */
	protected array $ccRecipients = [];

	/**
	 * BCC recipients indexed by email => name
	 *
	 * @var array<string, string>
	 */
	protected array $bccRecipients = [];

	/**
	 * Optional injected PHPMailer instance for testing or custom transport seams
	 */
	protected ?PHPMailer $customMailer = null;

	/**
	 * Module info
	 *
	 * @see Module
	 * @return array<string, mixed>
	 */
	public static function getModuleInfo(): array
	{
		return [
			'title' => 'WireMailPHPMailer',
			'version' => 150,
			'summary' => __('This module extends WireMail base class, integrating the PHPMailer mailing library into ProcessWire.'),
			'href' => 'https://github.com/trk/WireMailPHPMailer',
			'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
			'requires' => [
				'ProcessWire>=3.0.210',
				'PHP>=8.4.0',
			],
			'installs' => [],
			'icon' => 'envelope-o',
			'singular' => false,
			'autoload' => false,
		];
	}

	/**
	 * Initialize the module
	 */
	public function init(): void
	{
		$this->resetMessageState();
	}

	/**
	 * Reset per-message state
	 */
	protected function resetMessageState(bool $resetMailBody = false): void
	{
		$this->ccRecipients = [];
		$this->bccRecipients = [];

		if ($resetMailBody) {
			$this->mail['to'] = [];
			$this->mail['toName'] = [];
			$this->mail['from'] = '';
			$this->mail['fromName'] = '';
			$this->mail['replyTo'] = '';
			$this->mail['replyToName'] = '';
			$this->mail['subject'] = '';
			$this->mail['body'] = '';
			$this->mail['bodyHTML'] = '';
			$this->mail['header'] = [
				'X-Mailer' => 'ProcessWire/' . $this->className(),
			];
			$this->mail['param'] = [];
			$this->mail['attachments'] = [];
		}
	}

	/**
	 * Get mail or module property
	 *
	 * @param string $key
	 */
	public function get($key): mixed
	{
		if ($key === 'cc') {
			return $this->ccRecipients;
		}
		if ($key === 'bcc') {
			return $this->bccRecipients;
		}

		return parent::get($key);
	}

	/**
	 * Set mail or module property
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value): static
	{
		if ($key === 'cc') {
			$this->cc(is_string($value) || is_array($value) ? $value : null);
			return $this;
		}
		if ($key === 'bcc') {
			$this->bcc(is_string($value) || is_array($value) ? $value : null);
			return $this;
		}
		if ($key === 'attachments') {
			if ($value === null) {
				$this->attachment(null);
			} elseif (is_string($value)) {
				$this->attachment($value);
			} elseif (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_string($v) && $v !== '') {
						$this->attachment($v, is_string($k) ? $k : '');
					}
				}
			}
			return $this;
		}

		parent::set($key, $value);
		return $this;
	}

	/**
	 * Get array of all data for this email including CC and BCC
	 *
	 * @return array<string, mixed>
	 */
	public function getArray(): array
	{
		$data = parent::getArray();
		$data['cc'] = $this->ccRecipients;
		$data['bcc'] = $this->bccRecipients;
		return $data;
	}

	/**
	 * Inject a custom PHPMailer adapter instance
	 */
	public function setMailer(?PHPMailer $mailer): static
	{
		$this->customMailer = $mailer;
		return $this;
	}

	/**
	 * Return configured PHPMailer instance
	 *
	 * @param bool $initialize Whether to apply module configuration settings
	 * @param bool $exceptions Whether PHPMailer should throw exceptions
	 */
	public function getInstance(bool $initialize = true, bool $exceptions = false): PHPMailer
	{
		if ($this->customMailer !== null) {
			$instance = $this->customMailer;
			$instance->clearAllRecipients();
			$instance->clearReplyTos();
			$instance->clearAttachments();
			$instance->clearCustomHeaders();
			$instance->From = 'root@localhost';
			$instance->FromName = 'Root User';
			$instance->Sender = '';
			$instance->ContentType = PHPMailer::CONTENT_TYPE_PLAINTEXT;
			$instance->Body = '';
			$instance->AltBody = '';
			$instance->Subject = '';
			$instance->ErrorInfo = '';
		} else {
			$instance = new PHPMailer($exceptions);
		}

		if ($initialize) {
			$instance = $this->setModuleSettings($instance);
		}

		return $instance;
	}

	/**
	 * Cast a config value according to its expected PHPMailer type
	 */
	private function castConfigValue(string $key, mixed $value): mixed
	{
		if (in_array($key, self::PHPMAILER_INT_PROPS, true)) {
			return is_numeric($value) ? (int) $value : 0;
		}

		if (in_array($key, self::PHPMAILER_BOOL_PROPS, true)) {
			if (is_bool($value)) {
				return $value;
			}
			return $value === '1' || $value === 1 || $value === 'true';
		}

		if ($key === 'Priority') {
			if ($value === 'null' || $value === null || $value === '') {
				return null;
			}
			return is_numeric($value) ? (int) $value : null;
		}

		return (string) $value;
	}

	/**
	 * Apply module settings to a PHPMailer instance
	 */
	protected function setModuleSettings(PHPMailer $instance): PHPMailer
	{
		$data = $this->data;

		$mailer = isset($data['Mailer']) && is_string($data['Mailer']) ? $data['Mailer'] : 'mail';
		$smtpDsn = ($mailer === 'smtp' && isset($data['dsn']) && is_string($data['dsn'])) ? trim($data['dsn']) : '';
		$dsnManagedKeys = $smtpDsn !== '' ? ['Host', 'Port', 'SMTPSecure', 'SMTPAuth', 'Username', 'Password'] : [];

		if ($mailer === 'smtp') {
			$instance->isSMTP();
		} elseif ($mailer === 'sendmail') {
			$instance->isSendmail();
		} else {
			$instance->isMail();
		}

		foreach ($data as $key => $value) {
			$key = (string) $key;

			if (in_array($key, $dsnManagedKeys, true)) {
				continue;
			}

			if ($key === 'Priority') {
				$instance->Priority = $this->castConfigValue($key, $value);
				continue;
			}

			if (in_array($key, self::PHPMAILER_INT_PROPS, true) || in_array($key, self::PHPMAILER_BOOL_PROPS, true)) {
				$instance->set($key, $this->castConfigValue($key, $value));
				continue;
			}

			if (in_array($key, self::PHPMAILER_STRING_PROPS, true)) {
				$strVal = is_scalar($value) ? (string) $value : '';
				if ($strVal === '' && !in_array($key, self::PHPMAILER_EMPTY_ALLOWED_PROPS, true)) {
					continue;
				}
				if ($key === 'ContentType' && $strVal !== PHPMailer::CONTENT_TYPE_PLAINTEXT && $strVal !== PHPMailer::CONTENT_TYPE_TEXT_HTML) {
					$strVal = PHPMailer::CONTENT_TYPE_TEXT_HTML;
				}
				$instance->set($key, $strVal);
			}
		}

		if ($mailer === 'smtp') {
			if ($smtpDsn !== '') {
				$configurator = new DSNConfigurator();
				$instance = $configurator->configure($instance, $smtpDsn);
			}

			if (isset($data['AuthType']) && $data['AuthType'] === 'XOAUTH2') {
				$providerName = (string) ($data['OAuthProvider'] ?? '');
				$clientId = (string) ($data['OAuthClientId'] ?? '');
				$clientSecret = (string) ($data['OAuthClientSecret'] ?? '');
				$tenantId = (string) ($data['OAuthTenantId'] ?? '');
				$refreshToken = (string) ($data['OAuthRefreshToken'] ?? '');
				$email = (string) ($data['OAuthEmail'] ?? '');

				$providerObj = OAuthProvider::tryFrom($providerName)?->createProvider([
					'clientId' => $clientId,
					'clientSecret' => $clientSecret,
					'tenantId' => $tenantId,
				]);

				if ($providerObj !== null && class_exists(OAuth::class)) {
					$instance->setOAuth(
						new OAuth([
							'provider' => $providerObj,
							'clientId' => $clientId,
							'clientSecret' => $clientSecret,
							'refreshToken' => $refreshToken,
							'userName' => $email,
						])
					);
				} elseif ($providerObj === null && $providerName !== '') {
					$this->wire()->log->save(
						'WireMailPHPMailer',
						sprintf(
							$this->_("OAuth provider library for '%s' not found. Please install it via Composer. XOAUTH2 may fail."),
							$providerName
						)
					);
				}
			}
		}

		return $instance;
	}

	/**
	 * Apply message-level user settings to a PHPMailer instance
	 */
	protected function setUserSettings(PHPMailer $instance): PHPMailer
	{
		$this->applyRecipients($instance);
		$this->applySender($instance);
		$this->applyHeaders($instance);
		$this->applyBodyAndAttachments($instance);

		return $instance;
	}

	/**
	 * Bind To, CC, and BCC recipients to the PHPMailer instance
	 */
	private function applyRecipients(PHPMailer $instance): void
	{
		$toAddresses = is_array($this->mail['to'] ?? null) ? $this->mail['to'] : [];
		$toNames = is_array($this->mail['toName'] ?? null) ? $this->mail['toName'] : [];
		foreach ($toAddresses as $key => $email) {
			$address = (string) $email;
			if ($address === '') {
				continue;
			}
			$name = (string) ($toNames[$address] ?? $toNames[$key] ?? '');
			$instance->addAddress($address, $name);
		}

		foreach ($this->ccRecipients as $address => $name) {
			if ($address !== '') {
				$instance->addCC($address, $name);
			}
		}

		foreach ($this->bccRecipients as $address => $name) {
			if ($address !== '') {
				$instance->addBCC($address, $name);
			}
		}
	}

	/**
	 * Bind From, Sender (Return-Path), and Reply-To addresses to the PHPMailer instance
	 */
	private function applySender(PHPMailer $instance): void
	{
		$from = (string) ($this->mail['from'] ?? '');
		$fromName = (string) ($this->mail['fromName'] ?? '');
		if ($from === '' && $instance->From !== '' && $instance->From !== 'root@localhost') {
			$from = $instance->From;
		}
		if ($from === '') {
			$config = $this->wire()->config;
			$wireMailSettings = is_array($config->wireMail) ? $config->wireMail : [];
			if (!empty($wireMailSettings['from']) && is_string($wireMailSettings['from'])) {
				[$extractedFrom, $extractedFromName] = $this->extractEmailAndName($wireMailSettings['from']);
				$from = $extractedFrom;
				if ($fromName === '' && $extractedFromName !== '') {
					$fromName = $extractedFromName;
				}
			} elseif (!empty($config->adminEmail) && is_string($config->adminEmail)) {
				$from = $this->sanitizeEmail($config->adminEmail);
			} elseif (!empty($config->httpHost) && is_string($config->httpHost)) {
				$host = (string) preg_replace('/:\d+$/', '', trim($config->httpHost));
				if (!str_contains($host, '.')) {
					$host .= '.localdomain';
				}
				$candidate = 'processwire@' . $host;
				if (PHPMailer::validateAddress($candidate)) {
					$from = $candidate;
				}
			}
			if ($fromName === '' && !empty($wireMailSettings['fromName']) && is_string($wireMailSettings['fromName'])) {
				$fromName = $this->sanitizeHeaderValue($wireMailSettings['fromName']);
			}
		}

		if ($from !== '') {
			$effectiveFromName = $fromName !== '' ? $fromName : $instance->FromName;
			$instance->setFrom($from, $effectiveFromName, $instance->Sender === '');
		} elseif ($fromName !== '') {
			$instance->FromName = $fromName;
		}

		$params = is_array($this->mail['param'] ?? null) ? $this->mail['param'] : [];
		foreach ($params as $param) {
			if (is_string($param) && preg_match('/-f\s*([^\s]+)/', $param, $matches) === 1) {
				$envelopeSender = $this->sanitizeEmail($matches[1]);
				if ($envelopeSender !== '') {
					$instance->Sender = $envelopeSender;
				}
			}
		}

		$replyTo = (string) ($this->mail['replyTo'] ?? '');
		$replyToName = (string) ($this->mail['replyToName'] ?? '');
		if ($replyTo !== '') {
			$instance->addReplyTo($replyTo, $replyToName);
		}
	}

	/**
	 * Bind message headers (Reply-To, Cc, Bcc, X-Mailer, and custom headers) to the PHPMailer instance
	 */
	private function applyHeaders(PHPMailer $instance): void
	{
		$replyTo = (string) ($this->mail['replyTo'] ?? '');
		$headers = is_array($this->mail['header'] ?? null) ? $this->mail['header'] : [];
		$defaultXMailer = 'ProcessWire/' . $this->className();

		foreach ($headers as $headerName => $headerValue) {
			$headerName = (string) $headerName;
			$headerValue = (string) $headerValue;
			if ($headerName === '' || $headerValue === '') {
				continue;
			}
			if (strcasecmp($headerName, 'Reply-To') === 0) {
				if ($replyTo === '') {
					[$extractedEmail, $extractedName] = $this->extractEmailAndName($headerValue);
					if ($extractedEmail !== '') {
						$instance->addReplyTo($extractedEmail, $extractedName);
					}
				}
				continue;
			}
			if (strcasecmp($headerName, 'Cc') === 0 || strcasecmp($headerName, 'Bcc') === 0) {
				$isCc = strcasecmp($headerName, 'Cc') === 0;
				$existing = $isCc ? $this->ccRecipients : $this->bccRecipients;
				$parsed = [];
				$this->populateRecipientList($parsed, $headerValue);
				foreach ($parsed as $addr => $addrName) {
					if (!isset($existing[$addr])) {
						if ($isCc) {
							$instance->addCC($addr, $addrName);
						} else {
							$instance->addBCC($addr, $addrName);
						}
					}
				}
				continue;
			}
			if (strcasecmp($headerName, 'X-Mailer') === 0) {
				if ($headerValue !== $defaultXMailer) {
					$instance->XMailer = $headerValue;
				}
				continue;
			}
			$instance->addCustomHeader($headerName, $headerValue);
		}
	}

	/**
	 * Bind Subject, plain/HTML bodies, and file attachments to the PHPMailer instance
	 */
	private function applyBodyAndAttachments(PHPMailer $instance): void
	{
		$subject = (string) ($this->mail['subject'] ?? '');
		if ($subject !== '') {
			$instance->Subject = $subject;
		}

		$bodyText = (string) ($this->mail['body'] ?? '');
		$bodyHtml = (string) ($this->mail['bodyHTML'] ?? '');

		if ($bodyHtml !== '') {
			$instance->isHTML(true);
			$instance->Body = $bodyHtml;
		}
		if ($bodyText !== '') {
			$instance->AltBody = $bodyText;
		}

		if ($instance->Body === '' && $instance->AltBody !== '') {
			$instance->isHTML(false);
			$instance->Body = $instance->AltBody;
			$instance->AltBody = '';
		} elseif ($instance->Body !== '' && $instance->AltBody === '') {
			$instance->AltBody = $instance->html2text($instance->Body);
		}

		$wireAttachments = is_array($this->mail['attachments'] ?? null) ? $this->mail['attachments'] : [];
		foreach ($wireAttachments as $filename => $file) {
			$filePath = (string) $file;
			if ($filePath !== '') {
				$attachmentName = is_string($filename) ? $filename : '';
				$instance->addAttachment($filePath, $attachmentName);
			}
		}
	}

	/**
	 * Set the email to address (overrides WireMail::to to also reset CC/BCC when null)
	 *
	 * @param string|array<int|string, string>|null $email
	 * @param string|null $name
	 */
	public function to($email = null, $name = null): static
	{
		if ($email === null) {
			$this->ccRecipients = [];
			$this->bccRecipients = [];
		}

		parent::to($email, $name);
		return $this;
	}

	/**
	 * Set or clear CC recipient(s)
	 *
	 * @param string|array<int|string, string>|null $email
	 * @param string|null $name
	 */
	public function cc(string|array|null $email = null, ?string $name = null): static
	{
		if ($email === null) {
			$this->ccRecipients = [];
			return $this;
		}

		$this->populateRecipientList($this->ccRecipients, $email, $name);
		return $this;
	}

	/**
	 * Set or clear BCC recipient(s)
	 *
	 * @param string|array<int|string, string>|null $email
	 * @param string|null $name
	 */
	public function bcc(string|array|null $email = null, ?string $name = null): static
	{
		if ($email === null) {
			$this->bccRecipients = [];
			return $this;
		}

		$this->populateRecipientList($this->bccRecipients, $email, $name);
		return $this;
	}

	/**
	 * Parse and sanitize recipient list into target array
	 *
	 * @param array<string, string> $target
	 * @param string|array<int|string, string> $email
	 * @param string|null $name
	 */
	protected function populateRecipientList(array &$target, string|array $email, ?string $name = null): void
	{
		$emails = is_array($email) ? $email : explode(',', $email);

		foreach ($emails as $key => $value) {
			$recipientName = '';
			if (is_string($key)) {
				$recipientEmail = $key;
				$recipientName = (string) $value;
			} elseif (str_contains((string) $value, '<') && str_contains((string) $value, '>')) {
				[$recipientEmail, $recipientName] = $this->extractEmailAndName((string) $value);
			} else {
				$recipientEmail = (string) $value;
			}

			if ($recipientName === '' && $name !== null) {
				$recipientName = $name;
			}

			$recipientEmail = $this->sanitizeEmail($recipientEmail);
			if ($recipientEmail !== '') {
				$target[$recipientEmail] = $this->sanitizeHeaderValue($recipientName);
			}
		}
	}

	/**
	 * Send the email
	 *
	 * @return int Returns the number of successfully sent messages
	 */
	public function ___send(): int
	{
		$log = $this->wire()->log;
		$instance = $this->getInstance();

		try {
			$instance = $this->setUserSettings($instance);
			$result = $instance->send();

			if ($result) {
				$log->save('WireMailPHPMailer', $this->_('Message has been sent.'));
				$count = count($instance->getToAddresses());
				return $count > 0 ? $count : 1;
			}

			$log->save('WireMailPHPMailer', $this->_('Message could not be sent. Mailer Error:') . ' ' . $instance->ErrorInfo);
			return 0;
		} catch (\Throwable $e) {
			$error = $e->getMessage();
			if ($instance->ErrorInfo !== '') {
				$error = $instance->ErrorInfo;
			}
			$log->save('WireMailPHPMailer', $this->_('Message could not be sent. Mailer Error:') . ' ' . $error);
			return 0;
		} finally {
			$this->resetMessageState(true);
		}
	}
}
