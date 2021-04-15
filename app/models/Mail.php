<?php

require 'lib/PHPMailer/Exception.php';
require 'lib/PHPMailer/PHPMailer.php';
require 'lib/PHPMailer/SMTP.php';

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

class Mail extends \Controller
{

	public function send($sender,$recipient,$subject,$message)
	{
		$mail = new PHPMailer(true);							  // Passing `true` enables exceptions
		try {
			//Server settings
			$mail->CharSet = 'UTF-8';
			$mail->SMTPDebug = 0;								 // 2=Enable verbose debug output
			$mail->isSMTP();									  // Set mailer to use SMTP
			$mail->Host = $this->f3->get('smtp_host');   		  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;							   // Enable SMTP authentication
			$mail->Username = $this->f3->get('smtp_user');		// SMTP username
			$mail->Password = $this->f3->get('smtp_pw');		  // SMTP password
			$mail->SMTPSecure =$this->f3->get('smtp_scheme');	 // Enable TLS 
			$mail->Port = $this->f3->get('smtp_port');			// TCP port to connect to

			//reply to before setfrom: https://stackoverflow.com/questions/10396264/phpmailer-reply-using-only-reply-to-address
			$mail->AddReplyTo($sender);
			
			$mail->setFrom( $this->f3->get('smtp_user') );

			$mail->addAddress($recipient);	 // Add a recipient

			//Content
			$mail->isHTML(true);									// Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body	= $message;
			$mail->AltBody = strip_tags($message);

			$mail->send();
			
			$logger = new Log($this->f3->LOG_DIR.date("Ymd").'mail.log');
			$logger->write( "MAIL SENT: to " .$recipient . " message: ".$message,'r'  );
			return true;
			//echo 'Message has been sent';
		} catch (\Exception $e) {
			$logger = new Log($this->f3->LOG_DIR.date("Ymd").'mailerror.log');
			$logger->write( "MAIL ERROR: " .$mail->ErrorInfo,'r'  );
		}
		return false;

	}
}