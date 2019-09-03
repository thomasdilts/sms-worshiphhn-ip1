# Description
This is an SMS messaging system using the sms supplier https://ip1sms.com for the [WorshipHHN](https://github.com/thomasdilts/worshiphhn) project.
This program must be a subclass of the file Sms.php found in the [thomasdilts/sms_worshiphhn](https://github.com/thomasdilts/sms_worshiphhn) project so that [WorshipHHN](https://github.com/thomasdilts/worshiphhn) can use it.

## Rest(ful) API

The SMS messaging supplier https://ip1sms.com uses a Rest(ful) API in order to do the SMS messaging. This is different then many other SMS messaging systems in that this method uses only "pulling" of the status of the messages/replies from the Supplier. In many other SMS messaging systems you need to supply an web address that the supplier can call to give changed statuses of messages. I prefer the "pulling" technique because it is more secure both for data integrity and security from hacking.

The following table is the Rest(ful) API exposed by https://ip1sms.com that this program uses:

API	| Description
---- | ---------
GET api/sms/sent?from[0]={from[0]}&from[1]={from[1]}&to[0]={to[0]}&to[1]={to[1]} | Gives you a list of your sent SMS messages filtered by given URI parameters if given
GET api/sms/sent/bundle/{bundle} | Gives you a list of your sent SMS messages in a given bundle
GET api/sms/sent/{sms} | Gives you a given sent SMS message
POST api/sms/send | Send an sms with the given information directly
GET api/sms/received?from[0]={from[0]}&from[1]={from[1]}&to[0]={to[0]}&to[1]={to[1]} | Gives you a list of your received SMS messages filtered by given URI parameters if given
GET api/sms/received/{sms} | Gives you a given received SMS message

## Add to WorshipHHN
To add this to the [WorshipHHN](https://github.com/thomasdilts/worshiphhn) project you need to edit the file following file in [WorshipHHN](https://github.com/thomasdilts/worshiphhn), _protected/config/web.php

```txt
    'components' => [
...
	'SmsMessaging' => [
		'class' => 'thomasdilts\sms_worshiphhn_ip1\SmsForIp1',
		'account' => 'YOUR_ACCOUNT',
		'password' => 'YOUR_PASSWORD',
		'apiUrl' => 'api.ip1sms.com',
		'messageFrom' => 'FROM_NAME_OR_PHONE_NUMBER',
		'phoneNumberCountryCode' => '46', 
		'removeLeadingZeroFromPhoneNumber' => 'true', 			
	]		
    ],
```

You probably then need to eventually completely rewrite the file [_protected/vendor/thomasdilts/sms_worshiphhn_ip1/SmsForIp1.php](https://github.com/thomasdilts/sms_worshiphhn_ip1) to 
make it work with your SMS supplier. By "rewrite" I mean to copy to another file and class and then rewrite. Your class must be a sub-class of [thomasdilts\sms_worshiphhn\Sms](https://github.com/thomasdilts/sms_worshiphhn) to be useable by WorshipHHN.
