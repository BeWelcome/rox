<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

/*
 * DBUpdateCheck() checks the version of the current DB and updates it if possible
 * and shows the error message when not. No parameters or return values.
 */
function DBUpdateCheck()
{
	$updates = array();

	/* 
	 * to make new DB update just add a line like this:
	 * $updates[xxx] = "SQL string...";
	 * empty means that update has to be done manually:
	 * $updates[69] = ""; // this update has to be done manually
	 */
	
	$updates[1] = "CREATE TABLE `dbversion` (`version` INT NOT NULL DEFAULT '0',PRIMARY KEY ( `version` )) ENGINE = MYISAM COMMENT = 'stores the DB version';"; 
	$updates[2] = "INSERT into `dbversion` values(1)";

	$updates[3] = "CREATE TABLE `guestsonline` ("
				."`IpGuest` int(11) NOT NULL COMMENT 'ip address of the user who is online',"
  				."`updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update "
				."CURRENT_TIMESTAMP COMMENT 'last time the member was active',"
  				."`appearance` varchar(256) NOT NULL COMMENT 'What to show about the member this can be a html thing',"
  				."`lastactivity` varchar(256) NOT NULL COMMENT 'Last url the member call',"
  				."`Status` varchar(32) NOT NULL default 'Active' "
				."COMMENT 'a copy of the status of the member (sometime he can be ActiveHidden)',"
  				."PRIMARY KEY (`IpGuest`)) ENGINE=MEMORY DEFAULT CHARSET=utf8 "
				."COMMENT='Table of last guest online, it is purged periodically  ';";
	
	$updates[4] = "UPDATE words "
	            ."SET Sentence = 'Bitte &uuml;berpr&uuml;fe die Stadt. Die angegebene Stadt konnte nicht gefunden werden.' "
				."WHERE code='SignupErrorProvideCity' and ShortCode='de';";
	$updates[5] = "UPDATE words "
	            ."SET Sentence = 'Please check the city. The city could not be found.' "
				."WHERE code='SignupErrorProvideCity' and ShortCode='en';";
	$updates[6] = ""; // This is empty on purpose, this forces manual DB update
	$updates[7] = "DROP TABLE tantable";
	$updates[8] = "DROP TABLE userfrombewelcome";
	$updates[9] = "ALTER TABLE `guestsonline` MODIFY `appearance` varchar(255)";
	$updates[10] = "ALTER TABLE `guestsonline` MODIFY `lastactivity` varchar(255)";
	
	$updates[11] = "ALTER TABLE `cryptedfields` ADD `temporary_uncrypted_buffer` TEXT" ;
	$updates[12] = "ALTER TABLE `cryptedfields` CHANGE `temporary_uncrypted_buffer` `temporary_uncrypted_buffer` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT 'This is used when no encryption is use'" ;
	$updates[13] = "ALTER TABLE `words` ADD `created` TIMESTAMP NOT NULL" ;
	$updates[14] = "ALTER TABLE `members` CHANGE `TypicOffer` `TypicOffer` SET('guidedtour','dinner','CanHostWeelChair') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Other typical offer that a member can choose to propose'" ;
    $updates[15] = "ALTER TABLE `members` CHANGE `Accomodation` `Accomodation` "
                 ."ENUM('anytime', 'yesicanhost', 'dependonrequest', 'cannotfornow', 'neverask')";
    $updates[16] = "ALTER TABLE `members` CHANGE `Accomodation` `Accomodation` "
                 ."ENUM('anytime', 'dependonrequest', 'neverask')";
				 
	$updates[17] = "CREATE TABLE IF NOT EXISTS broadcast ("
					."id int(11) NOT NULL auto_increment COMMENT 'primary key',"
					."IdCreator int(11) NOT NULL COMMENT 'Id of the member who created the massmail',"
					."`Name` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of the mass mail',"
					."created timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'creation date',"
					."`Status` enum('Created','Triggered') collate utf8_unicode_ci NOT NULL default 'Created',"
					."`Type` enum('Normal') collate utf8_unicode_ci NOT NULL default 'Normal',"
					."PRIMARY KEY  (id)"
					.") ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This table is for massmail used by adminmassmails'" ;
	$updates[18] = "CREATE TABLE IF NOT EXISTS `broadcastmessages` ("
					."`IdBroadcast` int(11) NOT NULL COMMENT 'id of the broadcast entry',"
					."`IdReceiver` int(11) NOT NULL COMMENT 'Id of the receiver',"
					."`IdEnqueuer` int(11) NOT NULL COMMENT 'Id of the volunteer who enqueue the message',"
					."`Status` enum('ToApprove','ToSend','Sent') collate utf8_unicode_ci NOT NULL default 'ToApprove' COMMENT 'Status of the message',"
					."`updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'last update',"
					."PRIMARY KEY  (`IdBroadcast`,`IdReceiver`)"
					.") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This is the table with the list of members to broad cast'" ;
					
	$updates[19] = "ALTER TABLE `words` CHANGE `ShortCode` `ShortCode` CHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en'" ;

	$updates[20] = "delete from user where lastlogin is null" ; 

	$updates[21] = "ALTER TABLE `user` DROP INDEX `handle`" ;

	$updates[22] = "ALTER TABLE `user` ADD UNIQUE ( `handle`)" ;
	
	$updates[23] = "ALTER TABLE `user` DROP INDEX `handle`"; // correct 22, DROPs UNIQUE CONSTRAINT

	$updates[24] = "ALTER TABLE `user` ADD INDEX (`handle`)"; // correct 21
	
	$updates[25] = "INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'Gallery', 'This right is for Gallery managment (to allow to delete or edit other members pictures in the gallery) Avalaible scope are &quot;All&quot; for all scope &quot;edit&quot; for editing gallery text &quot;delete&quot; for allowing to delete other people picture')" ; // To create a right for gallery


	$updates[26] = 'ALTER TABLE `cryptedfields` CHANGE `id` `id` INT( 11 ) NOT NULL' ; 
	$updates[27] = 'ALTER TABLE `cryptedfields` DROP PRIMARY KEY' ; 
	$updates[28] = 'delete from cryptedfields where id=0' ;
	$updates[29] = 'ALTER TABLE `cryptedfields` ADD PRIMARY KEY ( `id` ) ' ;
	$updates[30] = 'ALTER TABLE `cryptedfields` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT' ;
	$updates[31] = "CREATE TABLE `posts_notificationqueue` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`Status` ENUM( 'ToSend', 'Sent' ) NOT NULL DEFAULT 'ToSend' COMMENT 'wether the notification was sent or not',
`IdMember` INT NOT NULL COMMENT 'The member to notify',
`IdPost` INT NOT NULL COMMENT 'The post to notify about',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL ,
`created` TIMESTAMP NOT NULL ,
`Type` ENUM( 'newthread', 'reply', 'moderatoraction', 'deletepost', 'deletethread', 'useredit', 'translation', 'buggy' ) NOT NULL DEFAULT 'buggy' COMMENT 'the type of the notification'
) ENGINE = MYISAM COMMENT = 'This table is to be used to tell mailbot who to notify about some forum activity'
" ;
	
	$updates[32] = "DROP TABLE IF EXISTS `broadcast`" ;
	$updates[33] = "CREATE TABLE `broadcast` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `IdCreator` int(11) NOT NULL COMMENT 'Id of the member who created the massmail',
  `Name` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of the mass mail',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'creation date',
  `Status` enum('Created','Triggered') collate utf8_unicode_ci NOT NULL default 'Created',
  `Type` enum('Normal') collate utf8_unicode_ci NOT NULL default 'Normal',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This table is for massmail used by adminmassmails'" ;
	// $updates[20] = "blahblah"
	
	
	$updates[34] = "ALTER TABLE `members` ADD UNIQUE `i_Username` ( `Username` ( 100 ) )  " ;
	
	
	$updates[35] = "ALTER TABLE `memberstrads` ADD `IdRecord` INT NOT NULL DEFAULT '0' COMMENT 'Security redudancy : This is the id of the record for the corresponding TableColumn',
