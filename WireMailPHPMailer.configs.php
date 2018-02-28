<?php namespace ProcessWire;
return array(
    "Priority" => array(
        "type" => "InputfieldSelect",
        "label" => __("Priority"),
        "description" => __("When null, the header is not set at all."),
        "required" => true,
        "default" => "null",
        "options" => array(
            "null" => __("Default"),
            1 => __("High"),
            3 => __("Normal"),
            5 => __("Low")
        ),
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 25
    ),
    "CharSet" => array(
        "type" => "InputfieldText",
        "label" => __("Character set"),
        "description" => __("The character set of the message."),
        "default" => "utf-8",
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 25
    ),
    "ContentType" => array(
        "type" => "InputfieldSelect",
        "label" => __("Content-type"),
        "description" => __("The MIME Content-type of the message."),
        "required" => true,
        "default" => "text/html",
        "options" => array(
            "text/plain" => "text/plain",
            "text/html" => "text/html",
            "multipart/alternative" => "multipart/alternative"
        ),
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 25
    ),
    "Encoding" => array(
        "type" => "InputfieldSelect",
        "label" => __("Encoding"),
        "description" => __("The message encoding."),
        "required" => true,
        "default" => "8bit",
        "options" => array(
            "8bit" => "8bit",
            "7bit" => "7bit",
            "binary" => "binary",
            "base64" => "base64",
            "quoted-printable" => "quoted-printable"
        ),
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 25
    ),
    "ErrorInfo" => array(
        "type" => "InputfieldText",
        "label" => __("Error Info"),
        "description" => __("Holds the most recent mailer error message."),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 100
    ),
    "Sender" => array(
        "type" => "InputfieldText",
        "label" => __("Sender"),
        "description" => __("The envelope sender of the message. This will usually be turned into a Return-Path header by the receiver, and is the address that bounces will be sent to."),
        "notes" => __("If not empty, will be passed via `-f` to sendmail or as the `MAIL FROM` value over SMTP."),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "FromName" => array(
        "type" => "InputfieldText",
        "label" => __("From Name"),
        "description" => __("The From name of the message."),
        "default" => "Root User",
        "attrs" => array(
            "placeholder" => __("Site administrator")
        ),
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 50
    ),
    "From" => array(
        "type" => "InputfieldText",
        "label" => __("From"),
        "description" => __("The From email address for the message."),
        "default" => "root@localhost",
        "attrs" => array(
            "placeholder" => "email@domain.ltd"
        ),
        "collapsed" => Inputfield::collapsedNever,
        "columnWidth" => 50
    ),
    "Subject" => array(
        "type" => "InputfieldText",
        "label" => __("Subject"),
        "description" => __("The Subject of the message."),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "Body" => array(
        "type" => "InputfieldTextarea",
        "label" => __("Body"),
        "description" => __("An HTML or plain text message body."),
        "notes" => __("If HTML then call `isHTML(true)`"),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "AltBody" => array(
        "type" => "InputfieldTextarea",
        "label" => __("Alt Body"),
        "description" => __("The plain-text message body. This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora."),
        "notes" => __("Clients that can read `HTML` will view the normal `Body`."),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "Ical" => array(
        "type" => "InputfieldText",
        "label" => __("iCal"),
        "description" => __("An iCal message part body."),
        "notes" => __("Only supported in simple alt or alt_inline message types To generate iCal event structures, use classes like EasyPeasyICS or iCalcreator. [see](http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/), [see](http://kigkonsult.se/iCalcreator/)"),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "WordWrap" => array(
        "type" => "InputfieldInteger",
        "label" => __("WordWrap"),
        "description" => __("Word-wrap the message body to this number of chars."),
        "notes" => __("Set to 0 to not wrap. A useful value here is 78, for [RFC2822](https://www.ietf.org/rfc/rfc2822.txt) section 2.1.1 compliance."),
        "default" => "0",
        "set" => array(
            "inputType" => "number",
            "min" => 0
        ),
        "collapsed" => Inputfield::collapsedNever
    ),
    "Mailer" => array(
        "type" => "InputfieldSelect",
        "label" => __("Mailer"),
        "description" => __("Which method to use to send mail."),
        "required" => true,
        "default" => "mail",
        "options" => array(
            "mail" => "mail",
            "sendmail" => "sendmail",
            "smtp" => "smtp"
        ),
        "collapsed" => Inputfield::collapsedNever
    ),
    "SendmailSettings" => array(
        "type" => "InputfieldFieldset",
        "label" => __("Sendmail Settings"),
        "showIf" => array(
            "Mailer" => "=sendmail"
        ),
        "fields" => array(
            "Sendmail" => array(
                "type" => "InputfieldText",
                "label" => __("Sendmail"),
                "description" => __("The path to the sendmail program."),
                "default" => "/usr/sbin/sendmail",
                "collapsed" => Inputfield::collapsedNever
            ),
            "UseSendmailOptions" => array(
                "type" => "InputfieldCheckbox",
                "label" => __("Use Sendmail Options"),
                "description" => __("Whether `mail()` uses a fully `sendmail-compatible` MTA."),
                "notes" => __("One which supports sendmail's `-oi -f` options."),
                "default" => true,
                "collapsed" => Inputfield::collapsedNever
            )
        )
    ),
    "ConfirmReadingTo" => array(
        "type" => "InputfieldCheckbox",
        "label" => __("Confirm Reading To"),
        "description" => __("The email address that a reading confirmation should be sent to, also known as read receipt."),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "Hostname" => array(
        "type" => "InputfieldText",
        "label" => __("Hostname"),
        "description" => __("The hostname to use in the `Message-ID` header and as default `HELO` string."),
        "notes" => __('If empty, PHPMailer attempts to find one with, in order, `$_SERVER["SERVER_NAME"]`, `gethostname()`, `php_uname("n")`, or the value `localhost.localdomain`'),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "MessageID" => array(
        "type" => "InputfieldText",
        "label" => __("Message ID"),
        "description" => __("An ID to be used in the `Message-ID` header."),
        "notes" => __("If empty, a unique id will be generated. You can set your own, but it must be in the format `<id@domain>`, as defined in RFC5322 section 3.6.4 or it will be ignored. [see](https://tools.ietf.org/html/rfc5322#section-3.6.4)"),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "MessageDate" => array(
        "type" => "InputfieldText",
        "label" => __("Message Date"),
        "description" => __("The message Date to be used in the Date header."),
        "notes" => __('If empty, the current date will be added.'),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "SMTP" => array(
        "type" => "InputfieldFieldset",
        "label" => __("SMTP Settings"),
        "showIf" => array(
            "Mailer" => "=smtp"
        ),
        "fields" => array(
            "Host" => array(
                "type" => "InputfieldText",
                "label" => __("Host"),
                "description" => __("SMTP hosts."),
                "notes" => __('Either a single hostname or multiple semicolon-delimited hostnames.
                                You can also specify a different port for each host by using this format: [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com").
                                You can also specify encryption type, for example: (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465"). Hosts will be tried in order.'),
                "default" => "localhost",
                "columnWidth" => 50,
                "collapsed" => Inputfield::collapsedNever
            ),
            "Helo" => array(
                "type" => "InputfieldText",
                "label" => __("Helo"),
                "description" => __("The SMTP HELO of the message."),
                "notes" => __('Default is `$Hostname`. If `$Hostname` is empty, PHPMailer attempts to find one with the same method described above for `$Hostname`.'),
                "default" => "",
                "columnWidth" => 50,
                "collapsed" => Inputfield::collapsedNever
            ),
            "SMTPSecure" => array(
                "type" => "InputfieldSelect",
                "label" => __("SMTP Secure"),
                "description" => __("What kind of encryption to use on the SMTP connection."),
                "required" => true,
                "default" => "tls",
                "options" => array(
                    "" => __("Default"),
                    "ssl" => __("SSL"),
                    "tls" => __("TLS")
                ),
                "columnWidth" => 50,
                "collapsed" => Inputfield::collapsedNever
            ),
            "Port" => array(
                "type" => "InputfieldInteger",
                "label" => __("Port"),
                "description" => __("The default SMTP server port."),
                "set" => array(
                    "inputType" => "number",
                    "min" => 0
                ),
                "default" => 587,
                "columnWidth" => 50,
                "collapsed" => Inputfield::collapsedNever
            ),
            "SMTPAutoTLS" => array(
                "type" => "InputfieldCheckbox",
                "label" => __("SMTP Auto TLS"),
                "description" => __("The email address that a reading confirmation should be sent to, also known as read receipt."),
                "default" => true,
                "collapsed" => Inputfield::collapsedNever
            ),
            "SMTPAuth" => array(
                "type" => "InputfieldCheckbox",
                "label" => __("SMTP Auth"),
                "description" => __("Whether to use SMTP authentication."),
                "notes" => __("Uses the Username and Password properties."),
                "default" => false,
                "collapsed" => Inputfield::collapsedNever
            ),
            "Username" => array(
                "type" => "InputfieldText",
                "label" => __("Username"),
                "description" => __("SMTP username."),
                "default" => "",
                "columnWidth" => 50,
                "collapsed" => Inputfield::collapsedNever
            ),
            "Password" => array(
                "type" => "InputfieldText",
                "label" => __("Password"),
                "description" => __("SMTP password."),
                "set" => array(
                    "type" => "password"
                ),
                "default" => "",
                "columnWidth" => 50,
                "collapsed" => Inputfield::collapsedNever
            ),
            "AuthType" => array(
                "type" => "InputfieldSelect",
                "label" => __("Auth Type"),
                "description" => __("SMTP auth type."),
                "notes" => __("Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified."),
                "required" => true,
                "default" => "",
                "options" => array(
                    "" => __("Default"),
                    "CRAM-MD5" => "CRAM-MD5",
                    "LOGIN" => "LOGIN",
                    "PLAIN" => "PLAIN",
                    "XOAUTH2" => "XOAUTH2"
                ),
                "collapsed" => Inputfield::collapsedNever
            ),
            "Timeout" => array(
                "type" => "InputfieldInteger",
                "label" => __("Timeout"),
                "description" => __("The SMTP server timeout in seconds."),
                "notes" => __("Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2."),
                "set" => array(
                    "inputType" => "number",
                    "min" => 0
                ),
                "default" => 300,
                "collapsed" => Inputfield::collapsedNever
            ),
            "SMTPDebug" => array(
                "type" => "InputfieldSelect",
                "label" => __("SMTP Debug"),
                "description" => __("SMTP class debug output mode."),
                "notes" => __("Debug output level."),
                "required" => true,
                "default" => "0",
                "options" => array(
                    "0" => __("No output"),
                    "1" => __("Commands"),
                    "2" => __("Data and commands"),
                    "3" => __("As 2 plus connection status"),
                    "4" => __("Low-level data output.")
                ),
                "collapsed" => Inputfield::collapsedNever
            ),
            "Debugoutput" => array(
                "type" => "InputfieldSelect",
                "label" => __("Debug output"),
                "description" => __("How to handle debug output."),
                "required" => true,
                "default" => "html",
                "options" => array(
                    "echo" => __("Output plain-text as-is, appropriate for CLI"),
                    "html" => __("Output escaped, line breaks converted to `<br>`, appropriate for browser output"),
                    "error_log" => __("Output to error log as configured in php.ini")
                ),
                "collapsed" => Inputfield::collapsedNever
            ),
            "SMTPKeepAlive" => array(
                "type" => "InputfieldCheckbox",
                "label" => __("SMTP Keep Alive"),
                "description" => __("Whether to keep SMTP connection open after each message."),
                "notes" => __("If this is set to true then to close the connection requires an explicit call to `smtpClose()`."),
                "default" => false,
                "collapsed" => Inputfield::collapsedNever
            )
        )
    ),
    "SingleTo" => array(
        "type" => "InputfieldCheckbox",
        "label" => __("Single To"),
        "description" => __("Whether to split multiple to addresses into multiple messages or send them all in one message."),
        "notes" => __("Only supported in `mail` and `sendmail` transports, not in SMTP."),
        "default" => false,
        "collapsed" => Inputfield::collapsedNever
    ),
    "do_verp" => array(
        "type" => "InputfieldCheckbox",
        "label" => __("Generate VERP addresses"),
        "description" => __("Whether to generate VERP addresses on send."),
        "notes" => __("Only applicable when sending via SMTP. [see](https://en.wikipedia.org/wiki/Variable_envelope_return_path), [see](http://www.postfix.org/VERP_README.html)"),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
    "AllowEmpty" => array(
        "type" => "InputfieldCheckbox",
        "label" => __("Allow Empty"),
        "description" => __("Whether to allow sending messages with an empty body."),
        "default" => false,
        "collapsed" => Inputfield::collapsedNever
    ),
    "DKIM" => array(
        "type" => "InputfieldFieldset",
        "label" => __("DKIM Settings"),
        "fields" => array(
            "DKIM_selector" => array(
                "type" => "InputfieldText",
                "label" => __("DKIM selector"),
                "default" => "",
                "collapsed" => Inputfield::collapsedNever,
                "columnWidth" => 50
            ),
            "DKIM_identity" => array(
                "type" => "InputfieldText",
                "label" => __("DKIM identity"),
                "description" => __("Usually the email address used as the source of the email."),
                "default" => "",
                "collapsed" => Inputfield::collapsedNever,
                "columnWidth" => 50
            ),
            "DKIM_passphrase" => array(
                "type" => "InputfieldText",
                "label" => __("DKIM passphrase"),
                "description" => __("Used if your key is encrypted."),
                "default" => "",
                "collapsed" => Inputfield::collapsedNever,
                "columnWidth" => 50
            ),
            "DKIM_domain" => array(
                "type" => "InputfieldText",
                "label" => __("DKIM domain"),
                "description" => __("DKIM signing domain name."),
                "notes" => __("example: `example.com`"),
                "default" => "",
                "collapsed" => Inputfield::collapsedNever,
                "columnWidth" => 50
            ),
            "DKIM_private" => array(
                "type" => "InputfieldText",
                "label" => __("DKIM private"),
                "description" => __("DKIM private key file path."),
                "default" => "",
                "collapsed" => Inputfield::collapsedNever,
                "columnWidth" => 50
            ),
            "DKIM_private_string" => array(
                "type" => "InputfieldText",
                "label" => __("DKIM private string"),
                "description" => __('If set, takes precedence over `$DKIM_private`.'),
                "default" => "",
                "collapsed" => Inputfield::collapsedNever,
                "columnWidth" => 50
            )
        )
    ),
    "XMailer" => array(
        "type" => "InputfieldText",
        "label" => __("XMailer"),
        "description" => __("What to put in the X-Mailer header."),
        "notes" => __("Options: An empty string for PHPMailer default, whitespace for none, or a string to use."),
        "default" => "",
        "collapsed" => Inputfield::collapsedNever
    ),
);