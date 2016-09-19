<?php

class Email extends PHPMailerLite {

	public $to;
	public $to_name =	'';
	public $from =		'system@oscarworthy.com';
	public $from_name = 'Oscarworthy';
	public $subject =	'';
	public $headers =	'';
	public $textbody =	'';
	public $htmlbody =	'';
	public $sig =		"--\r\nOscarworthy.com";
	public $act_code = 	'';
	public $site_url =	SITE_URL;

	function __construct(User $user) {
		parent::__construct();
		$this->to = $user->email;	
		$this->to_name = $user->username;	
	}
	
	
	function setCode($code) {
		$this->act_code = $code;
	}


	/**
	* Build body html from template and variables.
	*/
	function setBody($template) {
		$contents = file_get_contents($_SERVER['DOCUMENT_ROOT']."/emails/$template.tpl.php");
		$this->subject	= substr(strstr($contents, ']', true), 1);
		$body			= substr(strstr($contents, ']', false), 2);

		// Find and replace all possible tags in the template:
		$tags = array('to_name', 'to', 'site_url', 'act_code', 'sig');
		foreach ($tags as $tag) {
			$body = @preg_replace('/{'.$tag.'}/', $this->$tag, $body);	// @-SUPPRESS UNSET PROPERTY NOTICES
		}
		$this->htmlbody = file_get_contents($_SERVER['DOCUMENT_ROOT']."/emails/email_header.php").
						  $body.
						  file_get_contents($_SERVER['DOCUMENT_ROOT']."/emails/email_footer.php");
		$this->textbody = strip_tags($body);
	}


	/**
	* Set headers and send the email.
	*/
	function sendThis() {
		// Build the email from parts:
/*		$this->headers .= "From: ".$this->from_name." <".$this->from.">\r\n";
		$this->headers .= "Reply-To: ".$this->from_name." <".$this->from.">\r\n";
		$this->headers .= "Return-Path: ".$this->from_name." <".$this->from.">\r\n";
		$this->headers .= "X-Mailer: PHP\r\n";
		$this->headers .= "Content-Type: multipart/mixed; boundary='".$this->boundary."'\r\n";
*/		
		
		// Use PHPMailer to build email
		$this->SetFrom($this->from);
		$this->AddAddress($this->to);
		$this->Subject = $this->subject;
		$this->Body = $this->htmlbody;
		$this->AltBody = $this->textbody;
		$this->IsHTML();
		
		// Send:
		if(!$this->Send()) {
			return false;
		}
		else {
			return true;
		}
	}
		
} // end class Email
