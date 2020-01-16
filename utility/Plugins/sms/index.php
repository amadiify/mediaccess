<?php

// Plugin Provided by
// Joesphi

class Sms
{
	// send sms configuration
	private $smsapi = "http://api.smartsmssolutions.com/smsapi.php?";
	private $user;
	private $username = "kodedapp";
	private	$password = "jumong@80";


	// Send SMS
	public function send(string $senderid, string $message, string $sendto)
	{
		$arr = ["username" => $this->username, "password" => $this->password, "sender" => $senderid, "recipient" => $sendto, "message" => $message];

		$build = http_build_query($arr);

		// send sms;
		$send = $this->smsapi.$build;

		$ch = curl_init($send);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		// JSON data 
		return $response;

	}


	// Check SMS balance
	public function balance()
	{
		$build = "username=".$this->username."&password=".$this->password."&balance=true";

		$send = $this->smsapi.$build;

		$ch = curl_init($send);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("application/json"));

		$exec = curl_exec($ch);
		curl_close($ch);

		// balance
		$balance = ceil($exec);

		return $balance;
	}
}