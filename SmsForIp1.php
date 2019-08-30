<?php
namespace thomasdilts\sms_worshiphhn_ip1;

use thomasdilts\sms_worshiphhn\Sms;

class SmsForIp1 extends Sms
{
	public $account;
	public $password;
	public $apiUrl;
	public $messageFrom;
	public function receivedSms() {
        // not implemented

	}
	public function getSmsStatus($smsId, $smsLastStatusId, $smsLastStatusText)
	{
		if($smsLastStatusId == '22'){
			return ['statusId' => $smsLastStatusId, 'statusText' => $smsLastStatusText];
		}
		$confIn = array(
			'account' => $this->account,
			'password' => $this->password,
			'apiUrl' => $this->apiUrl,
			'method' => 'GET',
			'endpoint' => '/api/sms/sent/' . $smsId,
		);
		try {
			$result = $this->perform_http_request($confIn);
		} catch (Exception $e) {
			return ['statusId' => 'ServerReturnError', 'statusText' => $e->getMessage()];
		}
		try {
			$responseDecoded = json_decode($result);
		} catch (Exception $e) {
			return ['statusId' => 'BadServerResponse', 'statusText' => $e->getMessage()];
		}
		if (property_exists($responseDecoded, 'ID') && property_exists($responseDecoded, 'Status') && property_exists($responseDecoded, 'StatusDescription')) {
			return ['statusId' => $responseDecoded->Status, 'statusText' => $responseDecoded->StatusDescription];
		} else if (property_exists($responseDecoded, 'Message')) {
			return ['statusId' => 'Error', 'statusText' => $responseDecoded->Message];
		} else {
			return ['statusId' => 'Error', 'statusText' => 'Unknown error occured.'];
		}
	}
	public function sendSms($message, $phoneNumber)
	{
		$confIn = array(
			'account' => $this->account,
			'password' => $this->password,
			'apiUrl' => $this->apiUrl,
			'method' => 'POST',
			'endpoint' => '/api/sms/send',
		);
		$messageArray = array(
			'Numbers' => [$phoneNumber],
			'From' => $this->messageFrom,
			'Message' => $message,
		);

		try {
			$result = $this->perform_http_request($confIn, $messageArray);
		} catch (Exception $e) {
			return ['id' => '', 'statusId' => 'ServerReturnError', 'statusText' => $e->getMessage()];
		}
		try {
			$responseDecoded = json_decode($result);
		} catch (Exception $e) {
			return ['id' => '', 'statusId' => 'BadServerResponse', 'statusText' => $e->getMessage()];
		}
		if (property_exists($responseDecoded, 'ID') && property_exists($responseDecoded, 'Status') && property_exists($responseDecoded, 'StatusDescription')) {
			return [
				'id' => $responseDecoded->ID, 'statusId' => $responseDecoded->Status, 'statusText' => $responseDecoded->StatusDescription
			];
		} else if (property_exists($responseDecoded, 'Message')) {
			return ['id' => '', 'statusId' => 'Error', 'statusText' => $responseDecoded->Message];
		} else {
			return ['id' => '', 'statusId' => 'Error', 'statusText' => 'Unknown error occured.'];
		}
	}
	public function getImplementationLevel()
	{
		return IMPLEMENTED_ID_AND_STATUS;
	}
	private function perform_http_request($conf, $data = null)
	{
		$curl = curl_init();
		$url = "https://" . $conf['apiUrl'] . $conf['endpoint'];
		switch ($conf['method']) {
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);

				if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;

			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
				break;

			default:
				if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
		}

            // Optional Authentication:
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $conf['account'] . ":" . $conf['password']);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);

		return $result;
    }
}
