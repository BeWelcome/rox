<?php
// This file is an automated generated file it is make using AdminPanel tool
// Generated (manually ;-) by JeanYves on November 2006
$_SYSHCVOL['ReloadRight']='False' ; // This parameter if set to True will force each call to HasRight to look in the database, this is usefull when a right is update to force it to be used immediately, of course in the long run it slow the server 
$_SYSHCVOL['DomainName']='openhc.org' ; // This is the name of the web site 
$_SYSHCVOL['SiteName']="www.".$_SYSHCVOL['DomainName'] ; // This is the name of the web site 
$_SYSHCVOL['MessageSenderMail']='message@'.$_SYSHCVOL['DomainName'] ; // This is the default mail used as mail sender
$_SYSHCVOL['ferrorsSenderMail']='ferrors@'.$_SYSHCVOL['DomainName'] ; // This is the mail in case of mail error
$_SYSHCVOL['SignupSenderMail']='signup@'.$_SYSHCVOL['DomainName'] ; // This is the mail use by signup page for sending access

$_SYSHCVOL['QualityComments']=array('Good','Neutral','Bad') ; // This is the possible Qualifier for the comments

$_SYSHCVOL['SiteStatus']="Open" ; // This can be "Closed" or "Open", depend if the site is to be closed or open
$_SYSHCVOL['SiteCloseMessage']="The site is temporary closed" ; // Message wich is displayed when the site is closed
$_SYSHCVOL['Accomodation']=array('cannotfornow', 'yesicanhost', 'dependonrequest', 'notfornow', 'neverask', 'anytime') ; // possible answers for accomodation
$_SYSHCVOL['LenghtComments']=array('OnlyChatMail', 'OnlyOnce', 'hewasmyguest', 'hehostedme', 'Itrusthim', 'MoreThanAMonth', 'MoreThanAYear', 'IIntroduceHimToHospitality', 'HeIntroducemeToHospitality', 'HeIsMyFamily', 'HeHisMyOldCloseFriend', 'HeIsMyNeigbour') ;// possible lenght of stay
$_SYSHCVOL['EvaluateEventMessageReceived']="Yes" ;// If set to "Yes" events messages received is evaludated at each page refresh

$_SYSHCVOL['DomainName']='http://ns20516.ovh.net/HCVoljyh' ; // This is the name of the web site (overwrite)


?>