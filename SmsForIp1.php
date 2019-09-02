<?php
namespace thomasdilts\sms_worshiphhn_ip1;

use thomasdilts\sms_worshiphhn\Sms;
use Yii;
use yii\web\ServerErrorHttpException;

class SmsForIp1 extends Sms
{
	public $account;
	public $password;
	public $apiUrl;
	public $messageFrom;
	public $phoneNumberCountryCode;
	public $removeLeadingZeroFromPhoneNumber;
	
	public function receivedSms() {
        // not implemented

	}
	public function getSmsStatus($smsId, $smsLastStatusId, $smsLastStatusText)
	{
		// this is to stop unnecessary querying of the server. These are final statuses
		if($smsLastStatusId == '22' || $smsLastStatusId == '52' || $smsLastStatusId == '3' || $smsLastStatusId == '50' || $smsLastStatusId == '51'  ){
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
		Yii::info('result= '.$result .' ;config='.serialize($confIn), 'SmsForIp1-getSmsStatus');
		if(!$result || strlen($result)==0){
			// the server can't find this message.
			return ['statusId' => 'Error', 'statusText' => 'Server cannot find message'];
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
	public function sendSms($message, $phoneNumber, $fromNumber)
	{
		if(!$fromNumber || strlen($fromNumber)==0){
			$fromNumber=$this->messageFrom;
		}else{
			if(strlen($this->formatPhoneNumber($fromNumber))>5){
				$fromNumber=$this->formatPhoneNumber($fromNumber);
			}
		}
		//return ['id' => '42487049', 'statusId' => 'TurnedOff', 'statusText' => 'Turned off for now'];
		$phoneNumber=$this->formatPhoneNumber($phoneNumber);
		$confIn = array(
			'account' => $this->account,
			'password' => $this->password,
			'apiUrl' => $this->apiUrl,
			'method' => 'POST',
			'endpoint' => '/api/sms/send',
		);
		$messageArray = array(
			'Numbers' => [$phoneNumber],
			'From' => $fromNumber,
			'Message' => $message,
		);
		Yii::info('messageArray= '.json_encode($messageArray) .' ;config='.serialize($confIn), 'SmsForIp1-sendSms');

		try {
			$result = $this->perform_http_request($confIn, json_encode($messageArray));
		} catch (Exception $e) {
			return ['id' => '', 'statusId' => 'ServerReturnError', 'statusText' => $e->getMessage()];
		}
		Yii::info('response= '.$result , 'SmsForIp1-sendSms');
		try {
			$responseDecoded = json_decode($result);
		} catch (Exception $e) {
			return ['id' => '', 'statusId' => 'BadServerResponse', 'statusText' => $e->getMessage()];
		}
		if (property_exists($responseDecoded[0], 'ID') && property_exists($responseDecoded[0], 'Status') && property_exists($responseDecoded[0], 'StatusDescription')) {
			return [
				'id' => $responseDecoded[0]->ID, 'statusId' => $responseDecoded[0]->Status, 'statusText' => $responseDecoded[0]->StatusDescription
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
	private function formatPhoneNumber($phoneNumber){
		$numbersOnly = preg_replace('/[^0-9]/', '', $phoneNumber);
		if($this->removeLeadingZeroFromPhoneNumber=='true'){
			while(substr($numbersOnly,0,1)=='0'){
				$numbersOnly=substr($numbersOnly,1);
			}
		}
		if($this->phoneNumberCountryCode && strlen($this->phoneNumberCountryCode)>0 && substr($numbersOnly,0,strlen($this->phoneNumberCountryCode))!=$this->phoneNumberCountryCode){
			$numbersOnly=$this->phoneNumberCountryCode . $numbersOnly;
		}
		return $numbersOnly;
	}
	private function perform_http_request($conf, $data = null)
	{
		$curl = curl_init();
		$url = "https://" . $conf['apiUrl'] . $conf['endpoint'];
		switch ($conf['method']) {
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);

				if ($data) {
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($data)));  
				}					
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