ADD `TableColumn` VARCHAR( 200 ) NOT NULL DEFAULT 'NotSet' COMMENT 'Security redudancy : This define the Table and the column wich is the pointer to the current record'" ;

	$updates[36] = "ALTER TABLE `cryptedfields` ADD `IdRecord` INT NOT NULL DEFAULT '0' COMMENT 'Security redudancy : This is the id of the record for the corresponding TableColumn',
ADD `TableColumn` VARCHAR( 200 ) NOT NULL DEFAULT 'NotSet' COMMENT 'Security redudancy : This define the Table and the column wich is the pointer to the current record'" ;

	$updates[37] = "INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'ForumModerator', 'This is the right needed for forum moderators Various options will be define later for now, only Scope is : &quot;All&quot; &quot;Edit&quot; Scope will allow to edit messages'
)" ;

	$updates[37] = "CREATE TABLE `tags` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'id of the tag',
`Name` INT NOT NULL COMMENT 'Name of the tag ( this is in members trads or in words depend on the category)',
`Description` INT NOT NULL COMMENT 'description of the tag purpose( this is in members trads or in words depend on the category)',
`Type` ENUM( 'Category', 'UserTag' ) NOT NULL DEFAULT 'UserTag' COMMENT 'Type of the tag',
`Position` INT NOT NULL DEFAULT '200' COMMENT 'Position of the tag',
`created` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the tag was created'
) ENGINE = MYISAM COMMENT = 'This table is the table of tag, it will be use by forum, groups , blogs etc etc'" ;

	$updates[38] = "CREATE TABLE `tags_threads` (
