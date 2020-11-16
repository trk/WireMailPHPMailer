# WireMailPHPMailer

This module extends WireMail base class, integrating the [PHPMailer](https://github.com/PHPMailer/PHPMailer) mailing library into ProcessWire.The module using [PHPMailer](https://github.com/PHPMailer/PHPMailer). You can see an example usage below.

[Simple example](https://github.com/PHPMailer/PHPMailer#a-simple-example)

[Other examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples)

[Wiki](https://github.com/PHPMailer/PHPMailer/wiki)

You can set your configs from module settings or you can directly call `$mail = wire("modules")->get("WireMailPHPMailer"); $mail = $mail->mailer();` function for `new PHPMailer()` instance.

Using Directly PHPMailer library
-

```php
/** @var WireMailPHPMailer $mail */
$mail = wire("modules")->get("WireMailPHPMailer");
// load module without module configs
/** @var PHPMailer $mail */
$mail = $mail->getInstance(false);
```

```php
$mail = wire("modules")->get("WireMailPHPMailer");
$mail = $mail->getInstance();
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