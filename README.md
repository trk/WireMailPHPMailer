# WireMailPHPMailer

This module extends WireMail base class, integrating the [PHPMailer](https://github.com/PHPMailer/PHPMailer) mailing library into ProcessWire.The module using [PHPMailer 6.0.3](https://github.com/PHPMailer/PHPMailer). You can see an example usage below.

[Simple example](https://github.com/PHPMailer/PHPMailer#a-simple-example)

[Other examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples)

[Wiki](https://github.com/PHPMailer/PHPMailer/wiki)

You can set your configs from module settings or you can directly call `$mail = wire("modules")->get("WireMailPHPMailer"); $mail = $mail->mailer();` function for `new PHPMailer()` instance.

Using Directly PHPMailer library
-

```php
$mail = wire("modules")->get("WireMailPHPMailer");
$mail = $mail->mailer();
$mail->addAddress("email@domain.ltd", "Someone");
$mail->isHTML(true);
$mail->Subject = "WireMailPHPMailer";
$html = "<h1>WireMailPHPMailer</h1>";
$text = "WireMailPHPMailer";
$mail->Body    = $html;
$mail->AltBody = $text;
$mail->send();
```

Using Like classic WireMail method
-

```php
$mail = wire("modules")->get("WireMailPHPMailer");
$mail->from("from@domain.ltd")
    ->fromName("A From Name")
    ->to('email@domain.ltd')
    ->subject('A Message Subject')
    ->body('A Message Body')
    ->bodyHtml("<h1>A HTML Message Body</h1>")
    ->send();
```


# Changelog

## v.1.0.4

### Fixed

- Old module functions

## v.1.0.3

### Fixed

- Module config default values

## v.1.0.2

### Fixed

- Getting default config values

## v.1.0.1

### Updated

- Updated configs with [see](https://processwire.com/blog/posts/new-module-configuration-options/#using-an-array-to-define-module-configuration) method.

### Removed

- InputfieldHelper module requirement

## v.1.0.0

### Updated

- PHPMailer to 6.0.3

## v.0.0.7

### Updated

- PHPMailer updated to PHPMailer 5.2.23

## v.0.0.6

### Updated

- PHPMailer updated to PHPMailer 5.2.22

## v.0.0.5

### Updated

- PHPMailer updated to PHPMailer 5.2.21

## v.0.0.4

### Updated

- PHPMailer updated to PHPMailer 5.2.19

## v.0.0.3

### Updated

- PHPMailer updated to PHPMailer 5.2.16

## v.0.0.2

### Updated

- Some module corrections
- PHPMailer updated to PHPMailer 5.2.15

## v.0.0.1

### Updated

- Start Point