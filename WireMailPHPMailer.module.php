<?php

namespace ProcessWire;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/vendor/autoload.php";

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
     * Module Version
     */
    const VERSION = "1.4.3";

    /**
     * @var PHPMailer
     */
    protected $instance;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Module info
     *
     * @see Module
     * @return array
     */
    public static function getModuleInfo()
    {
        return array(
            'title' => 'WireMailPHPMailer',
            'version' => self::VERSION,
            'summary' => __('This module extends WireMail base class, integrating the PHPMailer mailing library into ProcessWire.'),
            'href' => 'https://github.com/trk/WireMailPHPMailer',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => array(
                'ProcessWire>=3.0.0'
            ),
            'installs' => array(),
            'icon' => 'envelope-o',
            'singular' => false,
            'autoload' => false
        );
    }

    public function __get($key)
    {
        $wireMailCompatibility = [
            'from' => 'From',
            'fromName' => 'FromName',
            'subject' => 'Subject',
            'body' => 'AltBody',
            'bodyHTML' => 'Body'
        ];

        if (isset($wireMailCompatibility[$key])) {
            return $this->{$wireMailCompatibility[$key]};
        }
        return parent::__get($key);
    }

    /**
     * Initialize the module
     */
    public function init()
    {
        $this->options = [
            'AltBody'   => '',
            'Subject'   => '',
            'setFrom'   => [
                'address' => '',
                'name' => '',
                'auto' => ''
            ],
            'addAddress' => [],
            'addCC' => [],
            'addBCC' => [],
            'addReplyTo' => [],
            'msgHTML' => [
                'message' => '',
                'basedir' => '',
                'advanced' => ''
            ]
        ];
    }

    /**
     * Return PHPMailer instance
     *
     * @return PHPMailer
     */
    public function mailer()
    {
        return $this->getInstance();
    }

    /**
     * Return PHPMailer instance
     *
     * @return PHPMailer
     */
    public function getInstance($initialize = true, $exceptions = false)
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
     * @param PHPMailer|null $instance
     * 
     * @return PHPMailer|null
     */
    protected function setModuleSettings($instance = null)
    {
        if ($instance instanceof PHPMailer) {
            $data = $this->getArray();

            if (isset($data['Mailer']) && $data['Mailer'] == 'smtp') {
                $instance->isSMTP();
            }

            // set module configs
            foreach ($data as $key => $value) {
                $instance->set($key, $value);
            }
        }

        return $instance;
    }

    // ------------------------------------------------------------------------

    /**
     * Apply user settings
     *
     * @param PHPMailer|null $instance
     * 
     * @return PHPMailer|null
     */
    protected function setUserSettings($instance = null)
    {
        if ($instance instanceof PHPMailer) {
            // set user configs
            foreach ($this->options as $name => $value) {
                if (in_array($name, ['AltBody', 'Subject', 'Body']) && is_string($value) && $value) {
                    if ($name == 'Body') {
                        $instance->isHTML();
                    }
                    $instance->{$name} = $value;
                } else if (in_array($name, ['addAddress', 'addCC', 'addBCC', 'addReplyTo']) && is_array($value) && count($value)) {
                    foreach ($value as $e => $n) {
                        $instance->{$name}($e, $n);
                    }
                } else if ($name == 'setFrom' && $value['address']) {
                    $instance->setFrom($value['address'], $value['name'], $value['auto']);
                } else if ($name == 'msgHTML' && $value['message']) {
                    $instance->msgHTML($value['message'], $value['basedir'], $value['advanced']);
                } else if ($name == 'addAttachment' && is_array($value) && count($value)) {
                    foreach ($value as $p => $v) {
                        $instance->addAttachment($p, $v['name'], $v['encoding'], $v['type'], $v['disposition']);
                    }
                }
            }
        }

        return $instance;
    }

    // ------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    // public function Body($Body)
    // {
    //     if (is_string($Body)) {
    //         $this->options['Body'] = $Body;
    //     }

    //     return $this;
    // }

    // ------------------------------------------------------------------------

    /**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     *
     * @param string $AltBody
     * @return $this
     */
    public function AltBody($AltBody = "")
    {
        if ($AltBody) {
            $this->options['AltBody'] = $AltBody;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * The Subject of the message.
     *
     * @var string
     * @return $this
     */
    public function addSubject($Subject = "")
    {
        if ($Subject) {
            $this->options['Subject'] = $Subject;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "To" address.
     *
     * @param $address
     * @param string $name
     * @return $this
     */
    public function addAddress($address, $name = '')
    {
        if (is_string($address)) {
            $this->options['addAddress'][$address] = $name;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "CC" address.
     *
     * @param $address
     * @param string $name
     * @return $this
     */
    public function addCC($address, $name = '')
    {
        if (is_string($address)) {
            $this->options['addCC'][$address] = $name;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "BCC" address.
     *
     * @param $address
     * @param string $name
     * @return $this
     */
    public function addBCC($address, $name = '')
    {
        if (is_string($address)) {
            $this->options['addBCC'][$address] = $name;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a "Reply-To" address.
     *
     * @param $address
     * @param string $name
     * @return $this
     */
    public function addReplyTo($address, $name = '')
    {
        if (is_string($address)) {
            $this->options['addReplyTo'][$address] = $name;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Parse and validate a string containing one or more RFC822-style comma-separated email addresses
     * of the form "display name <address>" into an array of name/address pairs.
     * Uses the imap_rfc822_parse_adrlist function if the IMAP extension is available.
     * Note that quotes in the name part are removed.
     *
     * @param string $addrstr The address list string
     * @param bool $useimap Whether to use the IMAP extension to parse the list
     * @return array
     * @link http://www.andrew.cmu.edu/user/agreen1/testing/mrbs/web/Mail/RFC822.php A more careful implementation
     */
    public function parseAddresses($addrstr, $useimap = true)
    {
        return PHPMailer::parseAddresses($addrstr, $useimap);
    }

    // ------------------------------------------------------------------------

    /**
     * Set the From and FromName properties.
     *
     * @param $address
     * @param string $name
     * @param bool $auto
     * @return $this
     * @throws Exception
     */
    public function setFrom($address, $name = '', $auto = true)
    {
        if (is_string($address)) {
            $this->options['setFrom'] = [
                'address' => $address,
                'name' => $name,
                'auto' => $auto
            ];
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Return the Message-ID header of the last email.
     * Technically this is the value from the last time the headers were created,
     * but it's also the message ID of the last sent message except in
     * pathological cases.
     *
     * @return string
     */
    // public function getLastMessageID()
    // {
    //     return $this->PHPMailer->getLastMessageID();
    // }

    // ------------------------------------------------------------------------

    /**
     * Create a message from an HTML string.
     * Automatically makes modifications for inline images and backgrounds
     * and creates a plain-text version by converting the HTML.
     * Overwrites any existing values in $this->Body and $this->AltBody
     *
     * @access public
     * @param string $message HTML message string
     * @param string $basedir baseline directory for path
     * @param boolean|callable $advanced Whether to use the internal HTML to text converter
     *    or your own custom converter @see PHPMailer::html2text()
     * @return string $message
     */
    public function msgHTML($message, $basedir = '', $advanced = false)
    {
        if (is_string($message)) {
            $this->options['msgHTML'] = [
                'message' => $message,
                'basedir' => $basedir,
                'advanced' => $advanced
            ];
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add an attachment from a path on the filesystem.
     * Returns false if the file could not be found or read.
     *
     * @param $path
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @param string $disposition
     * @return $this
     * @throws Exception
     */
    public function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        if (is_string($path)) {
            $this->options['addAttachment'][$path] = [
                'name' => $name,
                'encoding' => $encoding,
                'type' => $type,
                'disposition' => $disposition
            ];
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Add a file to be attached to the email
     * 
     * Multiple calls will append attachments. 
     * To remove the supplied attachments, specify NULL as the value. 
     *
     * @param string $value Full path and filename of file attachment
     * @param string $filename Optional different basename for file as it appears in the mail
     * @return $this 
     *
     */
    public function attachment($value, $filename = '')
    {
        if ($value === null) {
            // clear attachments
            // $this->PHPMailer->clearAttachments();
        } else {
            // add attachment
            $this->addAttachment($value, $filename);
        }

        return parent::attachment($value, $filename);
    }

    // ------------------------------------------------------------------------

    /**
     * Send the email
     *
     * @return int|void
     * @throws Exception
     */
    public function ___send()
    {
        $instance = $this->getInstance();

        try {

            if (count($this->attachments)) {
                foreach ($this->attachments as $filename => $file) {
                    $this->addAttachment($file, $filename);
                }
            }

            if ($this->bodyHTML) {
                $this->options['Body'] = $this->bodyHTML;
            }

            if ($this->body) {
                $this->AltBody($this->body);
            }

            foreach ($this->mail as $key => $value) {

                if ($key == 'to') {
                    if (is_array($value)) {
                        foreach ($value as $i => $e) {
                            $n = is_array($this->mail['toName']) && $this->mail['toName'][$i] ? $this->mail['toName'][$i] : '';
                            $this->addAddress($e, $n);
                        }
                    } else {
                        $n = is_string($this->mail['toName']) ? $this->mail['toName'] : '';
                        $this->addAddress($value, $n);
                    }
                }

                if ($key == 'from') {
                    $this->setFrom($value, $this->mail['fromName']);
                }

                if ($key == 'replyTo') {
                    $this->addReplyTo($value, $this->mail['replyToName']);
                }

                if ($key == 'subject') {
                    $this->addSubject($this->mail['subject']);
                }

                if ($key == 'bodyHTML') {
                    $this->options['Body'] = $value;
                }

                if ($key == 'body') {
                    $this->AltBody($value);
                }

                if ($key == 'attachments') {
                    foreach ($value as $filename => $file) {
                        $this->addAttachment($file, $filename);
                    }
                }
            }

            if (count($this->attachments)) {
                foreach ($this->attachments as $filename => $file) {
                    $this->addAttachment($file, $filename);
                }
            }

            if ($this->bodyHTML) {
                // $this->msgHTML($this->bodyHTML);
                $this->options['Body'] = $this->bodyHTML;
            }

            if ($this->body) {
                $this->AltBody($this->body);
            }

            $instance = $this->setUserSettings($instance);
            $instance->send();

            wireLog('WireMailPHPMailer', $this->_('Message has been sent.'));
            return true;
        } catch (Exception $e) {
            wireLog('WireMailPHPMailer', $this->_('Message could not be sent. Mailer Error:') . $instance->ErrorInfo);
            return false;
        }
    }
}