`IdTag` INT NOT NULL COMMENT 'The tag',
`IdThread` INT NOT NULL COMMENT 'The thread'
) ENGINE = MYISAM COMMENT = 'link between tags and threads'" ;

	$updates[39] = "ALTER TABLE `tags_threads` ADD UNIQUE (`IdTag` ,`IdThread`)" ;

	$updates[40] = "UPDATE `rights` SET `created` = NOW( ) ,
`Description` = 'The debug right allow the user to see debug error (like the one produced by sql_query) ; or the one produced by bw_error() call The level is 1 or 0 They are specific Scope values : ShowLastActivity : allow to see last activity of logged people (beware it is at the limit of privacy) ShowErrorLog : allow to see the last lines of the php error log ShowSlowQuery : allow to see the last queries DB_QUERY : allow to list the DB_QUERY which have been done on the current page' WHERE `rights`.`id` =7 LIMIT 1 " ;

	$updates[41] = "ALTER TABLE `countries` ADD `NbMembers` INT NOT NULL DEFAULT '0' COMMENT 'Number of active members in this country'" ;
	$updates[42] = "ALTER TABLE `regions` ADD `NbMembers` INT NOT NULL DEFAULT '0' COMMENT 'The number of members in this region (this is a redudancy)'" ;
	$updates[43] = "ALTER TABLE `cities` ADD `NbMembers` INT NOT NULL DEFAULT '0' COMMENT 'The number of members in this city (this is a redudancy)'" ;
	$updates[44] ="update countries set NbMembers=(select count(*) from members,cities,regions where members.IdCity=cities.id and cities.IdRegion=regions.id and members.Status='Active' and regions.IdCountry=countries.id)" ;
	$updates[45] ="update regions set NbMembers=(select count(*) from members,cities where members.IdCity=cities.id and cities.IdRegion=regions.id and members.Status='Active' and regions.id=cities.id)" ;
	$updates[46] ="update cities set NbMembers=(select count(*) from members where members.IdCity=cities.id and members.Status='Active') where cities.id in (select distinct IdCity from members)" ;
	
	$updates[47] ="ALTER TABLE `members` CHANGE `Username` `Username` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'This is the username of the member its a unique field (shared with the user table)'" ;
	
	$updates[48] ="ALTER TABLE `posts_notificationqueue` ADD `IdSubscription` INT NOT NULL DEFAULT '0' COMMENT 'Id of the subsrciption (if any) to allow efficient unsubscribe procedure'" ;
	$updates[49] ="ALTER TABLE `posts_notificationqueue` ADD `TableSubscription` VARCHAR( 64 ) NOT NULL DEFAULT 'NotSet' COMMENT 'The name of the subscription table this notification is caused by'" ;
	$updates[50] ="CREATE TABLE `gallery_comments` (
