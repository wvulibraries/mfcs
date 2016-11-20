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

	public static function notify_form_contacts($formID, $subject, $message) {

		$sql       = sprintf("SELECT `email`, `firstname`, `lastname` FROM `users` LEFT JOIN `permissions` ON `permissions`.`userID`=`users`.`ID` WHERE `permissions`.`formID`='%s' AND `permissions`.`type`='%s'",
			mfcs::$engine->openDB->escape($formID),
			mfcs::AUTH_CONTACT
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		$contact_emails = array();
		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$contact_emails[$row['email']] = sprintf("%s %s",$row["firstname"],$row['lastname']);
		}

		return (count($contact_emails) > 0)?self::email($contact_emails,$subject,$message):TRUE;

	}

	/**
	 * Get an array of email addresses for the form id that should be contacted on events.
	 *
	 * For information on adding contacts to a form see this wiki page:
	 * https://github.com/wvulibraries/mfcs/wiki/Form-Permissions
	 *
	 * For information on adding global contacts see:
	 * https://github.com/wvulibraries/mfcs/wiki/Installation
	 *
	 * @param integer $form_id valid form id
	 * @return array of emails defined in the contact permissions for the form PLUS the global contacts.
	 */
	public static function get_notification_emails($form_id) {
		global $notificationEmails;

		$sql       = sprintf("SELECT `users`.`email` as `email` FROM `users` LEFT JOIN `permissions` on `permissions`.`userID`=`users`.`ID` WHERE `permissions`.`type`=4 AND `permissions`.`formID`='%s'",
		mfcs::$engine->openDB->escape($form_id));
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return false;
		}

		while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			$notificationEmails[$row['email']] = $row['email'];
		}

		return $notificationEmails;
	}

}

?>
