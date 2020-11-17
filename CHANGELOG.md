# Release Notes for WireMailPHPMailer

### Added

- wireLog() for send() method, add send result to log file

### Removed

- public $exceptions variable
- construct method

### Updated

- send() method, use WireMailPHPMailer functions
- check is_string() for $address, $message, $path
- separate set user and module settings
- $options now inside init() function
- set isHTML()) for Body
- set isSMTP() for Mailer=smtp

## v.1.2.4

### Removed

- getLastMessageID from WireMailPHPMailer class

### Updated

- PHPMailer to 6.1.8
- re-write code for fix duplicate message send

## v.1.2.3

### Updated

- PHPMailer to 6.1.6

## v.1.2.2

### Updated

- PHPMailer to 6.0.7

## v.1.0.8

### Updated

- PHPMailer to 6.0.6

## v.1.0.7

### Updated

- PHPMailer to 6.0.5
- Module setting `singular: true`
- Module setting `autoload: true`

## v.1.0.6

### Fixed

- Send mail, remove `bundleEmailAndName()` function we don't need it

### Updated

- Module config panel

## v.1.0.5

### Fixed

- Old module functions

## v.1.0.5

### Fixed

- Module config default values

## v.1.0.3

### Fixed

- Getting default config values

## v.1.0.2

### Updated

- Updated configs with [see](https://processwire.com/blog/posts/new-module-configuration-options/#using-an-array-to-define-module-configuration) method.

### Removed

- InputfieldHelper module requirement

## v.1.0.1

### Updated

- PHPMailer to 6.0.3

## v.1.0.0

### Updated

- PHPMailer updated to PHPMailer 5.2.23

## v.0.0.7

### Updated

- PHPMailer updated to PHPMailer 5.2.22

## v.0.0.6

### Updated

- PHPMailer updated to PHPMailer 5.2.21

## v.0.0.5

### Updated

- PHPMailer updated to PHPMailer 5.2.19

## v.0.0.4

### Updated

- PHPMailer updated to PHPMailer 5.2.16

## v.0.0.3

### Updated

- Some module corrections
- PHPMailer updated to PHPMailer 5.2.15

## v.0.0.2

### Updated

- Initial commit

## v.0.0.1