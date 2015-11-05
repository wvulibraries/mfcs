<?php

class notification {

	/**
	 * Send an email
	 *
	 * @param array  $recipient assos array where "email@addr.com" => "Friendly Name"
	 * @param string $subject   subject of the email
	 * @param string $message   message body of the email
	 * @return bool
	 **/
	public static function email($recipient,$subject,$message) {

		$mail = new mailSender();

		foreach ($recipient as $email=>$name) {
			$mail->addRecipient($email, $name);
		}

		$mail->addSender(mfcs::config('systemEmail'), mfcs::config('systemEmailName'));
		$mail->addSubject($subject);
		$mail->addBody($message);

		return $mail->sendEmail();

	}

	public static function notifyAdmins($subject,$message) {

		global $notificationEmails;
		return self::email($notificationEmails,$subject,$message);

	}

}

?>