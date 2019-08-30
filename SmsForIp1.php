use thomasdilts\sms-worshiphhn\base\Sms;

class SmsForIp1 extends Sms
{
  public function receivedSms(){
  }
  public function getSmsStatus($smsId,$smsLastStatusId,$smsLastStatusText){
  }
  public function sendSms($message,$phoneNumbers){
  }
  public function getImplementationLevel(){
    return IMPLEMENTED_ID_AND_STATUS;
  }
}