`id` int( 10 ) unsigned NOT NULL NULL  auto_increment ,
`gallery_id_foreign` int( 10 ) unsigned NOT NULL default '0',
`gallery_items_id_foreign` int( 10 ) unsigned NOT NULL default '0',
`user_id_foreign` int( 10 ) unsigned NOT NULL default '0',
`created` datetime NOT NULL ,
`title` varchar( 75 ) NOT NULL default '',
`text` mediumtext NOT NULL ,
KEY `id` ( `id` ) ,
KEY `blog_id_foreign` ( `gallery_items_id_foreign` ) ,
KEY `user_id_foreign` ( `user_id_foreign` )
) ENGINE = InnoDB DEFAULT CHARSET = utf8";
	$updates[51] ="ALTER TABLE `gallery_items`
  ADD `description` text NOT NULL ";
	
	$updates[] ="ALTER TABLE `forums_posts` ADD `IdWriter` INT NOT NULL DEFAULT '0' COMMENT 'This is the member who write the post, this is th index to use to retrieve the member data in Members table' AFTER `authorid`" ;
	$updates[] ="ALTER TABLE `forums_posts` ADD INDEX ( `IdWriter` )" ;
	$updates[] ="update forums_posts,user,members set forums_posts.IdWriter=members.id  where forums_posts.authorid=user.id and members.Username=user.handle" ;
	$updates[] ="CREATE TABLE IF NOT EXISTS `donations` (
  `id` int(11) NOT NULL auto_increment,
  `IdMember` int(11) NOT NULL default '0' COMMENT 'Id of the member (if any)',
  `Email` tinytext collate utf8_unicode_ci NOT NULL COMMENT 'email used by the member if any',
  `StatusPrivate` enum('private','shownameonly','showamountonly','shownameandamount') collate utf8_unicode_ci NOT NULL default 'showamountonly',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'when the donation occurs',
  `Amount` decimal(10,0) NOT NULL COMMENT 'amount of money',
  `Money` varchar(10) collate utf8_unicode_ci NOT NULL COMMENT '$ euros ...',
  `IdCountry` int(11) NOT NULL COMMENT 'country where the member was at donation time',
  `namegiven` text collate utf8_unicode_ci NOT NULL COMMENT 'name given by the user (if any)',
  `referencepaypal` text collate utf8_unicode_ci NOT NULL COMMENT 'paypal reference',
  `membercomment` text collate utf8_unicode_ci NOT NULL COMMENT 'comment of the member if any',
  `SystemComment` text collate utf8_unicode_ci NOT NULL COMMENT 'system comment',
  PRIMARY KEY  (`id`),
  KEY `IdMember` (`IdMember`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This is the table where the history of donation is kept'" ;

	$updates[] ="ALTER TABLE `broadcastmessages` CHANGE `Status` `Status` ENUM( 'ToApprove', 'ToSend', 'Sent', 'Failed' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ToApprove' COMMENT 'Status of the message'" ;
	$updates[] ="CREATE TABLE `members_threads_subscribed` (
`id` INT NOT NULL COMMENT 'primary key',
`IdSubscriber` INT NOT NULL COMMENT 'Id of the member who is subscribing',
`IdThread` INT NOT NULL COMMENT 'Id of the thread the member is subscribing to',
`ActionToWatch` SET( 'replies', 'updates' ) NOT NULL DEFAULT 'replies' COMMENT 'type/scope of subscription',
`UnSubscribeKey` VARCHAR( 20 ) NOT NULL COMMENT 'Key to check when someone click on unsubscribe (to be sure he has ridght to do so)',
`created` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the subscription was created',
PRIMARY KEY ( `id` ) ,
INDEX ( `IdSubscriber` , `IdThread` )
) ENGINE = MYISAM COMMENT = 'This is the table used to store which members has subscribed to which threads'" ;

	$updates[] ="ALTER TABLE `members_threads_subscribed` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT 'primary key'" ;
	$updates[] ="ALTER TABLE `guestsonline` DROP PRIMARY KEY ,ADD PRIMARY KEY ( `IpGuest` , `appearance` ) " ;
	$updates[] ="CREATE TABLE `forum_trads` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'id of the record', `IdLanguage` INT NOT NULL COMMENT 'supposed language of the Sentence', `IdOwner` INT NOT NULL COMMENT 'owner of the record (guy who created it)', `IdTrad` INT NOT NULL COMMENT 'Unique IdTrad (with the IdLa,gauge) for this record', `IdTranslator` INT NOT NULL COMMENT 'Id of the translator', `updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the record was updated', `created` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the record was created', `Type` ENUM('member','translator','admin') NOT NULL COMMENT 'Type of translation', `Sentence` TEXT NOT NULL COMMENT 'Text in one language', `IdRecord` INT NOT NULL COMMENT 'Id of the record in the foreign table', `TableColumn` VARCHAR(200) NOT NULL DEFAULT 'NotSet' COMMENT 'name of the table and field (linked with this record)', INDEX (`IdTrad`) ) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'This table will be used to store translated data from the forum'" ;
	$updates[] ="ALTER TABLE `forums_tags` ADD `IdName` INT NOT NULL DEFAULT '0' COMMENT 'Name of the tag (in forum_trads)',
