<?php

declare(strict_types=1);

namespace ProcessWire;

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
    public function __construct()
    {
        $this->add([
            "TabGeneral" => [
                "type" => "InputfieldFieldset",
                "label" => __("General"),
                "class" => "WireTab",
                "children" => [
                    "Priority" => [
                        "type" => "InputfieldSelect",
                        "label" => __("Priority"),
                        "description" => __("When null, the header is not set at all."),
                        "required" => true,
                        "value" => "null",
                        "options" => [
                            "null" => __("Default"),
                            1 => __("High"),
                            3 => __("Normal"),
                            5 => __("Low")
                        ],
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 25
                    ],
                    "CharSet" => [
                        "type" => "InputfieldText",
                        "label" => __("Character set"),
                        "description" => __("The character set of the message."),
                        "value" => "utf-8",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 25
                    ],
                    "ContentType" => [
                        "type" => "InputfieldSelect",
                        "label" => __("Content-type"),
                        "description" => __("The MIME Content-type of the message."),
                        "required" => true,
                        "value" => "text/html",
                        "options" => [
                            "text/plain" => "text/plain",
                            "text/html" => "text/html",
                            "multipart/related" => "multipart/related",
                            "multipart/alternative" => "multipart/alternative",
                            "multipart/mixed" => "multipart/mixed"
                        ],
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 25
                    ],
                    "Encoding" => [
                        "type" => "InputfieldSelect",
                        "label" => __("Encoding"),
                        "description" => __("The message encoding."),
                        "required" => true,
                        "value" => "8bit",
                        "options" => [
                            "8bit" => "8bit",
                            "7bit" => "7bit",
                            "binary" => "binary",
                            "base64" => "base64",
                            "quoted-printable" => "quoted-printable"
                        ],
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 25
                    ],
                    "WordWrap" => [
                        "type" => "InputfieldInteger",
                        "label" => __("WordWrap"),
                        "description" => __("Word-wrap the message body to this number of chars."),
                        "notes" => __("Set to 0 to not wrap. A useful value here is 78, for [RFC2822](https://www.ietf.org/rfc/rfc2822.txt) section 2.1.1 compliance."),
                        "value" => "0",
                        "inputType" => "number",
                        "min" => 0,
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "AllowEmpty" => [
                        "type" => "InputfieldCheckbox",
                        "label" => __("Allow Empty"),
                        "description" => __("Whether to allow sending messages with an empty body."),
                        "value" => false,
                        "collapsed" => Inputfield::collapsedNever
                    ],

                ]
            ],
            "TabRouting" => [
                "type" => "InputfieldFieldset",
                "label" => __("Routing"),
                "class" => "WireTab",
                "children" => [
                    "Sender" => [
                        "type" => "InputfieldText",
                        "label" => __("Sender"),
                        "description" => __("The envelope sender of the message. This will usually be turned into a Return-Path header by the receiver, and is the address that bounces will be sent to."),
                        "notes" => __("If not empty, will be passed via `-f` to sendmail or as the `MAIL FROM` value over SMTP."),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "FromName" => [
                        "type" => "InputfieldText",
                        "label" => __("From Name"),
                        "description" => __("The From name of the message."),
                        "value" => "Root User",
                        "placeholder" => __("Site administrator"),
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "From" => [
                        "type" => "InputfieldText",
                        "label" => __("From"),
                        "description" => __("The From email address for the message."),
                        "value" => "root@localhost",
                        "placeholder" => "email@domain.ltd",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "Subject" => [
                        "type" => "InputfieldText",
                        "label" => __("Subject"),
                        "description" => __("The Subject of the message."),
                        "value" => "",
                        "placeholder" => __("An email subject"),
                        "collapsed" => Inputfield::collapsedNever
                    ],


                ]
            ],
            "TabTransport" => [
                "type" => "InputfieldFieldset",
                "label" => __("Transport"),
                "class" => "WireTab",
                "children" => [
                    "Mailer" => [
                        "type" => "InputfieldSelect",
                        "label" => __("Mailer"),
                        "description" => __("Which method to use to send mail."),
                        "required" => true,
                        "value" => "mail",
                        "options" => [
                            "mail" => "mail",
                            "sendmail" => "sendmail",
                            "smtp" => "smtp"
                        ],
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "SendmailSettings" => [
                        "type" => "InputfieldFieldset",
                        "label" => __("Sendmail Settings"),
                        "showIf" => "Mailer=sendmail",
                        "collapsed" => Inputfield::collapsedNo,
                        "children" => [
                            "Sendmail" => [
                                "type" => "InputfieldText",
                                "label" => __("Sendmail Path"),
                                "description" => __("The path to the sendmail program."),
                                "value" => "/usr/sbin/sendmail",
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "UseSendmailOptions" => [
                                "type" => "InputfieldCheckbox",
                                "label" => __("Use Sendmail Options"),
                                "description" => __("Whether `mail()` uses a fully `sendmail-compatible` MTA."),
                                "notes" => __("One which supports sendmail's `-oi -f` options."),
                                "value" => true,
                                "collapsed" => Inputfield::collapsedNever
                            ]
                        ]
                    ],
                    "SMTP" => [
                        "type" => "InputfieldFieldset",
                        "label" => __("SMTP Settings"),
                        "showIf" => "Mailer=smtp",
                        "collapsed" => Inputfield::collapsedNo,
                        "children" => [
                            "dsn" => [
                                "type" => "InputfieldText",
                                "label" => __("DSN"),
                                "description" => __("A Data Source Name (DSN) string for SMTP setup."),
                                "notes" => __("Overrides Host, Port, SMTPSecure, Username, Password. e.g. smtps://user:password@smtp.example.com:465"),
                                "value" => "",
                                "collapsed" => Inputfield::collapsedBlank,
                            ],
                            "Host" => [
                                "type" => "InputfieldText",
                                "label" => __("Host"),
                                "description" => __("SMTP hosts."),
                                "notes" => __('Either a single hostname or multiple semicolon-delimited hostnames. e.g. "smtp1.example.com;smtp2.example.com".'),
                                "value" => "localhost",
                                "columnWidth" => 50,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "Helo" => [
                                "type" => "InputfieldText",
                                "label" => __("Helo"),
                                "description" => __("The SMTP HELO of the message."),
                                "notes" => __('Default is `$Hostname`.'),
                                "value" => "",
                                "columnWidth" => 50,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "SMTPSecure" => [
                                "type" => "InputfieldSelect",
                                "label" => __("SMTP Secure"),
                                "description" => __("What kind of encryption to use on the SMTP connection."),
                                "value" => "tls",
                                "options" => [
                                    "" => __("Default"),
                                    "ssl" => __("SSL"),
                                    "tls" => __("TLS")
                                ],
                                "columnWidth" => 33,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "Port" => [
                                "type" => "InputfieldInteger",
                                "label" => __("Port"),
                                "description" => __("The default SMTP server port."),
                                "inputType" => "number",
                                "min" => 0,
                                "value" => 587,
                                "columnWidth" => 34,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "SMTPAutoTLS" => [
                                "type" => "InputfieldCheckbox",
                                "label" => __("SMTP Auto TLS"),
                                "description" => __("Whether to enable SMTP Auto TLS."),
                                "value" => true,
                                "columnWidth" => 33,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "SMTPAuth" => [
                                "type" => "InputfieldCheckbox",
                                "label" => __("SMTP Auth"),
                                "description" => __("Whether to use SMTP authentication."),
                                "notes" => __("Uses the Username and Password properties."),
                                "value" => false,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "Username" => [
                                "type" => "InputfieldText",
                                "label" => __("Username"),
                                "description" => __("SMTP username."),
                                "value" => "",
                                "columnWidth" => 50,
                                "showIf" => "SMTPAuth=1",
                                "collapsed" => Inputfield::collapsedBlank
                            ],
                            "Password" => [
                                "type" => "InputfieldText",
                                "label" => __("Password"),
                                "description" => __("SMTP password."),
                                "attr" => [
                                    "type" => "password"
                                ],
                                "showIf" => "SMTPAuth=1",
                                "value" => "",
                                "columnWidth" => 50,
                                "collapsed" => Inputfield::collapsedBlank
                            ],
                            "AuthType" => [
                                "type" => "InputfieldSelect",
                                "label" => __("Auth Type"),
                                "description" => __("SMTP auth type."),
                                "notes" => __("Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified."),
                                "value" => "",
                                "showIf" => "SMTPAuth=1",
                                "options" => [
                                    "" => __("Default"),
                                    "CRAM-MD5" => "CRAM-MD5",
                                    "LOGIN" => "LOGIN",
                                    "PLAIN" => "PLAIN",
                                    "XOAUTH2" => "XOAUTH2"
                                ],
                                "columnWidth" => 100,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "OAuth2" => [
                                "type" => "InputfieldFieldset",
                                "label" => __("OAuth2 Settings"),
                                "showIf" => "SMTPAuth=1, AuthType=XOAUTH2",
                                "collapsed" => Inputfield::collapsedNo,
                                "children" => [
                                    "OAuthProvider" => [
                                        "type" => "InputfieldSelect",
                                        "label" => __("OAuth Provider"),
                                        "description" => __("Select the OAuth2 provider. Make sure the corresponding provider library is installed via Composer."),
                                        "required" => true,
                                        "value" => "google",
                                        "options" => [
                                            "google" => __("Google"),
                                            "yahoo" => __("Yahoo"),
                                            "microsoft" => __("Microsoft"),
                                            "azure" => __("Microsoft Azure (Greew)")
                                        ],
                                        "collapsed" => Inputfield::collapsedNever,
                                        "columnWidth" => 50
                                    ],
                                    "OAuthEmail" => [
                                        "type" => "InputfieldText",
                                        "label" => __("OAuth Email"),
                                        "description" => __("The email address of the account you are authorizing."),
                                        "value" => "",
                                        "collapsed" => Inputfield::collapsedNever,
                                        "columnWidth" => 50
                                    ],
                                    "OAuthClientId" => [
                                        "type" => "InputfieldText",
                                        "label" => __("Client ID"),
                                        "description" => __("The Client ID from your OAuth provider."),
                                        "value" => "",
                                        "collapsed" => Inputfield::collapsedNever,
                                        "columnWidth" => 50
                                    ],
                                    "OAuthClientSecret" => [
                                        "type" => "InputfieldText",
                                        "label" => __("Client Secret"),
                                        "description" => __("The Client Secret from your OAuth provider. Not required for Azure if using delegated permissions for public clients, but typically needed."),
                                        "value" => "",
                                        "collapsed" => Inputfield::collapsedNever,
                                        "columnWidth" => 50
                                    ],
                                    "OAuthTenantId" => [
                                        "type" => "InputfieldText",
                                        "label" => __("Tenant ID (Azure Only)"),
                                        "description" => __("The Tenant ID from Microsoft Azure."),
                                        "showIf" => "OAuthProvider=azure",
                                        "value" => "",
                                        "collapsed" => Inputfield::collapsedNever,
                                        "columnWidth" => 100
                                    ],
                                    "OAuthRefreshToken" => [
                                        "type" => "InputfieldText",
                                        "label" => __("Refresh Token"),
                                        "description" => __("The Refresh Token. Once obtained, it is saved here automatically."),
                                        "value" => "",
                                        "collapsed" => Inputfield::collapsedNever,
                                    ]
                                ]
                            ],
                            "SMTPKeepAlive" => [
                                "type" => "InputfieldCheckbox",
                                "label" => __("SMTP Keep Alive"),
                                "description" => __("Whether to keep SMTP connection open after each message."),
                                "notes" => __("If this is set to true then to close the connection requires an explicit call to `smtpClose()`."),
                                "value" => false,
                                "collapsed" => Inputfield::collapsedNever
                            ],
                            "do_verp" => [
                                "type" => "InputfieldCheckbox",
                                "label" => __("Generate VERP addresses"),
                                "description" => __("Whether to generate VERP addresses on send."),
                                "notes" => __("Only applicable when sending via SMTP. [see](https://en.wikipedia.org/wiki/Variable_envelope_return_path), [see](http://www.postfix.org/VERP_README.html)"),
                                "value" => "",
                                "collapsed" => Inputfield::collapsedNever
                            ],
                        ]
                    ]
                ]
            ],
            "TabDKIM" => [
                "type" => "InputfieldFieldset",
                "label" => __("DKIM"),
                "class" => "WireTab",
                "children" => [
                    "DKIM_domain" => [
                        "type" => "InputfieldText",
                        "label" => __("DKIM domain"),
                        "description" => __("DKIM signing domain name."),
                        "notes" => __("example: `example.com`"),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "DKIM_identity" => [
                        "type" => "InputfieldText",
                        "label" => __("DKIM identity"),
                        "description" => __("Usually the email address used as the source of the email."),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "DKIM_selector" => [
                        "type" => "InputfieldText",
                        "label" => __("DKIM selector"),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "DKIM_passphrase" => [
                        "type" => "InputfieldText",
                        "label" => __("DKIM passphrase"),
                        "description" => __("Used if your key is encrypted."),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "DKIM_private" => [
                        "type" => "InputfieldText",
                        "label" => __("DKIM private"),
                        "description" => __("DKIM private key file path."),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "DKIM_private_string" => [
                        "type" => "InputfieldText",
                        "label" => __("DKIM private string"),
                        "description" => __('If set, takes precedence over `$DKIM_private`.'),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever,
                        "columnWidth" => 50
                    ],
                    "DKIM_copyHeaderFields" => [
                        "type" => "InputfieldCheckbox",
                        "label" => __("DKIM copyHeaderFields"),
                        "description" => __("Whether to include the 'z' tag in the DKIM signature."),
                        "notes" => __("Only use this for debugging as it adds size to the signature and could be abused. See PHPMailer source for details."),
                        "value" => false,
                        "collapsed" => Inputfield::collapsedNever,
                    ],
                ]
            ],
            "TabAdvanced" => [
                "type" => "InputfieldFieldset",
                "label" => __("Advanced & Debug"),
                "class" => "WireTab",
                "children" => [
                    "Hostname" => [
                        "type" => "InputfieldText",
                        "label" => __("Hostname"),
                        "description" => __("The hostname to use in the `Message-ID` header and as default `HELO` string."),
                        "notes" => __('If empty, PHPMailer attempts to find one automatically.'),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "XMailer" => [
                        "type" => "InputfieldText",
                        "label" => __("XMailer"),
                        "description" => __("What to put in the X-Mailer header."),
                        "notes" => __("Options: An empty string for PHPMailer default, whitespace for none, or a string to use."),
                        "value" => "",
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "UseSMTPUTF8" => [
                        "type" => "InputfieldCheckbox",
                        "label" => __("Use SMTP UTF8"),
                        "description" => __("Enable SMTP UTF8 support (SMTPUTF8 extension)."),
                        "value" => false,
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "Timeout" => [
                        "type" => "InputfieldInteger",
                        "label" => __("Timeout"),
                        "description" => __("The SMTP server timeout in seconds."),
                        "notes" => __("Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2."),
                        "inputType" => "number",
                        "min" => 0,
                        "value" => 300,
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "SMTPDebug" => [
                        "type" => "InputfieldSelect",
                        "label" => __("SMTP Debug"),
                        "description" => __("SMTP class debug output mode."),
                        "notes" => __("Debug output level."),
                        "required" => true,
                        "value" => "0",
                        "options" => [
                            "0" => __("No output"),
                            "1" => __("Commands"),
                            "2" => __("Data and commands"),
                            "3" => __("As 2 plus connection status"),
                            "4" => __("Low-level data output.")
                        ],
                        "collapsed" => Inputfield::collapsedNever
                    ],
                    "Debugoutput" => [
                        "type" => "InputfieldSelect",
                        "label" => __("Debug output"),
                        "description" => __("How to handle debug output."),
                        "required" => true,
                        "value" => "html",
                        "options" => [
                            "echo" => __("Output plain-text as-is, appropriate for CLI"),
                            "html" => __("Output escaped, line breaks converted to `<br>`, appropriate for browser output"),
                            "error_log" => __("Output to error log as configured in php.ini")
                        ],
                        "collapsed" => Inputfield::collapsedNever
                    ],
                ]
            ],
        ]);
    }

    /**
     * Optional method to process inputs and generate dynamic markup for OAuth2
     */
    public function getInputfields(): InputfieldWrapper
    {
        $inputfields = parent::getInputfields();
        $input = $this->wire('input');
        $page = $this->wire('page');

        // Check if OAuth attributes are present
        $providerName = $this->get('OAuthProvider');
        $clientId = $this->get('OAuthClientId');
        $clientSecret = $this->get('OAuthClientSecret');
        $tenantId = $this->get('OAuthTenantId');
        
        $redirectUri = $page->httpUrl() . '?name=WireMailPHPMailer';

        // Check if provider class is available
        $providerClass = null;
        $providerObj = null;

        if ($providerName === 'google') {
            $providerClass = '\\League\\OAuth2\\Client\\Provider\\Google';
            if (class_exists($providerClass)) {
                $providerObj = new $providerClass([
                    'clientId'     => $clientId,
                    'clientSecret' => $clientSecret,
                    'redirectUri'  => $redirectUri,
                    'accessType'   => 'offline'
                ]);
            }
        } elseif ($providerName === 'yahoo') {
            $providerClass = '\\Hayageek\\OAuth2\\Client\\Provider\\Yahoo';
            if (class_exists($providerClass)) {
                $providerObj = new $providerClass([
                    'clientId'     => $clientId,
                    'clientSecret' => $clientSecret,
                    'redirectUri'  => $redirectUri
                ]);
            }
        } elseif ($providerName === 'microsoft') {
            $providerClass = '\\Stevenmaguire\\OAuth2\\Client\\Provider\\Microsoft';
            if (class_exists($providerClass)) {
                $providerObj = new $providerClass([
                    'clientId'     => $clientId,
                    'clientSecret' => $clientSecret,
                    'redirectUri'  => $redirectUri
                ]);
            }
        } elseif ($providerName === 'azure') {
            $providerClass = '\\Greew\\OAuth2\\Client\\Provider\\Azure';
            if (class_exists($providerClass)) {
                $providerObj = new $providerClass([
                    'clientId'                => $clientId,
                    'clientSecret'            => $clientSecret,
                    'redirectUri'             => $redirectUri,
                    'tenant'                  => $tenantId ?: 'common',
                    'defaultEndPointVersion'  => '2.0'
                ]);
                $providerObj->scope = implode(' ', [
                    'offline_access',
                    'https://outlook.office.com/SMTP.Send'
                ]);
            }
        }

        // Process incoming OAuth Code
        if ($input->get('code') && $providerObj) {
            try {
                $token = $providerObj->getAccessToken('authorization_code', [
                    'code' => $input->get('code')
                ]);
                $refreshToken = $token->getRefreshToken();
                if ($refreshToken) {
                    $this->message("Successfully generated Refresh Token! Please save the module settings.");
                    
                    $refreshField = $inputfields->getChildByName('OAuthRefreshToken');
                    if ($refreshField) {
                        $refreshField->attr('value', $refreshToken);
                    }
                } else {
                    $this->error("Failed to fetch Refresh Token. The provider did not return one. You might need to re-consent the app.");
                }
            } catch (\Exception $e) {
                $this->error("OAuth Token Error: " . $e->getMessage());
            }
        }

        // Generate Markup
        $markup = $this->wire('modules')->get('InputfieldMarkup');
        $markup->name = 'OAuthMarkup';
        $markup->label = __('Authorize Account');
        
        if (!$clientId) {
            $markup->value = "<p>" . __("Please enter your Client ID & Secret, save the settings, and then a button will appear here to authorize the app and generate a refresh token.") . "</p>";
        } elseif (!$providerObj) {
            $missingPackages = [
                'google'    => 'league/oauth2-google',
                'yahoo'     => 'hayageek/oauth2-yahoo',
                'microsoft' => 'stevenmaguire/oauth2-microsoft',
                'azure'     => 'greew/oauth2-azure-provider',
            ];
            $packageToInstall = $missingPackages[$providerName] ?? 'unknown/package';
            $markup->value = "<p class='NoticeError'>" . sprintf(__("OAuth library for %s is not installed! Please run `composer require %s` in your project root."), strtoupper($providerName), $packageToInstall) . "</p>";
        } else {
            $options = [];
            if ($providerName === 'google') {
                $options = [
                    'prompt' => 'consent',
                    'scope' => ['https://mail.google.com/']
                ];
            }
            $authUrl = $providerObj->getAuthorizationUrl($options);
            $markup->value = "<a href='{$authUrl}' class='ui-button ui-widget ui-corner-all'>" . __("Authorize via ") . ucfirst($providerName) . " & Fetch Refresh Token</a>";
            $markup->value .= "<p class='notes'>" . __("Clicking this button will take you to the OAuth provider. You will be redirected back here afterwards. Make sure you have added:") . "<br><b>" . htmlentities($redirectUri) . "</b><br>" . __("as an authorized Redirect URI / Reply URL in your app console.") . "</p>";
        }

        $oauthFieldset = $inputfields->getChildByName('OAuth2');
        if ($oauthFieldset) {
            $oauthFieldset->add($markup);
        }

        return $inputfields;
    }
}