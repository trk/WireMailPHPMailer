<?php

declare(strict_types=1);

namespace ProcessWire;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
     * @var PHPMailer|null
     */
    protected ?PHPMailer $instance = null;

    /**
     * @var array
     */
    protected array $options = [];

    protected const COMPATIBILITY = [
        'from' => 'From',
        'fromName' => 'FromName',
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
            'version' => 143,
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
            return $this->{self::COMPATIBILITY[$key]};
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
     * Apply module settings
     *
     * @param PHPMailer $instance
     * 
     * @return PHPMailer
     */
    protected function setModuleSettings(PHPMailer $instance): PHPMailer
    {
        $data = $this->getArray();

        if (isset($data['Mailer']) && $data['Mailer'] === 'smtp') {
            $instance->isSMTP();
            
            if (isset($data['AuthType']) && $data['AuthType'] === 'XOAUTH2') {
            $providerName = $data['OAuthProvider'] ?? '';
            $clientId = $data['OAuthClientId'] ?? '';
            $clientSecret = $data['OAuthClientSecret'] ?? '';
            $tenantId = $data['OAuthTenantId'] ?? 'common';
            $refreshToken = $data['OAuthRefreshToken'] ?? '';
            $email = $data['OAuthEmail'] ?? '';

            $providerClass = null;
            $providerObj = null;

            if ($providerName === 'google') {
                $providerClass = '\\League\\OAuth2\\Client\\Provider\\Google';
                if (class_exists($providerClass)) {
                    $providerObj = new $providerClass([
                        'clientId'     => $clientId,
                        'clientSecret' => $clientSecret,
                    ]);
                }
            } elseif ($providerName === 'yahoo') {
                $providerClass = '\\Hayageek\\OAuth2\\Client\\Provider\\Yahoo';
                if (class_exists($providerClass)) {
                    $providerObj = new $providerClass([
                        'clientId'     => $clientId,
                        'clientSecret' => $clientSecret,
                    ]);
                }
            } elseif ($providerName === 'microsoft') {
                $providerClass = '\\Stevenmaguire\\OAuth2\\Client\\Provider\\Microsoft';
                if (class_exists($providerClass)) {
                    $providerObj = new $providerClass([
                        'clientId'     => $clientId,
                        'clientSecret' => $clientSecret,
                    ]);
                }
            } elseif ($providerName === 'azure') {
                $providerClass = '\\Greew\\OAuth2\\Client\\Provider\\Azure';
                if (class_exists($providerClass)) {
                    $providerObj = new $providerClass([
                        'clientId'               => $clientId,
                        'clientSecret'           => $clientSecret,
                        'tenant'                 => $tenantId ?: 'common',
                        'defaultEndPointVersion' => '2.0',
                    ]);
                }
            }

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
            } elseif ($providerObj === null) {
                wireLog('WireMailPHPMailer', "OAuth provider library for '{$providerName}' not found. Please install it via Composer. XOAUTH2 may fail.");
            }
        }
        }

        // set module configs
        foreach ($data as $key => $value) {
            $instance->set((string)$key, $value);
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
        return PHPMailer::parseAddresses($addrstr);
    }

    // ------------------------------------------------------------------------

    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool $auto
     * @return $this
     * @throws Exception
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
     * @throws Exception
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
        if ($value !== null) {
            $this->addAttachment((string)$value, (string)$filename);
        }

        return parent::attachment($value, $filename);
    }

    // ------------------------------------------------------------------------

    /**
     * Send the email
     *
     * @return int Returns the number of successfully sent messages
     * @throws Exception
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

            if (!empty($this->bodyHTML)) {
                $this->options['Body'] = $this->bodyHTML;
            }

            if (!empty($this->body)) {
                $this->AltBody($this->body);
            }

            if (is_array($this->mail)) {
                foreach ($this->mail as $key => $value) {
                    if ($key === 'to') {
                        if (is_array($value)) {
                            foreach ($value as $i => $e) {
                                $toNameList = $this->mail['toName'] ?? [];
                                $n = (is_array($toNameList) && isset($toNameList[$i])) ? $toNameList[$i] : (is_string($toNameList) ? $toNameList : '');
                                $this->addAddress((string)$e, (string)$n);
                            }
                        } else {
                            $toNameList = $this->mail['toName'] ?? '';
                            $n = is_string($toNameList) ? $toNameList : '';
                            $this->addAddress((string)$value, $n);
                        }
                    }

                    if ($key === 'from') {
                        $fromName = isset($this->mail['fromName']) ? (string)$this->mail['fromName'] : '';
                        $this->setFrom((string)$value, $fromName);
                    }

                    if ($key === 'replyTo') {
                        $replyToName = isset($this->mail['replyToName']) ? (string)$this->mail['replyToName'] : '';
                        $this->addReplyTo((string)$value, $replyToName);
                    }

                    if ($key === 'subject') {
                        $this->addSubject((string)($this->mail['subject'] ?? ''));
                    }

                    if ($key === 'bodyHTML') {
                        $this->options['Body'] = (string)$value;
                    }

                    if ($key === 'body') {
                        $this->AltBody((string)$value);
                    }

                    if ($key === 'attachments' && is_array($value)) {
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
                $count = is_array($this->options['addAddress'] ?? null) ? count($this->options['addAddress']) : 1;
                return $count > 0 ? $count : 1;
            }

            return 0;
        } catch (Exception $e) {
            wireLog('WireMailPHPMailer', $this->_('Message could not be sent. Mailer Error:') . ' ' . $instance->ErrorInfo);
            return 0;
        }
    }
}
