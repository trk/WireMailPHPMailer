<?php

declare(strict_types=1);

namespace ProcessWire;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\DSNConfigurator;

require_once __DIR__ . "/vendor/autoload.php";

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
     * @var array
     */
    protected array $options = [];

    protected const COMPATIBILITY = [
        'from' => 'setFrom',
        'fromName' => 'setFrom',
        'subject' => 'Subject',
        'body' => 'AltBody',
        'bodyHTML' => 'Body'
    ];

    /**
     * Module info
     *
     * @see Module
     * @return array
     */
    public static function getModuleInfo(): array
    {
        return [
            'title' => 'WireMailPHPMailer',
            'version' => 147,
            'summary' => __('This module extends WireMail base class, integrating the PHPMailer mailing library into ProcessWire.'),
            'href' => 'https://github.com/trk/WireMailPHPMailer',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'ProcessWire>=3.0.0'
            ],
            'installs' => [],
            'icon' => 'envelope-o',
            'singular' => false,
            'autoload' => false
        ];
    }

    public function __get($key): mixed
    {
        if (isset(self::COMPATIBILITY[$key])) {
            $map = self::COMPATIBILITY[$key];
            if ($map === 'setFrom') {
                if ($key === 'from') {
                    return $this->options['setFrom']['address'] ?? parent::__get($key);
                }
                return $this->options['setFrom']['name'] ?? parent::__get($key);
            }
            if ($map === 'Body') {
                return $this->options['msgHTML']['message'] ?? parent::__get($key);
            }
            return $this->options[$map] ?? parent::__get($key);
        }
        return parent::__get($key);
    }

    /**
     * Initialize the module
     */
    public function init(): void
    {
        $this->options = [
            'AltBody'   => '',
            'Subject'   => '',
            'setFrom'   => [
                'address' => '',
                'name' => '',
                'auto' => false
            ],
            'addAddress' => [],
            'addCC' => [],
            'addBCC' => [],
            'addReplyTo' => [],
            'msgHTML' => [
                'message' => '',
                'basedir' => '',
                'advanced' => false
            ]
        ];
    }

    /**
     * Return PHPMailer instance
     *
     * @return PHPMailer
     */
    public function mailer(): PHPMailer
    {
        return $this->getInstance();
    }

    /**
     * Return PHPMailer instance
     *
     * @param bool $initialize
     * @param bool $exceptions
     * @return PHPMailer
     */
    public function getInstance(bool $initialize = true, bool $exceptions = false): PHPMailer
    {
        $instance = new PHPMailer($exceptions);

        if ($initialize) {
            $instance = $this->setModuleSettings($instance);
        }

        return $instance;
    }

    /**
     * Cast a config value according to its expected PHPMailer type
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    private function castConfigValue(string $key, mixed $value): mixed
    {
        $intKeys = ['WordWrap', 'Port', 'Timeout', 'SMTPDebug'];
        if (in_array($key, $intKeys, true)) {
            return is_numeric($value) ? (int)$value : 0;
        }

        $boolKeys = [
            'SMTPAutoTLS',
            'SMTPAuth',
            'SMTPKeepAlive',
            'do_verp',
            'UseSendmailOptions',
            'AllowEmpty',
            'UseSMTPUTF8',
            'DKIM_copyHeaderFields'
        ];
        if (in_array($key, $boolKeys, true)) {
            if (is_bool($value)) return $value;
            if ($value === '1' || $value === 1 || $value === 'true') return true;
            return false;
        }

        if ($key === 'Priority') {
            if ($value === 'null' || $value === null || $value === '') return null;
            return is_numeric($value) ? (int)$value : null;
        }

        return $value;
    }

    /**
     * Map a provider slug to its FQCN and constructor options
     *
     * Centralizes provider configuration so WireMailPHPMailer and WireMailPHPMailerConfig
     * construct OAuth2 providers consistently. Returns null when the provider library
     * is not installed.
     *
     * Supported $providerName values: 'google', 'yahoo', 'microsoft', 'azure'
     *
     * @param string $providerName  Provider slug from module config
     * @param string $clientId      OAuth Client ID
     * @param string $clientSecret  OAuth Client Secret
     * @param string $redirectUri   Authorization redirect URI (only needed for authorization flows)
     * @param string $tenantId      Azure/Microsoft tenant ID (fallback: 'common')
     * @param array  $extra         Additional overrides merged into constructor args
     * @return object|null          Provider instance or null when library missing / invalid slug
     */
    public static function getProvider(
        string $providerName,
        string $clientId,
        string $clientSecret,
        string $redirectUri = '',
        string $tenantId = 'common',
        array $extra = []
    ): ?object {
        if ($clientId === '' || $clientSecret === '' || $providerName === '') {
            return null;
        }

        $common = [
            'clientId'     => $clientId,
            'clientSecret' => $clientSecret,
        ];

        switch ($providerName) {
            case 'google':
                $class = '\\League\\OAuth2\\Client\\Provider\\Google';
                if (!class_exists($class)) return null;
                $args = $common + [
                    'redirectUri' => $redirectUri,
                    'accessType'  => 'offline',
                ];
                break;

            case 'yahoo':
                $class = '\\Hayageek\\OAuth2\\Client\\Provider\\Yahoo';
                if (!class_exists($class)) return null;
                $args = $common + [
                    'redirectUri' => $redirectUri,
                ];
                break;

            case 'microsoft':
                $class = '\\Stevenmaguire\\OAuth2\\Client\\Provider\\Microsoft';
                if (!class_exists($class)) return null;
                $args = $common + [
                    'redirectUri' => $redirectUri,
                ];
                break;

            case 'azure':
                $class = '\\Greew\\OAuth2\\Client\\Provider\\Azure';
                if (!class_exists($class)) return null;
                $args = $common + [
                    'redirectUri'             => $redirectUri,
                    'tenantId'                => $tenantId !== '' ? $tenantId : 'common',
                    'defaultEndPointVersion'  => '2.0',
                ];
                break;

            default:
                return null;
        }

        if ($extra !== []) {
            $args = array_replace($args, $extra);
        }

        return new $class($args);
    }

    /**
     * Apply module settings
     *
     * @param PHPMailer $instance
     * 
     * @return PHPMailer
     */
    protected function setModuleSettings(PHPMailer $instance): PHPMailer
    {
        $data = $this->getArray();

        $smtpDsn = '';
        if (isset($data['dsn']) && is_string($data['dsn']) && $data['dsn'] !== '') {
            $smtpDsn = $data['dsn'];
            unset($data['dsn']);
        }

        if (isset($data['Mailer']) && $data['Mailer'] === 'smtp') {
            $instance->isSMTP();

            if ($smtpDsn !== '') {
                $configurator = new DSNConfigurator();
                $instance = $configurator->configure($instance, $smtpDsn);
            }

            if (isset($data['AuthType']) && $data['AuthType'] === 'XOAUTH2') {
                $providerName = (string)($data['OAuthProvider'] ?? '');
                $clientId     = (string)($data['OAuthClientId'] ?? '');
                $clientSecret = (string)($data['OAuthClientSecret'] ?? '');
                $tenantId     = (string)($data['OAuthTenantId'] ?? '');
                $refreshToken = (string)($data['OAuthRefreshToken'] ?? '');
                $email        = (string)($data['OAuthEmail'] ?? '');

                $providerObj = self::getProvider(
                    $providerName,
                    $clientId,
                    $clientSecret,
                    '',
                    $tenantId
                );

                if ($providerObj !== null && class_exists('\\PHPMailer\\PHPMailer\\OAuth')) {
                    $instance->setOAuth(
                        new \PHPMailer\PHPMailer\OAuth([
                            'provider'     => $providerObj,
                            'clientId'     => $clientId,
                            'clientSecret' => $clientSecret,
                            'refreshToken' => $refreshToken,
                            'userName'     => $email,
                        ])
                    );
                } elseif ($providerObj === null && $providerName !== '') {
                    wireLog('WireMailPHPMailer', "OAuth provider library for '{$providerName}' not found. Please install it via Composer. XOAUTH2 may fail.");
                }
            }
        }

        foreach ($data as $key => $value) {
            if ($value === '' && !in_array($key, ['CharSet', 'ContentType', 'Encoding', 'XMailer', 'Hostname'], true)) {
                continue;
            }
            $instance->set((string)$key, $this->castConfigValue((string)$key, $value));
        }

        return $instance;
    }

    // ------------------------------------------------------------------------

    /**
     * Apply user settings
     *
     * @param PHPMailer $instance
     * 
     * @return PHPMailer
     */
    protected function setUserSettings(PHPMailer $instance): PHPMailer
    {
        foreach ($this->options as $name => $value) {
            if (in_array($name, ['AltBody', 'Subject', 'Body'], true) && is_string($value) && $value !== '') {
                if ($name === 'Body') {
                    $instance->isHTML(true);
                }
                $instance->{$name} = $value;
            } elseif (in_array($name, ['addAddress', 'addCC', 'addBCC', 'addReplyTo'], true) && is_array($value) && count($value) > 0) {
                foreach ($value as $e => $n) {
                    $instance->{$name}($e, $n);
                }
            } elseif ($name === 'setFrom' && !empty($value['address'])) {
                $instance->setFrom($value['address'], $value['name'], (bool)$value['auto']);
            } elseif ($name === 'msgHTML' && !empty($value['message'])) {
                $instance->msgHTML($value['message'], $value['basedir'], $value['advanced']);
            } elseif ($name === 'addAttachment' && is_array($value) && count($value) > 0) {
                foreach ($value as $p => $v) {
                    $instance->addAttachment((string)$p, (string)$v['name'], (string)$v['encoding'], (string)$v['type'], (string)$v['disposition']);
                }
            }
        }

        return $instance;
    }

    // ------------------------------------------------------------------------

    /**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     *
     * @param string $AltBody
     * @return $this
     */
    public function AltBody(string $AltBody = ""): self
    {
        if ($AltBody !== '') {
            $this->options['AltBody'] = $AltBody;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * The Subject of the message.
     *
     * @param string $Subject
     * @return $this
     */
    public function addSubject(string $Subject = ""): self
    {
        if ($Subject !== '') {
            $this->options['Subject'] = $Subject;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "To" address.
     *
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function addAddress(string $address, string $name = ''): self
    {
        $this->options['addAddress'][$address] = $name;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "CC" address.
     *
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function addCC(string $address, string $name = ''): self
    {
        $this->options['addCC'][$address] = $name;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "BCC" address.
     *
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function addBCC(string $address, string $name = ''): self
    {
        $this->options['addBCC'][$address] = $name;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "Reply-To" address.
     *
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function addReplyTo(string $address, string $name = ''): self
    {
        $this->options['addReplyTo'][$address] = $name;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Parse and validate a string containing one or more RFC822-style comma-separated email addresses
     *
     * @param string $addrstr The address list string
     * @param bool $useimap Whether to use the IMAP extension to parse the list
     * @return array
     */
    public function parseAddresses(string $addrstr, bool $useimap = true): array
    {
        return PHPMailer::parseAddresses($addrstr, $useimap);
    }

    // ------------------------------------------------------------------------

    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool $auto
     * @return $this
     */
    public function setFrom(string $address, string $name = '', bool $auto = true): self
    {
        $this->options['setFrom'] = [
            'address' => $address,
            'name' => $name,
            'auto' => $auto
        ];

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Create a message from an HTML string.
     *
     * @param string $message HTML message string
     * @param string $basedir baseline directory for path
     * @param boolean|callable $advanced
     * @return $this
     */
    public function msgHTML(string $message, string $basedir = '', $advanced = false): self
    {
        $this->options['msgHTML'] = [
            'message' => $message,
            'basedir' => $basedir,
            'advanced' => $advanced
        ];

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add an attachment from a path on the filesystem.
     *
     * @param string $path
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @param string $disposition
     * @return $this
     */
    public function addAttachment(string $path, string $name = '', string $encoding = 'base64', string $type = '', string $disposition = 'attachment'): self
    {
        $this->options['addAttachment'][$path] = [
            'name' => $name,
            'encoding' => $encoding,
            'type' => $type,
            'disposition' => $disposition
        ];

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a file to be attached to the email
     *
     * @param mixed $value Full path and filename of file attachment
     * @param string $filename Optional different basename for file as it appears in the mail
     * @return $this 
     */
    public function attachment($value, $filename = '')
    {
        return parent::attachment($value, $filename);
    }

    // ------------------------------------------------------------------------

    /**
     * Send the email
     *
     * @return int Returns the number of successfully sent messages
     */
    public function ___send(): int
    {
        $instance = $this->getInstance();

        try {
            if (is_array($this->attachments) && count($this->attachments) > 0) {
                foreach ($this->attachments as $filename => $file) {
                    $this->addAttachment((string)$file, (string)$filename);
                }
            }

            if (is_array($this->mail)) {
                $toNameList = $this->mail['toName'] ?? null;

                foreach ($this->mail as $key => $value) {
                    if ($key === 'to') {
                        if (is_array($value)) {
                            $names = is_array($toNameList) ? $toNameList : [];
                            foreach ($value as $i => $e) {
                                $n = isset($names[$i]) ? (string)$names[$i] : '';
                                $this->addAddress((string)$e, $n);
                            }
                        } else {
                            $n = is_string($toNameList) ? $toNameList : '';
                            $this->addAddress((string)$value, $n);
                        }
                    } elseif ($key === 'from') {
                        $fromName = isset($this->mail['fromName']) ? (string)$this->mail['fromName'] : '';
                        $this->setFrom((string)$value, $fromName);
                    } elseif ($key === 'replyTo') {
                        $replyToName = isset($this->mail['replyToName']) ? (string)$this->mail['replyToName'] : '';
                        $this->addReplyTo((string)$value, $replyToName);
                    } elseif ($key === 'subject') {
                        $this->addSubject((string)$value);
                    } elseif ($key === 'bodyHTML') {
                        $this->options['Body'] = (string)$value;
                    } elseif ($key === 'body') {
                        $this->AltBody((string)$value);
                    } elseif ($key === 'attachments' && is_array($value)) {
                        foreach ($value as $filename => $file) {
                            $this->addAttachment((string)$file, (string)$filename);
                        }
                    }
                }
            }

            $instance = $this->setUserSettings($instance);
            $result = $instance->send();

            if ($result) {
                wireLog('WireMailPHPMailer', $this->_('Message has been sent.'));
                $count = is_array($this->options['addAddress']) ? count($this->options['addAddress']) : 0;
                return $count > 0 ? $count : 1;
            }

            wireLog('WireMailPHPMailer', $this->_('Message could not be sent. Mailer Error:') . ' ' . $instance->ErrorInfo);
            return 0;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            if (isset($instance) && $instance->ErrorInfo) {
                $error = $instance->ErrorInfo;
            }
            wireLog('WireMailPHPMailer', $this->_('Message could not be sent. Mailer Error:') . ' ' . $error);
            return 0;
        }
    }
}
