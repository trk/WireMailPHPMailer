<?php

declare(strict_types=1);

namespace ProcessWire;

require_once __DIR__ . '/WireMailPHPMailer.module.php';

/**
 * Class WireMailPHPMailerConfig
 *
 * Configs for WireMailPHPMailer
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.totoglu.com
 * @projectWebsite	: https://github.com/trk/WireMailPHPMailer
 */
class WireMailPHPMailerConfig extends ModuleConfig
{
	/**
	 * Define default module configuration fields
	 */
	public function __construct()
	{
		parent::__construct();

		$providerOptions = [];
		foreach (OAuthProvider::cases() as $case) {
			$providerOptions[$case->value] = $case->label();
		}

		$this->add([
			'TabGeneral' => [
				'type' => 'InputfieldFieldset',
				'label' => __('General'),
				'collapsed' => Inputfield::collapsedYes,
				'children' => [
					'Priority' => [
						'type' => 'InputfieldSelect',
						'label' => __('Priority'),
						'description' => __('When null, the header is not set at all.'),
						'required' => true,
						'value' => 'null',
						'options' => [
							'null' => __('Default'),
							1 => __('High'),
							3 => __('Normal'),
							5 => __('Low'),
						],
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 25,
					],
					'CharSet' => [
						'type' => 'InputfieldText',
						'label' => __('Character set'),
						'description' => __('The character set of the message.'),
						'value' => 'utf-8',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 25,
					],
					'ContentType' => [
						'type' => 'InputfieldSelect',
						'label' => __('Content-type'),
						'description' => __('The default MIME Content-type of the message.'),
						'required' => true,
						'value' => 'text/html',
						'options' => [
							'text/plain' => 'text/plain',
							'text/html' => 'text/html',
						],
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 25,
					],
					'Encoding' => [
						'type' => 'InputfieldSelect',
						'label' => __('Encoding'),
						'description' => __('The message encoding.'),
						'required' => true,
						'value' => '8bit',
						'options' => [
							'8bit' => '8bit',
							'7bit' => '7bit',
							'binary' => 'binary',
							'base64' => 'base64',
							'quoted-printable' => 'quoted-printable',
						],
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 25,
					],
					'WordWrap' => [
						'type' => 'InputfieldInteger',
						'label' => __('WordWrap'),
						'description' => __('Word-wrap the message body to this number of chars.'),
						'notes' => __('Set to 0 to not wrap. A useful value here is 78, for [RFC2822](https://www.ietf.org/rfc/rfc2822.txt) section 2.1.1 compliance.'),
						'value' => '0',
						'inputType' => 'number',
						'min' => 0,
						'collapsed' => Inputfield::collapsedNever,
					],
					'AllowEmpty' => [
						'type' => 'InputfieldCheckbox',
						'label' => __('Allow Empty'),
						'description' => __('Whether to allow sending messages with an empty body.'),
						'value' => false,
						'collapsed' => Inputfield::collapsedNever,
					],
				],
			],
			'TabRouting' => [
				'type' => 'InputfieldFieldset',
				'label' => __('Routing'),
				'collapsed' => Inputfield::collapsedYes,
				'children' => [
					'Sender' => [
						'type' => 'InputfieldEmail',
						'label' => __('Sender'),
						'description' => __('The envelope sender of the message. This will usually be turned into a Return-Path header by the receiver, and is the address that bounces will be sent to.'),
						'notes' => __('If not empty, will be passed via `-f` to sendmail or as the `MAIL FROM` value over SMTP.'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
					],
					'FromName' => [
						'type' => 'InputfieldText',
						'label' => __('From Name'),
						'description' => __('The From name of the message.'),
						'value' => '',
						'placeholder' => __('Site administrator'),
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'From' => [
						'type' => 'InputfieldEmail',
						'label' => __('From'),
						'description' => __('The From email address for the message.'),
						'value' => '',
						'placeholder' => 'email@domain.ltd',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'Subject' => [
						'type' => 'InputfieldText',
						'label' => __('Subject'),
						'description' => __('The Subject of the message.'),
						'value' => '',
						'placeholder' => __('An email subject'),
						'collapsed' => Inputfield::collapsedNever,
					],
				],
			],
			'TabTransport' => [
				'type' => 'InputfieldFieldset',
				'label' => __('Transport'),
				'collapsed' => Inputfield::collapsedYes,
				'children' => [
					'Mailer' => [
						'type' => 'InputfieldSelect',
						'label' => __('Mailer'),
						'description' => __('Which method to use to send mail.'),
						'required' => true,
						'value' => 'mail',
						'options' => [
							'mail' => 'mail',
							'sendmail' => 'sendmail',
							'smtp' => 'smtp',
						],
						'collapsed' => Inputfield::collapsedNever,
					],
					'SendmailSettings' => [
						'type' => 'InputfieldFieldset',
						'label' => __('Sendmail Settings'),
						'showIf' => 'Mailer=sendmail',
						'collapsed' => Inputfield::collapsedYes,
						'children' => [
							'Sendmail' => [
								'type' => 'InputfieldText',
								'label' => __('Sendmail Path'),
								'description' => __('The path to the sendmail program.'),
								'value' => '/usr/sbin/sendmail',
								'collapsed' => Inputfield::collapsedNever,
							],
							'UseSendmailOptions' => [
								'type' => 'InputfieldCheckbox',
								'label' => __('Use Sendmail Options'),
								'description' => __('Whether `mail()` uses a fully `sendmail-compatible` MTA.'),
								'notes' => __("One which supports sendmail's `-oi -f` options."),
								'value' => true,
								'collapsed' => Inputfield::collapsedNever,
							],
						],
					],
					'SMTP' => [
						'type' => 'InputfieldFieldset',
						'label' => __('SMTP Settings'),
						'showIf' => 'Mailer=smtp',
						'collapsed' => Inputfield::collapsedYes,
						'children' => [
							'dsn' => [
								'type' => 'InputfieldText',
								'label' => __('DSN'),
								'description' => __('A Data Source Name (DSN) string for SMTP setup.'),
								'notes' => __('Overrides Host, Port, SMTPSecure, Username, Password. e.g. smtps://user:password@smtp.example.com:465'),
								'value' => '',
								'collapsed' => Inputfield::collapsedBlank,
							],
							'Host' => [
								'type' => 'InputfieldText',
								'label' => __('Host'),
								'description' => __('SMTP hosts.'),
								'notes' => __('Either a single hostname or multiple semicolon-delimited hostnames. e.g. "smtp1.example.com;smtp2.example.com".'),
								'value' => 'localhost',
								'columnWidth' => 50,
								'showIf' => "dsn=''",
								'collapsed' => Inputfield::collapsedNever,
							],
							'Helo' => [
								'type' => 'InputfieldText',
								'label' => __('Helo'),
								'description' => __('The SMTP HELO of the message.'),
								'notes' => __('Default is `$Hostname`.'),
								'value' => '',
								'columnWidth' => 50,
								'showIf' => "dsn=''",
								'collapsed' => Inputfield::collapsedNever,
							],
							'SMTPSecure' => [
								'type' => 'InputfieldSelect',
								'label' => __('SMTP Secure'),
								'description' => __('What kind of encryption to use on the SMTP connection.'),
								'value' => 'tls',
								'options' => [
									'' => __('Default'),
									'ssl' => __('SSL'),
									'tls' => __('TLS'),
								],
								'columnWidth' => 33,
								'showIf' => "dsn=''",
								'collapsed' => Inputfield::collapsedNever,
							],
							'Port' => [
								'type' => 'InputfieldInteger',
								'label' => __('Port'),
								'description' => __('The default SMTP server port.'),
								'inputType' => 'number',
								'min' => 0,
								'value' => 587,
								'columnWidth' => 34,
								'showIf' => "dsn=''",
								'collapsed' => Inputfield::collapsedNever,
							],
							'SMTPAutoTLS' => [
								'type' => 'InputfieldCheckbox',
								'label' => __('SMTP Auto TLS'),
								'description' => __('Whether to enable SMTP Auto TLS.'),
								'value' => true,
								'columnWidth' => 33,
								'collapsed' => Inputfield::collapsedNever,
							],
							'SMTPAuth' => [
								'type' => 'InputfieldCheckbox',
								'label' => __('SMTP Auth'),
								'description' => __('Whether to use SMTP authentication.'),
								'notes' => __('Uses the Username and Password properties.'),
								'value' => false,
								'showIf' => "dsn=''",
								'collapsed' => Inputfield::collapsedNever,
							],
							'Username' => [
								'type' => 'InputfieldText',
								'label' => __('Username'),
								'description' => __('SMTP username.'),
								'value' => '',
								'columnWidth' => 50,
								'showIf' => "SMTPAuth=1, dsn=''",
								'collapsed' => Inputfield::collapsedBlank,
							],
							'Password' => [
								'type' => 'InputfieldText',
								'label' => __('Password'),
								'description' => __('SMTP password.'),
								'attr' => [
									'type' => 'password',
								],
								'showIf' => "SMTPAuth=1, AuthType!=XOAUTH2, dsn=''",
								'value' => '',
								'columnWidth' => 50,
								'collapsed' => Inputfield::collapsedBlank,
							],
							'AuthType' => [
								'type' => 'InputfieldSelect',
								'label' => __('Auth Type'),
								'description' => __('SMTP auth type.'),
								'notes' => __('Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.'),
								'value' => '',
								'showIf' => "SMTPAuth=1, dsn=''",
								'options' => [
									'' => __('Default'),
									'CRAM-MD5' => 'CRAM-MD5',
									'LOGIN' => 'LOGIN',
									'PLAIN' => 'PLAIN',
									'XOAUTH2' => 'XOAUTH2',
								],
								'columnWidth' => 100,
								'collapsed' => Inputfield::collapsedNever,
							],
							'OAuth2' => [
								'type' => 'InputfieldFieldset',
								'label' => __('OAuth2 Settings'),
								'showIf' => "SMTPAuth=1, AuthType=XOAUTH2, dsn=''",
								'collapsed' => Inputfield::collapsedYes,
								'children' => [
									'OAuthProvider' => [
										'type' => 'InputfieldSelect',
										'label' => __('OAuth Provider'),
										'description' => __('Select the OAuth2 provider. Make sure the corresponding provider library is installed via Composer.'),
										'required' => true,
										'value' => OAuthProvider::Google->value,
										'options' => $providerOptions,
										'collapsed' => Inputfield::collapsedNever,
										'columnWidth' => 50,
									],
									'OAuthEmail' => [
										'type' => 'InputfieldEmail',
										'label' => __('OAuth Email'),
										'description' => __('The email address of the account you are authorizing.'),
										'value' => '',
										'collapsed' => Inputfield::collapsedNever,
										'columnWidth' => 50,
									],
									'OAuthClientId' => [
										'type' => 'InputfieldText',
										'label' => __('Client ID'),
										'description' => __('The Client ID from your OAuth provider.'),
										'value' => '',
										'collapsed' => Inputfield::collapsedNever,
										'columnWidth' => 50,
									],
									'OAuthClientSecret' => [
										'type' => 'InputfieldText',
										'label' => __('Client Secret'),
										'description' => __('The Client Secret from your OAuth provider. Not required for Azure if using delegated permissions for public clients, but typically needed.'),
										'attr' => [
											'type' => 'password',
										],
										'value' => '',
										'collapsed' => Inputfield::collapsedNever,
										'columnWidth' => 50,
									],
									'OAuthTenantId' => [
										'type' => 'InputfieldText',
										'label' => __('Tenant ID (Azure Only)'),
										'description' => __('The Tenant ID from Microsoft Azure.'),
										'showIf' => 'OAuthProvider=azure',
										'value' => '',
										'collapsed' => Inputfield::collapsedNever,
										'columnWidth' => 100,
									],
									'OAuthRefreshToken' => [
										'type' => 'InputfieldText',
										'label' => __('Refresh Token'),
										'description' => __('The Refresh Token. Once obtained, it is saved here automatically.'),
										'attr' => [
											'type' => 'password',
										],
										'value' => '',
										'collapsed' => Inputfield::collapsedNever,
									],
								],
							],
							'SMTPKeepAlive' => [
								'type' => 'InputfieldCheckbox',
								'label' => __('SMTP Keep Alive'),
								'description' => __('Whether to keep SMTP connection open after each message.'),
								'notes' => __('If this is set to true then to close the connection requires an explicit call to `smtpClose()`.'),
								'value' => false,
								'collapsed' => Inputfield::collapsedNever,
							],
							'do_verp' => [
								'type' => 'InputfieldCheckbox',
								'label' => __('Generate VERP addresses'),
								'description' => __('Whether to generate VERP addresses on send.'),
								'notes' => __('Only applicable when sending via SMTP. [see](https://en.wikipedia.org/wiki/Variable_envelope_return_path), [see](http://www.postfix.org/VERP_README.html)'),
								'value' => false,
								'collapsed' => Inputfield::collapsedNever,
							],
						],
					],
				],
			],
			'TabDKIM' => [
				'type' => 'InputfieldFieldset',
				'label' => __('DKIM'),
				'collapsed' => Inputfield::collapsedYes,
				'children' => [
					'DKIM_domain' => [
						'type' => 'InputfieldText',
						'label' => __('DKIM domain'),
						'description' => __('DKIM signing domain name.'),
						'notes' => __('example: `example.com`'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'DKIM_identity' => [
						'type' => 'InputfieldText',
						'label' => __('DKIM identity'),
						'description' => __('Usually the email address used as the source of the email.'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'DKIM_selector' => [
						'type' => 'InputfieldText',
						'label' => __('DKIM selector'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'DKIM_passphrase' => [
						'type' => 'InputfieldText',
						'label' => __('DKIM passphrase'),
						'description' => __('Used if your key is encrypted.'),
						'attr' => [
							'type' => 'password',
						],
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'DKIM_private' => [
						'type' => 'InputfieldText',
						'label' => __('DKIM private'),
						'description' => __('DKIM private key file path.'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'DKIM_private_string' => [
						'type' => 'InputfieldText',
						'label' => __('DKIM private string'),
						'description' => __('If set, takes precedence over `$DKIM_private`.'),
						'attr' => [
							'type' => 'password',
						],
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
						'columnWidth' => 50,
					],
					'DKIM_copyHeaderFields' => [
						'type' => 'InputfieldCheckbox',
						'label' => __('DKIM copyHeaderFields'),
						'description' => __("Whether to include the 'z' tag in the DKIM signature."),
						'notes' => __('Only use this for debugging as it adds size to the signature and could be abused. See PHPMailer source for details.'),
						'value' => false,
						'collapsed' => Inputfield::collapsedNever,
					],
				],
			],
			'TabAdvanced' => [
				'type' => 'InputfieldFieldset',
				'label' => __('Advanced & Debug'),
				'collapsed' => Inputfield::collapsedYes,
				'children' => [
					'Hostname' => [
						'type' => 'InputfieldText',
						'label' => __('Hostname'),
						'description' => __('The hostname to use in the `Message-ID` header and as default `HELO` string.'),
						'notes' => __('If empty, PHPMailer attempts to find one automatically.'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
					],
					'XMailer' => [
						'type' => 'InputfieldText',
						'label' => __('XMailer'),
						'description' => __('What to put in the X-Mailer header.'),
						'notes' => __('Options: An empty string for PHPMailer default, whitespace for none, or a string to use.'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
					],
					'UseSMTPUTF8' => [
						'type' => 'InputfieldCheckbox',
						'label' => __('Use SMTP UTF8'),
						'description' => __('Enable SMTP UTF8 support (SMTPUTF8 extension).'),
						'value' => false,
						'collapsed' => Inputfield::collapsedNever,
					],
					'Timeout' => [
						'type' => 'InputfieldInteger',
						'label' => __('Timeout'),
						'description' => __('The SMTP server timeout in seconds.'),
						'notes' => __('Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.'),
						'inputType' => 'number',
						'min' => 0,
						'value' => 300,
						'collapsed' => Inputfield::collapsedNever,
					],
					'SMTPDebug' => [
						'type' => 'InputfieldSelect',
						'label' => __('SMTP Debug'),
						'description' => __('SMTP class debug output mode.'),
						'notes' => __('Debug output level.'),
						'required' => true,
						'value' => '0',
						'options' => [
							'0' => __('No output'),
							'1' => __('Commands'),
							'2' => __('Data and commands'),
							'3' => __('As 2 plus connection status'),
							'4' => __('Low-level data output.'),
						],
						'collapsed' => Inputfield::collapsedNever,
					],
					'Debugoutput' => [
						'type' => 'InputfieldSelect',
						'label' => __('Debug output'),
						'description' => __('How to handle debug output.'),
						'required' => true,
						'value' => 'html',
						'options' => [
							'echo' => __('Output plain-text as-is, appropriate for CLI'),
							'html' => __('Output escaped, line breaks converted to `<br>`, appropriate for browser output'),
							'error_log' => __('Output to error log as configured in php.ini'),
						],
						'collapsed' => Inputfield::collapsedNever,
					],
				],
			],
			'TabTest' => [
				'type' => 'InputfieldFieldset',
				'label' => __('Test'),
				'collapsed' => Inputfield::collapsedYes,
				'children' => [
					'_test_email' => [
						'type' => 'InputfieldEmail',
						'label' => __('Send a test email to'),
						'description' => __('Enter an email address and save the module configuration to send a test email. The address will not be saved.'),
						'value' => '',
						'collapsed' => Inputfield::collapsedNever,
					],
				],
			],
		]);
	}

	/**
	 * Process inputs and generate dynamic markup for OAuth2
	 */
	public function getInputfields(): InputfieldWrapper
	{
		$page = $this->wire()->page;
		$config = $this->wire()->config;
		$baseUrl = ($page !== null && $page->id > 0)
			? $page->httpUrl()
			: $config->urls->httpAdmin . 'module/edit';
		$rawProvider = (string) ($this->data['OAuthProvider'] ?? $this->get('OAuthProvider'));

		$oauthConfig = [
			'providerName' => $rawProvider !== '' ? $rawProvider : OAuthProvider::Google->value,
			'clientId' => (string) ($this->data['OAuthClientId'] ?? $this->get('OAuthClientId')),
			'clientSecret' => (string) ($this->data['OAuthClientSecret'] ?? $this->get('OAuthClientSecret')),
			'tenantId' => (string) ($this->data['OAuthTenantId'] ?? $this->get('OAuthTenantId')),
			'redirectUri' => $baseUrl . '?name=WireMailPHPMailer',
		];

		$inputfields = parent::getInputfields();

		$this->registerTestEmailHook();
		$this->handleOAuthSection($inputfields, $oauthConfig);

		return $inputfields;
	}

	/**
	 * Register a post-save hook to dispatch a test email using the newly saved module configuration
	 */
	private function registerTestEmailHook(): void
	{
		$input = $this->wire()->input;
		$sanitizer = $this->wire()->sanitizer;
		$session = $this->wire()->session;

		if (!$input->requestMethod('POST')) {
			return;
		}

		$rawEmail = $input->post('_test_email');
		if (!is_string($rawEmail) || $rawEmail === '' || !$session->CSRF->hasValidToken()) {
			return;
		}

		$testEmail = $sanitizer->email($rawEmail);
		if ($testEmail === '') {
			return;
		}

		$this->wire()->addHookAfter('Modules::saveConfig', function (HookEvent $event) use ($testEmail): void {
			$moduleName = (string) $event->arguments(0);
			if ($moduleName !== 'WireMailPHPMailer') {
				return;
			}
			$event->removeHook(null);

			try {
				$modules = $event->wire()->modules;
				/** @var WireMailPHPMailer|null $mail */
				$mail = $modules->get('WireMailPHPMailer');
				if ($mail instanceof WireMailPHPMailer) {
					$savedData = $event->arguments(1);
					if (is_array($savedData)) {
						$mail->setArray($savedData);
					}
					$mail->to($testEmail)
						->subject($this->_('ProcessWire: WireMailPHPMailer Test'))
						->bodyHTML($this->_('<h3>Test successful!</h3><p>If you are reading this, WireMailPHPMailer is configured correctly.</p>'))
						->body($this->_('Test successful! If you are reading this, WireMailPHPMailer is configured correctly.'));

					$numSent = $mail->send();
					if ($numSent > 0) {
						$this->message(sprintf($this->_('Test email successfully sent to %s'), $testEmail));
					} else {
						$this->error(sprintf($this->_('Failed to send test email to %s. Check Tracy Debugger or Error Logs.'), $testEmail));
					}
				}
			} catch (\Throwable $e) {
				$message = sprintf($this->_('Test email exception: %s'), $e->getMessage());
				$event->wire()->log->save('WireMailPHPMailer', $message);
				$this->error($message);
			}
		});
	}

	/**
	 * Process incoming OAuth2 authorization code and append OAuth2 authorization UI markup
	 *
	 * @param array{providerName: string, clientId: string, clientSecret: string, tenantId: string, redirectUri: string} $oauthConfig
	 */
	private function handleOAuthSection(InputfieldWrapper $inputfields, array $oauthConfig): void
	{
		$providerEnum = OAuthProvider::tryFrom($oauthConfig['providerName']);
		$providerObj = $providerEnum?->createProvider($oauthConfig);

		$this->processOAuthCallback($providerObj, $inputfields, $oauthConfig['redirectUri']);

		$oauthFieldset = $inputfields->getChildByName('OAuth2');
		if ($oauthFieldset instanceof InputfieldWrapper) {
			$oauthFieldset->add($this->buildOAuthMarkupField($providerEnum, $providerObj, $oauthConfig));
		}
	}

	/**
	 * Exchange an incoming OAuth2 authorization code for a refresh token and persist it
	 */
	private function processOAuthCallback(?object $providerObj, InputfieldWrapper $inputfields, string $redirectUri): void
	{
		$input = $this->wire()->input;
		$oauthCode = $input->get('code');
		if (!is_string($oauthCode) || $oauthCode === '' || $providerObj === null || !method_exists($providerObj, 'getAccessToken')) {
			return;
		}

		$session = $this->wire()->session;
		$expectedState = (string) $session->getFor('WireMailPHPMailer', 'oauthState');
		$receivedState = (string) $input->get('state');
		$session->removeFor('WireMailPHPMailer', 'oauthState');

		if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
			$this->error($this->_('OAuth state mismatch. Please try authorizing again.'));
			return;
		}

		try {
			$token = $providerObj->getAccessToken('authorization_code', [
				'code' => $oauthCode,
			]);
			$refreshToken = is_object($token) && method_exists($token, 'getRefreshToken')
				? (string) $token->getRefreshToken()
				: '';

			if ($refreshToken === '') {
				$this->error($this->_('Failed to fetch Refresh Token. The provider did not return one. You might need to re-consent the app.'));
				return;
			}

			$this->wire()->modules->saveConfig('WireMailPHPMailer', 'OAuthRefreshToken', $refreshToken);
			$this->set('OAuthRefreshToken', $refreshToken);

			$refreshField = $inputfields->getChildByName('OAuthRefreshToken');
			if ($refreshField !== null) {
				$refreshField->attr('value', $refreshToken);
			}

			$this->message($this->_('Successfully generated and saved Refresh Token.'));
			if ($redirectUri !== '?name=WireMailPHPMailer') {
				$session->redirect($redirectUri, false);
			}
		} catch (\Throwable $e) {
			$message = sprintf($this->_('OAuth Token Error: %s'), $e->getMessage());
			$this->wire()->log->save('WireMailPHPMailer', $message);
			$this->error($message);
		}
	}

	/**
	 * Build the OAuth2 authorization status and action button markup field
	 *
	 * @param array{providerName: string, clientId: string, clientSecret: string, tenantId: string, redirectUri: string} $oauthConfig
	 */
	private function buildOAuthMarkupField(?OAuthProvider $providerEnum, ?object $providerObj, array $oauthConfig): InputfieldMarkup
	{
		$input = $this->wire()->input;
		$sanitizer = $this->wire()->sanitizer;
		$session = $this->wire()->session;

		/** @var InputfieldMarkup $markup */
		$markup = $this->wire()->modules->get('InputfieldMarkup');
		$markup->attr('name', '_oauth_markup');
		$markup->label = __('Authorize Account');

		if ($providerEnum === null || !$providerEnum->hasRequiredCredentials($oauthConfig['clientId'], $oauthConfig['clientSecret'])) {
			$markup->markupText = '<p>' . $this->_('Please enter your Client ID & Secret, save the settings, and then a button will appear here to authorize the app and generate a refresh token.') . '</p>';
		} elseif (!$providerEnum->isInstalled() || $providerObj === null) {
			$packageToInstall = $providerEnum->packageName();
			$markup->markupText = "<p class='NoticeError'>" . sprintf(
				$this->_('OAuth library for %s is not installed! Please run `composer require %s` in your project root.'),
				$sanitizer->entities(strtoupper($oauthConfig['providerName'])),
				$sanitizer->entities($packageToInstall)
			) . '</p>';
		} elseif (method_exists($providerObj, 'getAuthorizationUrl') && !$input->requestMethod('POST')) {
			$options = $providerEnum->authorizationOptions();
			$authUrl = (string) $providerObj->getAuthorizationUrl($options);
			if (method_exists($providerObj, 'getState')) {
				$session->setFor('WireMailPHPMailer', 'oauthState', (string) $providerObj->getState());
			}
			$escapedAuthUrl = $sanitizer->entities($authUrl);
			$escapedButtonLabel = $sanitizer->entities(
				sprintf($this->_('Authorize via %s & Fetch Refresh Token'), $providerEnum->label())
			);
			$escapedRedirectUri = $sanitizer->entities($oauthConfig['redirectUri']);

			$markup->markupText = "<a href='{$escapedAuthUrl}' class='ui-button ui-widget ui-corner-all'>{$escapedButtonLabel}</a>"
				. "<p class='notes'>" . $this->_('Clicking this button will take you to the OAuth provider. You will be redirected back here afterwards. Make sure you have added:') . '<br><b>' . $escapedRedirectUri . '</b><br>' . $this->_('as an authorized Redirect URI / Reply URL in your app console.') . '</p>';
		}

		return $markup;
	}
}
