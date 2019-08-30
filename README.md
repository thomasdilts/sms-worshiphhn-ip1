# sms-worshiphhn-ip1
This is an SMS system using the sms supplier https://ip1sms.com for the worshiphhn project.
This vendor must be a subclass of the file SmsInterface found in the thomasdilts/worshiphhn project so that WorshipHHN can use it.


API	| Description
---- | ---------
GET api/sms/sent?from[0]={from[0]}&from[1]={from[1]}&to[0]={to[0]}&to[1]={to[1]} | Gives you a list of your sent SMS messages filtered by given URI parameters if given
GET api/sms/sent/bundle/{bundle} | Gives you a list of your sent SMS messages in a given bundle
GET api/sms/sent/{sms} | Gives you a given sent SMS message
POST api/sms/send | Send an sms with the given information directly
GET api/sms/received?from[0]={from[0]}&from[1]={from[1]}&to[0]={to[0]}&to[1]={to[1]} | Gives you a list of your received SMS messages filtered by given URI parameters if given
GET api/sms/received/{sms} | Gives you a given received SMS message
