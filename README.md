# PHPMailer

This is just a simple mailer class.

To use it:

$mailer = new PHPMailer('mail.yourdomain.com', 25, 'no-replay@yourdomain.com');
if ($mailer->send('you@yourdomain.com', 'This is a subject', 'This is the message body.')) {
  echo "Woohoo! We sent a message!";
} else {
  echo "Oops! Our message didn't go through.";
}

The script can give you that confirmation that the message was sent, but you don't have to include the if-then-else statements.
