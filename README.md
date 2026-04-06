# WireMailPHPMailer

A robust, modern integration of the popular [PHPMailer](https://github.com/PHPMailer/PHPMailer) library for the ProcessWire CMS/CMF. This module transparently extends the core `WireMail` class, allowing you to use advanced mailing features via familiar API calls, or access the native `PHPMailer` instance directly for fine-grained control.

## Requirements

- **ProcessWire:** `3.0.0` or newer
- **PHP:** `8.0` or newer (Strict Types enforced)

## Installation

1. Copy the module files into your `site/modules/WireMailPHPMailer/` directory.
2. In the ProcessWire admin, go to **Modules \> Refresh**.
3. Locate **WireMailPHPMailer** under the *WireMail* section and click **Install**.
4. Configure your SMTP, Sendmail, or PHP Mail settings directly via the module's configuration screen.

*(Note: The module comes pre-packaged with the PHPMailer library in the `vendor` directory. You do not need to run Composer manually unless you are doing custom developmental modifications.)*

## Usage

You can use the module through the standard ProcessWire `WireMail` API or access the underlying `PHPMailer` instance directly.

### 1. The Classic WireMail Way (Recommended)

This approach seamlessly integrates into existing ProcessWire core API workflows.

```php
$mail = wire('modules')->get('WireMailPHPMailer');

$mail->from('from@example.com')
     ->fromName('Sender Name')
     ->to('to@example.com')
     ->subject('Your Subject Here')
     ->body('This is the plain-text message body.')
     ->bodyHTML('<h1>This is the HTML message body</h1>')
     ->send();
```

### 2. The Native PHPMailer Way

If you need advanced configuration, attachments, or specific PHPMailer features, you can interact with the instance directly.

```php
/** @var WireMailPHPMailer $wireMail */
$wireMail = wire('modules')->get('WireMailPHPMailer');

// Retrieve the initialized PHPMailer instance 
// (passing `false` skips loading the module configurations if needed)
$mail = $wireMail->getInstance();

$mail->addAddress('email@domain.ltd', 'Recipient Name');
$mail->isHTML(true);
$mail->Subject = 'Advanced PHPMailer Usage';
$mail->Body    = '<h1>Hello World</h1><p>ProcessWire + PHPMailer</p>';
$mail->AltBody = 'Hello World - ProcessWire + PHPMailer';

if ($mail->send()) {
    echo "Message has been sent.";
} else {
    echo "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
}
```

## Features & Configuration

The module provides an extensive settings page in the ProcessWire admin (`Modules > Configure > WireMailPHPMailer`) where you can define:

- **Transport method:** SMTP, Sendmail, or native `mail()`
- **SMTP credentials:** Host, Port, Username, Password, AutoTLS (enabled by default)
- **Encryption:** SSL / TLS
- **Authentication Types:** LOGIN, PLAIN, CRAM-MD5, and native **XOAUTH2** support.
- **DKIM Signing:** Domain, Identity, Selector, Path to Private Key
- **Global Defaults:** Default Sender (`From`/`FromName`), Reply-To, and Bcc tracking.

## XOAUTH2 (Google, Microsoft, Yahoo, Azure) Support

WireMailPHPMailer supports natively injecting XOAUTH2 authentication directly into ProcessWire's unified `WireMail` flow via the underlying PHPMailer engine.

Because OAuth2 providers are heavy, this module allows you to selectively install the right provider package to the root workspace using Composer, keeping the module core lightweight.

### 1. Install an OAuth2 Provider Library
From your ProcessWire project root (where the main `composer.json` is located), require the provider:
- **For Google:** `composer require league/oauth2-google`
- **For Yahoo:** `composer require hayageek/oauth2-yahoo`
- **For Microsoft:** `composer require stevenmaguire/oauth2-microsoft`
- **For Azure:** `composer require greew/oauth2-azure-provider`

### 2. Configure XOAUTH2 Settings
1. Go to **Modules > Configure > WireMailPHPMailer**.
2. Select **XOAUTH2** under `Auth Type`.
3. Fill out the **OAuth2 Settings** block that appears below it:
   - Select the **OAuth Provider** (Google, Yahoo, Microsoft, or Azure).
   - Enter your **OAuth Email** (the account initiating sending).
   - Provide your **Client ID** and **Client Secret**.
   - *(Azure Only)* Specify your **Tenant ID** (or leave default `common`).
4. **Save** the module settings.

### 3. Generate a Refresh Token
After saving your `Client ID` and `Client Secret`, an **Authorize via {Provider}** button will appear in the settings.
1. Click the authorization button.
2. Follow the prompt to consent and log into your provider account.
3. You will be redirected back securely to the module's setup page, where the module automatically pulls and saves your **Refresh Token**.

That's it! WireMailPHPMailer will intercept ProcessWire's Mail sends and handle token refreshes in the background natively.

## Support & Links

- [PHPMailer Documentation & Wiki](https://github.com/PHPMailer/PHPMailer/wiki)
- [PHPMailer Advanced Examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples)

## License

This module is free and open-source software.