ADD `IdDescription` INT NOT NULL DEFAULT '0' COMMENT 'Description of the tag (in forum_trads)',
ADD `Type` ENUM( 'Category', 'Member' ) NOT NULL COMMENT 'Type of the tag',
ADD `id` INT NOT NULL COMMENT 'id of the tag (for transition with tagid)'" ;
	$updates[] ="ALTER TABLE `forums_tags` ADD INDEX ( `id` ) " ;
	
	$updates[] ="ALTER TABLE `forums_threads` ADD `id` INT NOT NULL COMMENT 'This is aimed to be the primary key (currently redudnant with threadid)' FIRST ,
ADD `expiredate` TIMESTAMP NULL COMMENT 'When the thread will expire' AFTER `id` " ;
	$updates[] ="ALTER TABLE `forums_threads` ADD `IdTitle` INT NOT NULL DEFAULT '0' COMMENT 'Corresponding record for text title in forum_trads' AFTER `expiredate` " ;
	$updates[] ="ALTER TABLE `forums_posts` ADD `IdContent` INT NOT NULL DEFAULT '0' COMMENT 'Corresponding record for post message in forum_trads' AFTER `message`" ;	
	$updates[] ="ALTER TABLE `forums_posts` ADD `id` INT NOT NULL COMMENT 'id of the posts (this will be the primary key) currently redudant with postid' FIRST " ;
	$updates[] ="ALTER TABLE `forums_posts` ADD INDEX ( `id` ) " ;
	$updates[] ="ALTER TABLE `memberslanguageslevel` CHANGE `Level` `Level` ENUM( 'MotherLanguage', 'Expert', 'Fluent', 'Intermediate', 'Beginner', 'HelloOnly', 'DontKnow' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DontKnow' COMMENT 'level in the language'" ;
	$updates[] ="ALTER TABLE `forums_tags` CHANGE `Type` `Type` ENUM( 'Category', 'Member' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Member' COMMENT 'Type of the tag'" ;
	$updates[] ="ALTER TABLE `previousversion` CHANGE `Type` `Type` ENUM( 'DoneByMember', 'DoneByOtherMember&quot;,&quot;DoneByVolunteer', 'DoneByAdmin', 'DoneByModerator' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DoneByMember'" ;
	
	$updates[] ="CREATE TABLE `members_tags_subscribed` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
`IdSubscriber` int( 11 ) NOT NULL COMMENT 'Id of the member who is subscribing',
`IdTag` int( 11 ) NOT NULL COMMENT 'Id of the tagthe member is subscribing to',
`ActionToWatch` set( 'replies', 'updates' ) NOT NULL default 'replies' COMMENT 'type/scope of subscription',
`UnSubscribeKey` varchar( 20 ) NOT NULL COMMENT 'Key to check when someone click on unsubscribe (to be sure he has ridght to do so)',
`created` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when the subscription was created',
PRIMARY KEY ( `id` ) ,
KEY `IdSubscriber` ( `IdSubscriber` , `IdTag` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8 COMMENT = 'This is the table used to store which members has subscribed to a tag'" ;

	$updates[] ="ALTER TABLE `forums_threads` ADD `stickyvalue` INT NOT NULL DEFAULT '0' COMMENT 'This field is used for sticky thread, default is 0, if negative then it become sticky, the more negative, the one at the top'" ;
	
	$res = mysql_query( "SELECT version FROM dbversion" );

	if (empty($res))
		$version = 0;
	else	
	{
		$row = mysql_fetch_assoc( $res );
		if (!empty($row))
			$version = (int)$row['version'];
		else
			bw_error("Error: Could not retrieve DB version.", true);
	}
	
	assert( isset( $version ) );
	
	while (isset($updates[$version+1]))
	{
		print("updating DB to version ".($version+1)."\n<br>");
	
		if (empty($updates[$version+1]))
			bw_error("The database needs update but it cannot be done automatically. Do the changes manually or get the latest DB from the repository (<a href=\"https://www.bewelcome.org/svn/develstuff/trunk/testdb/\">https://www.bewelcome.org/svn/develstuff/trunk/testdb/</a>).", true);
		
		$qry = sql_query($updates[$version+1]);
		$qry = sql_query("UPDATE dbversion SET version=version+1");
		$version++;
	}
}

?>
