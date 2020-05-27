<?php namespace ProcessWire;

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require __DIR__ . DIRECTORY_SEPARATOR . "vendor/autoload.php";

/**
 * Class WireMailPHPMailer
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 * @projectWebsite	: https://github.com/trk/WireMailPHPMailer
 */
class WireMailPHPMailer extends WireMail implements Module, ConfigurableModule {

    /**
     * PHPMailer Version
     */
    const PHPMailer_VERSION = "6.1.5";

    /**
     * PHPMailer instance
     *
     * @var PHPMailer
     */
    protected $PHPMailer;

    /**
     * Should we throw external exceptions?
     *
     * @var null
     */
    protected $exceptions = null;

    // ------------------------------------------------------------------------

    /**
     * Module info
     *
     * @see Module
     * @return array
     */
    public static function getModuleInfo() {
        return array(
            'title' => 'WireMailPHPMailer',
            'version' => 121,
            'summary' => __('This module extends WireMail base class, integrating the PHPMailer mailing library into ProcessWire.'),
            'href' => 'https://github.com/trk/WireMailPHPMailer',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => array(
                'ProcessWire>=3.0.0'
            ),
            'installs' => array(),
            // 'permanent' => false
            // 'permission' => 'permission-name',
            // 'permissions' => array()
            'icon' => 'envelope-o',
            'singular' => true,
            'autoload' => false
        );
    }

    // ------------------------------------------------------------------------

    /**
     * Populate the default config data
     *
     * WireMailPHPMailer constructor.
     */
    public function __construct() {}

    // ------------------------------------------------------------------------

    /**
     * Initialize the module
     */
    public function init() {
        $this->PHPMailer = new PHPMailer($this->exceptions);
        foreach($this->getArray() as $key => $value) {
            $this->PHPMailer->{$key} = $value;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Return PHPMailer instance
     *
     * @return PHPMailer
     */
    public function mailer() { return $this->PHPMailer; }

    // ------------------------------------------------------------------------

    /**
     * The plain-text message body.
     * This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora.
     * Clients that can read HTML will view the normal Body.
     *
     * @param string $AltBody
     * @return $this
     */
    public function AltBody($AltBody="") {
        if($AltBody) $this->PHPMailer->AltBody = $AltBody;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * The Subject of the message.
     *
     * @var string
     * @return $this
     */
    public function addSubject($Subject="") {
        if($Subject) $this->PHPMailer->Subject = $Subject;
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
    public function addAddress($address, $name = '') {
        $this->PHPMailer->addAddress($address, $name);
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
    public function addCC($address, $name = '') {
        $this->PHPMailer->addCC($address, $name);
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
    public function addBCC($address, $name = '') {
        $this->PHPMailer->addBCC($address, $name);
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
    public function addReplyTo($address, $name = '') {
        $this->PHPMailer->addReplyTo($address, $name);
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
    public function parseAddresses($addrstr, $useimap = true) {
        return $this->PHPMailer::parseAddresses($addrstr, $useimap);
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
    public function setFrom($address, $name = '', $auto = true) {
        $this->PHPMailer->setFrom($address, $name, $auto);
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
    public function getLastMessageID() {
        return $this->PHPMailer->getLastMessageID();
    }

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
    public function msgHTML($message, $basedir = '', $advanced = false) {
        $this->PHPMailer->msgHTML($message, $basedir, $advanced);
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
    public function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment') {
        $this->PHPMailer->addAttachment($path, $name, $encoding, $type, $disposition);
        return $this;
    }

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
    public function attachment($value, $filename = '') {
        if($value === null) {
            // clear attachments
            $this->PHPMailer->clearAttachments();
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
    public function ___send() {
        if($this->mail["from"]) {
            $this->PHPMailer->From = $this->mail["from"];
            $this->PHPMailer->FromName = $this->mail["fromName"];
        }
        if($this->mail["subject"]) $this->PHPMailer->Subject = $this->mail["subject"];
        foreach($this->to as $to) {
            $toName = $this->mail['toName'][$to];
            $this->PHPMailer->addAddress($to, $toName);
        }
        if(count($this->attachments)) {
            foreach($this->attachments as $filename => $file) {
                $this->PHPMailer->addAttachment($file, $filename);
            }
        }
        if($this->mail["replyTo"]) {
            $this->PHPMailer->addReplyTo($this->mail["replyTo"], $this->mail["replyToName"]);
        }
        if($this->bodyHTML) {
            $this->PHPMailer->isHTML(true);
            $this->PHPMailer->Body = $this->bodyHTML;
        }
        if($this->body) {
            $this->PHPMailer->AltBody = $this->body;
        }
        return $this->PHPMailer->send() ? 1 : 0;
    }
}