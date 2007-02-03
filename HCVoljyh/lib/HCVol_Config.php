<?php

// Generated using Admin Pannel at January 15, 2007, 9:07 pm // This file is an automated generated file it is make using AdminPanel tool (todo !)
// Generated (manually ;-) by JeanYves on November 2006
$_SYSHCVOL['Crypted'] = ''; // This parameter if the name of the database with (a dot) where are stored crypted data, there is no cryptation it it is left blank 
$_SYSHCVOL['ReloadRight'] = 'False'; // This parameter if set to True will force each call to HasRight to look in the database, this is usefull when a right is update to force it to be used immediately, of course in the long run it slow the server 
$_SYSHCVOL['DomainName'] = 'bewelcome.org'; // This is the name of the web site 
$_SYSHCVOL['SiteName'] = "www." . $_SYSHCVOL['DomainName']; // This is the name of the web site 
$_SYSHCVOL['MessageSenderMail'] = 'message@' . $_SYSHCVOL['DomainName']; // This is the default mail used as mail sender
$_SYSHCVOL['CommentNotificationSenderMail'] = 'admincomment@bewelcome.org'; // This is the mail which receive notification about Bad comments
$_SYSHCVOL['NotificationMail'] = 'comment@' . $_SYSHCVOL['DomainName']; // This is the default mail used to notify a member about a comment
$_SYSHCVOL['ferrorsSenderMail'] = 'ferrors@' . $_SYSHCVOL['DomainName']; // This is the mail in case of mail error
$_SYSHCVOL['SignupSenderMail'] = 'signup@' . $_SYSHCVOL['DomainName']; // This is the mail use by signup page for sending access
$_SYSHCVOL['AccepterSenderMail'] = 'accepting@' . $_SYSHCVOL['DomainName']; // This is the mail use by accepter action
$_SYSHCVOL['FeedbackSenderMail'] = 'feedback@' . $_SYSHCVOL['DomainName']; // This is the mail use to send mail to volunteers
$_SYSHCVOL['TestMail'] = 'testmail@' . $_SYSHCVOL['DomainName']; // This is the sender to use with the TestMail feature
$_SYSHCVOL['MailToNotifyWhenNewMemberSignup'] = 'jyhegron@laposte.net'; // This is the mail(s) to notify when a new member has signup
$_SYSHCVOL['QualityComments'] = array (
	'Good',
	'Neutral',
	'Bad'
); // These are the possible Qualifier for the comments
$_SYSHCVOL['SiteStatus'] = "Open"; // This can be "Closed" or "Open", depend if the site is to be closed or open
$_SYSHCVOL['SiteCloseMessage'] = "The site is temporary closed"; // Message wich is displayed when the site is closed
$_SYSHCVOL['Accomodation'] = array (
	'cannotfornow',
	'yesicanhost',
	'dependonrequest',
	'notfornow',
	'neverask',
	'anytime'
); // possible answers for accomodation
$_SYSHCVOL['LenghtComments'] = array (
	'OnlyChatMail',
	'OnlyOnce',
	'hewasmyguest',
	'hehostedme',
	'Itrusthim',
	'MoreThanAMonth',
	'MoreThanAYear',
	'IIntroduceHimToHospitality',
	'HeIntroducemeToHospitality',
	'HeIsMyFamily',
	'HeHisMyOldCloseFriend',
	'HeIsMyNeigbour'
); // possible lenght of stay
$_SYSHCVOL['EvaluateEventMessageReceived'] = "Yes"; // If set to "Yes" events messages received is evaludated at each page refresh
//$_SYSHCVOL['DomainName']='http://ns20516.ovh.net/HCVoljyh' ; // This is the name of the web site (overwrite)
$_SYSHCVOL['UploadPictMaxSize'] = 500000; // This define the size of the max loaded pictures
$_SYSHCVOL['AgeMinForApplying'] = 18; // Minimum age a wannabe member must have to become a member 
$_SYSHCVOL['MainDir'] = '/HCVoljyh'; // This is the name of the web site 
$_SYSHCVOL['WhoIsOnlineActive'] = 'Yes'; // Wether who is online is active can be Yes or No 
$_SYSHCVOL['WhoIsOnlineDelayInMinutes'] = 10; // The delay of non activity to consider a member off line 
$_SYSHCVOL['WhoIsOnlineLimit'] = 11; // This limit the number of whoisonline, causing the display of ww('MaxOnlineNumberExceeded') at login for new loggers 
?>