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
	$updates[6] = "select now() "; // This is empty on purpose, this forces manual DB update
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

	$updates[20] = "DELETE FROM user WHERE lastlogin IS NULL" ; 

	$updates[21] = "ALTER TABLE `user` DROP INDEX `handle`" ;

	$updates[22] = "ALTER TABLE `user` ADD UNIQUE ( `handle`)" ;
	
	$updates[23] = "ALTER TABLE `user` DROP INDEX `handle`"; // correct 22, DROPs UNIQUE CONSTRAINT

	$updates[24] = "ALTER TABLE `user` ADD INDEX (`handle`)"; // correct 21
	
	$updates[25] = "INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'Gallery', 'This right is for Gallery managment (to allow to delete or edit other members pictures in the gallery) Avalaible scope are 'All' for all scope 'edit' for editing gallery text 'delete' for allowing to delete other people picture')" ; // To create a right for gallery


	$updates[26] = 'ALTER TABLE `cryptedfields` CHANGE `id` `id` INT( 11 ) NOT NULL' ; 
	$updates[27] = 'SELECT 1+2' ;	 
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
NULL , NOW( ) , 'ForumModerator', 'This is the right needed for forum moderators Various options will be define later for now, only Scope is : 'All' 'Edit' Scope will allow to edit messages'
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
	$updates[] ="ALTER TABLE `previousversion` CHANGE `Type` `Type` ENUM( 'DoneByMember', 'DoneByOtherMember','DoneByVolunteer', 'DoneByAdmin', 'DoneByModerator' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DoneByMember'" ;	
    
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
	$updates[] ="ALTER TABLE `forums_threads` CHANGE `expiredate` `expiredate` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the thread will expire'" ;
    $updates[] ="ALTER TABLE geonames_cache DROP FOREIGN KEY geonames_cache_ibfk_2";
		
    $updates[] ="select now() "; // This to keep the count of versions clean
    $updates[] ="select now() ";
    $updates[] ="select now() ";
    $updates[] ="select now() ";
    $updates[] ="select now() ";
/*
	 $updates[] ="ALTER TABLE forums_threads DROP FOREIGN KEY IF EXISTS forums_threads_ibfk_16";
    $updates[] ="ALTER TABLE forums_threads DROP FOREIGN KEY IF EXISTS forums_threads_ibfk_17";
    $updates[] ="ALTER TABLE forums_threads DROP FOREIGN KEY IF EXISTS forums_threads_ibfk_18";
    $updates[] ="ALTER TABLE forums_threads DROP FOREIGN KEY IF EXISTS forums_threads_ibfk_19";
    $updates[] ="ALTER TABLE forums_threads DROP FOREIGN KEY IF EXISTS forums_threads_ibfk_20";
*/
    $updates[] = 'UPDATE languages SET Name = "English" WHERE id = 0';
    $updates[] = 'UPDATE languages SET EnglishName = CONCAT(UPPER(SUBSTR(EnglishName, 1, 1)), (SUBSTR(EnglishName, 2)))';
    $updates[] = "CREATE TABLE IF NOT EXISTS `meetings` (
  `id` int(11) NOT NULL auto_increment,
  `type` enum('Public','not Public') NOT NULL default 'Public',
  `time` time NOT NULL default '00:00:00',
  `date` date NOT NULL default '0000-00-00',
  `geonameid` int(10) NOT NULL default '333333',
  `name` varchar(80) NOT NULL default '',
  `meetingpoint` varchar(200) NOT NULL default '',
  `contact` varchar(100) default 'keine',
  `begin` time default NULL,
  `location` varchar(80) default NULL,
  `description` text NOT NULL,
  `picture` text default NULL,
  `moreinfolink` text default NULL, 
  `min` int(11) NOT NULL default '2',
  `max` int(11) NOT NULL default '9999',
  `confirmed` int(11) NOT NULL default '0',
  `mostlikely` int(11) NOT NULL default '0',
  `maybe` int(11) NOT NULL default '0',
  `wantbutcant` int(11) NOT NULL default '0',
  `lastinput` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=15 ";
    $updates[] = "INSERT INTO `meetings` (`id`, `type`, `time`, `date`,`geonameid`, `name`, `meetingpoint`, `contact`, `begin`, `location`, `description`, `picture`, `moreinfolink`, `min`, `max`, `confirmed`, `mostlikely`, `maybe`, `wantbutcant`, `lastinput`) VALUES
(1,'Public', '10:00:00', '2008-10-06','333333', 'Movie night1','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(2,'Public', '10:00:00', '2008-05-06','333333', 'Movie night2','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(3,'Public', '10:00:00', '2008-06-06','333333', 'Movie night3','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(4,'Public', '10:00:00', '2008-07-06','333333', 'Movie night4','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(5,'Public', '10:00:00', '2008-08-06','333333', 'Movie night5','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(6,'Public', '23:00:00', '2008-05-01','333333', 'Movie test','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 1, 1, 1, 1, '2008-02-18 11:37:35'),
(7,'Public', '10:00:00', '2008-05-15','333333', 'Movie night2','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 8, 1, 1, 1, '2008-02-18 11:37:35'),
(8,'Public', '10:00:00', '2008-04-31','333333', 'Movie night3','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(9,'Public', '10:00:00', '2008-06-05','333333', 'Movie night4','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 1, 1, 1, 1, '2008-02-18 11:37:35'),
(10,'Public', '11:00:00', '2008-05-06','333333', 'Movie night5','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(11,'Public', '02:00:00', '2008-04-20','333333', 'Movie night5','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(12,'Public', '10:00:00', '2008-04-23','333333', 'Movie night4','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 1, 1, 1, 1, '2008-02-18 11:37:35'),
(13,'Public', '11:00:00', '2008-04-15','333333', 'Movie night5','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35'),
(14,'Public', '01:00:00', '2008-04-16','333333', 'Movie night5','in front of main station','bw member tester','10:30:00','Cinema ABC, Teststreet 300', 'let us watch a film in the ABC cinema', '', '', 2, 7, 3, 1, 1, 1, '2008-02-18 11:37:35')";
    $updates[] = "CREATE TABLE IF NOT EXISTS `membersmeetings` (
  `id` int(11) NOT NULL auto_increment,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `Comment` int(11) NOT NULL COMMENT 'Comment filled by the member',
  `IdMember` int(11) NOT NULL COMMENT 'Id of the concerned member',
  `IdMeeting` int(11) NOT NULL COMMENT 'meeting the member belong to',
  PRIMARY KEY  (`id`),
  KEY `IdMember` (`IdMember`,`IdMeeting`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Describe which members have subscribed to which meeting.'";
    $updates[] = "INSERT INTO `membersmeetings` VALUES (1,'2006-11-24 12:53:37','2006-11-23 19:06:42',305,14,3),(2,'2006-11-24 12:53:37','2006-11-23 19:06:42',305,14,1),(3,'2006-11-24 12:53:37','2006-11-23 19:06:42',305,14,5),(4,'2006-11-24 12:53:37','2006-11-23 19:06:42',305,9,3),(5,'2006-11-24 12:53:37','2006-11-23 19:06:42',305,9,1)";
    

    // introduce chat
    $updates[] =
        "
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'id of the chat message',
    `author_id` INT NOT NULL COMMENT 'who wrote the message',
    `chatroom_id` INT NOT NULL DEFAULT 1 COMMENT 'chatroom of the message. For now we begin with one chatroom only.',
    `updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the message was last modified',
    `created` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the message was sent (added to the DB)',
    `text` TEXT NOT NULL COMMENT 'text content of the message - for now it is raw html',
    INDEX (`chatroom_id`)
)
ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci
COMMENT = 'This table contains the chat messages'
        "
    ;
    $updates[] ="ALTER TABLE `regions` ADD `admin2_code` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `admin1_code` " ;
    $updates[] ="ALTER TABLE `cities` ADD `admin2_code` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `admin1_code` " ;
    
    // new preference for a FeatureObject on the mainpage
    $updates[] =
        "
ALTER TABLE `params`
ADD `ToggleDonateBar` int(11) NOT NULL DEFAULT 0
COMMENT 'This field is used for the donate bar in the teaser, default is 0, if one, it shows up on the mainpage'
        "
    ;
    
    
    $updates[] =   "CREATE TABLE  IF NOT EXISTS `Volunteer_Boards` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'The id of the record',
`Name` VARCHAR( 64 ) NOT NULL COMMENT 'The name of the board (this is an index)',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the board was updated',
`PurposeComment` TEXT NOT NULL COMMENT 'A comment about the purpose of this board',
`TextContent` TEXT NOT NULL COMMENT 'The content of the board',
`created` TIMESTAMP NOT NULL COMMENT 'When the board was created',
UNIQUE (
`Name`
)
) ENGINE = MYISAM COMMENT = 'this is the table which is aimed to store the data for volunteers board'" ;
		
		
    $updates[] =   "CREATE TABLE 
	 `verifiedmembers` ( `id` INT NOT NULL COMMENT 'Id of the record', `updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the record was updated', `created` TIMESTAMP NOT NULL COMMENT 'When the record was created', `IdVerifier` INT NOT NULL COMMENT 'Id of the member who make the verification',
	  `IdVerified` INT NOT NULL COMMENT 'Id of the verified member', `AddressVerified` ENUM('False','True') NOT NULL DEFAULT 'False' 
	  COMMENT 'True if the address was verified', `NameVerified` ENUM('False','True') NOT NULL DEFAULT 'False' COMMENT 'True if the Name was verified', 
	  `Comment` INT NOT NULL COMMENT 'This is a comment (translatable) the verifier can enter', `Type` ENUM('Buggy','Normal',
	  'ApprovedVerifier') NOT NULL DEFAULT 'Buggy' COMMENT 'This is the type of verification (ex : done by an ApprovedVerifier)', 
	  PRIMARY KEY (`id`), INDEX (`IdVerifier`, `IdVerified`), UNIQUE (`Type`) ) 
	  ENGINE = myisam COMMENT = 'In this table are stored information about verified members'" ;
	  
    $updates[] ="INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` ) VALUES (
        NULL , NOW( ) , 'Verifier', 'This right is to be set for members who are Approved Verifier Scope is to be ''ApprovedVerifier'', may be in future more other kind of Verifier will exist. Level is to be set to 1'
    )" ; 
	   
    $updates[] ="ALTER TABLE `verifiedmembers` CHANGE `Type` `Type` ENUM( 'Buggy', 'VerifiedByNormal', 'VerifiedByVerified', 'VerifiedByApproved' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Buggy' COMMENT 'This is the type of verification (ex : done by an ApprovedVerifier)'" ;
	   
    $updates[] ="ALTER TABLE `verifiedmembers` CHANGE `Comment` `Comment` TEXT NOT NULL COMMENT 'This is a comment the verifier can enter'" ;
	   
    $updates[] ="ALTER TABLE `verifiedmembers` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT 'Id of the record'" ;
	   
    $updates[]="ALTER TABLE `verifiedmembers` DROP INDEX `Type` " ;
		 
    $updates[]="INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'SqlForVolunteers', 'This allow the user to execute some specific query using adminquery page. The Scope can be 'All' for all queries or '1';'3';'6' if the user has only rights to execute the specific 1 3 and 6 queries. Nota : in future the specific scope for this query will be granted via the adminquery page'
)" ;
    
    // AUTO_INCREMENT for tb user table
    $updates[] ="
ALTER TABLE `user`
CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT
        "
    ;
	 
// Adminquerie table
    $updates[] ="CREATE TABLE IF NOT EXISTS `sqlforvolunteers` (
  `id` int(11) NOT NULL auto_increment,
  `Name` text collate utf8_unicode_ci NOT NULL COMMENT 'name of the query',
  `Query` text collate utf8_unicode_ci NOT NULL COMMENT 'content of the query',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `param1` text collate utf8_unicode_ci COMMENT 'first optional parameter the user will be prompt for if no empty (the text will be displayed)',
  `param2` text collate utf8_unicode_ci COMMENT 'Second  optional parameter the user will be prompt for if no empty (the text will be displayed)',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='this is a table for queries made for helping volunteers' AUTO_INCREMENT=2 " ;
    $updates[] ="ALTER TABLE `sqlforvolunteers` ADD `LogMe` ENUM( 'False', 'True' ) NOT NULL DEFAULT 'False' COMMENT 'Wether the use of thsi query should be logged or not'" ;

// Creating a view to speed NbMembers by cities update
    $updates[] ="CREATE VIEW NbMembersByCities(IdCity,NbMembers) as select members.IdCity,count(*) 
	 from members  where (members.Status='Active' or members.Status='ChoiceActive' ) group by members.IdCity" ;
	 
	 
    $updates[] ="ALTER TABLE `members` ADD `MonStatus` ENUM( 'MailToConfirm', 'Pending', 'DuplicateSigned', 'NeedMore', 'Rejected', 'CompletedPending', 'Active', 'TakenOut', 'Banned', 'Sleeper', 'ChoiceInactive', 'OutOfRemind', 'Renamed', 'ActiveHidden', 'SuspendedBeta', 'AskToLeave', 'StopBoringMe', 'PassedAway', 'Buggy' ) NOT NULL DEFAULT 'MailToConfirm' COMMENT 'Status of the member (if just subscribed, mail confimed or not, accepted, etc) the usual being Active) ' AFTER `Username` ";
    $updates[] ="UPDATE `members` SET `MonStatus`=`Status` ";
    $updates[] ="ALTER TABLE `members` DROP `Status` ";
    $updates[] ="ALTER TABLE `members` CHANGE `MonStatus` `Status` ENUM( 'MailToConfirm', 'Pending', 'DuplicateSigned', 'NeedMore', 'Rejected', 'CompletedPending', 'Active', 'TakenOut', 'Banned', 'Sleeper', 'ChoiceInactive', 'OutOfRemind', 'Renamed', 'ActiveHidden', 'SuspendedBeta', 'AskToLeave', 'StopBoringMe', 'PassedAway', 'Buggy' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'MailToConfirm' COMMENT 'Status of the member (if just subscribed, mail confimed or not, accepted, etc) the usual being Active) '" ;
     
// creating a table to store data about links between members
	$updates[] = "CREATE TABLE `linklist` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`fromID` INT NOT NULL ,
		`toID` INT NOT NULL ,
		`degree` TINYINT NOT NULL ,
		`rank` TINYINT NOT NULL ,
		`path` VARCHAR( 10000 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
		) ENGINE = MyISAM COMMENT='table holding information about links between members'";

	
// Adding a reference language (the first one used, default to english) for each post
	$updates[] = "ALTER TABLE `forums_posts` 	ADD `IdFirstLanguageUsed` INT NOT NULL DEFAULT '0'
	COMMENT 'This is the id of the first language used for this post, which allows to consider the \"reference language\" for it'";

// Adding a reference language (the first one used, default to english) for each post
	$updates[] = "ALTER TABLE `forums_threads` 	ADD `IdFirstLanguageUsed` INT NOT NULL DEFAULT '0'
	COMMENT 'This is the id of the first language used for this thread title, which allows to consider the \"reference language\" for this forum title'";

// // Adding a new preference for the link system 

	$updates[] = "select now() ";
	$updates[] = "select now() "; //please add the following manually to the table preferences
	
	/**
	"INSERT INTO `bewelcome`.`preferences` (
			`id` ,
			`codeName` ,
			`codeDescription` ,
			`Description` ,
			`created` ,
			`DefaultValue` ,
			`PossibleValues` ,
			`EvalString` ,
			`Status`
			) VALUES (
			 NULL ,
			 'PreferenceLinkPrivacy',
			 'PreferenceLinkPrivacyDesc',
			 '
			    Allow a member to choose if he wants to appear in connections between members.
			    Defaults to hidden which results in not showing his picture / name
			    but still computing the connection while no would completely
			    remove the member from all connections between members
			 ',
			 NOW( ) ,
			 'hidden',
			 'yes,hidden,no',
			 '
				'echo "<select name=PreferenceLinkPrivacy class=\"prefsel\">" ;
				echo "<option value=yes " ;
				if ($Value=="yes") echo " selected " ;
				echo ">",ww("Yes"),"</option>" ;
				echo "<option value=hidden" ;
				if ($Value=="hidden") echo " selected " ;
				echo ">",ww("Hidden"),"</option>" ;
				echo "<option value=no" ;
				if ($Value=="no") echo " selected " ;
				echo ">",ww("No"),"</option>" ;
				echo "</select>" ;'
			 ',
			 'Normal'
			)";
			
		**/

	$updates[] = "select now() "; // adding other void query to be sure the counter keep Sync
	$updates[] = "select now() "; // adding other void query to be sure the counter keep Sync


	$updates[] = "ALTER TABLE `groups` ADD `DisplayedOnProfile` 
	ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'Yes' COMMENT 'State wether the membership in this group is displayed in the profile'";
	$updates[] = "ALTER TABLE `words` ADD `TranslationPriority` INT NOT NULL 
	DEFAULT '5' COMMENT 'Priority to give for the translation 1=to urgent, 10 lesser emergency'";
	
		$updates[] = "ALTER TABLE `forums_threads` ADD `IdGroup` INT NOT NULL DEFAULT '0' COMMENT 'Optional Id of the group the thread depens on'" ;
		$updates[] = "ALTER TABLE `forums_posts` ADD `Visibility` ENUM('NoRestriction','MembersOnly','GroupOnly','ModeratorOnly') NOT NULL DEFAULT 'NoRestriction' COMMENT 'Visibility for this post (supersed thread visibility)'" ;
		$updates[] = "ALTER TABLE `forums_threads` ADD `WhoCanReply` ENUM( 'MembersOnly', 'GroupMembersOnly', 'ModeratorsOnly' ) NOT NULL DEFAULT 'MembersOnly' COMMENT 'Who is allowed to reply in this thread'" ;
		$updates[] = "INSERT INTO `flags` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'NotAllowToPostInForum', 'If this Flag is set for a member, he will not be able to post in the forum'
), (
NULL , NOW( ) , 'NotAllowToWriteInChat', 'If this Flag is set for a member, he will not be able to write in the chat'
)" ; 

		$updates[] = "CREATE TABLE `geonames_alternate_names` (
			`alternateNameId` INT NOT NULL ,
			`geonameId` INT NOT NULL ,
			`isoLanguage` VARCHAR( 7 ) NOT NULL ,
			`alternateName` VARCHAR( 200 ) NOT NULL ,
			`isPreferredName` BINARY NOT NULL DEFAULT '0',
			`isShortName` BINARY NOT NULL DEFAULT '0',
			PRIMARY KEY ( `alternateNameId` ) ,
			INDEX ( `geonameId` )
			) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'table in geonames format holding translated names for geographic places'";
			
		$updates[] = "ALTER TABLE `geonames_cache` ADD `fclass` VARCHAR(1) NULL, ADD `fcode` VARCHAR( 10 ) NULL, ADD `fk_admin2code` VARCHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
			ADD `timezone` INT NULL ";
			
		$updates[] = " ALTER TABLE `geonames_cache` CHANGE `fk_admincode` `fk_admin1code` CHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  ";
		
		$updates[] = "CREATE TABLE `geonames_timezones` (
						`TimeZoneId` INT NOT NULL ,
						`OffsetJanuary` DECIMAL NOT NULL ,
						`OffsetJuly` DECIMAL NOT NULL ,
						PRIMARY KEY ( `TimeZoneId` )
						) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'geonames based list of timezones'";
						
						
		$updates[] = "INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'SafetyTeam', 'This gives specific right for the safety team It provides : - a link to updatemandatory on each profile - the capability to see any profile regardless its status'
)" ;

		$updates[] = "CREATE TABLE `geo_location` (
						`locationId` INT NOT NULL AUTO_INCREMENT ,
						`latitude` DOUBLE NOT NULL ,
						`longitude` DOUBLE NOT NULL ,
						`name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
						UNIQUE (
						`locationId`
						)
						) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'contains lat/long/name information for arbitrary locations not in geonamesDB'";
						
		$updates[] = " CREATE TABLE `geo_hierarchy` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`geoId` INT NOT NULL COMMENT 'can be geonameId or locationId',
						`parentId` INT NOT NULL COMMENT 'geonameId of the parent region',
						`comment` VARCHAR( 255 ) NULL ,
						INDEX ( `geoId` , `parentId` ) ,
						UNIQUE (
						`id`
						)
						) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'table to store the hierarchy of geographic elements' ";
		$updates[] = "ALTER TABLE `geonames_cache` DROP `fk_admin2code` ";
		$updates[] = " ALTER TABLE `geonames_cache` CHANGE `fk_admin1code` `fk_admincode` CHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ";
		$updates[] = "CREATE TABLE `geo_usage` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`geoId` INT NOT NULL COMMENT 'geonameId or locationId',
						`typeId` INT NOT NULL COMMENT 'id specifying the usage type, eg member, blog or gallery',
						`count` INT NOT NULL COMMENT 'counts the number of references of type typeId to ths geoId',
						INDEX ( `geoId` , `typeId` ) ,
						UNIQUE (
						`id`
						)
						) ENGINE = innodb COMMENT = 'table to keep track how often a geoId is used by a certain type (eg, member, ..)'";
		$updates[] = "CREATE TABLE `geo_type` (
						`id` INT NOT NULL AUTO_INCREMENT COMMENT 'typeId',
						`name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'short name to specify the usage type for geo information , eg member',
						`description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
						UNIQUE (
						`id`
						)
						) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = 'table to differentiate between different types of georeferenced information'";
		$updates[] = "CREATE TABLE `geonames_cache_backup` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `geonameid` int(10) unsigned NOT NULL,
						  `latitude` double NOT NULL,
						  `longitude` double NOT NULL,
						  `name` varchar(200) NOT NULL,
						  `population` int(10) unsigned NOT NULL,
						  `fclass` varchar(1) default NULL,
						  `fcode` varchar(10) default NULL,
						  `fk_countrycode` char(2) NOT NULL,
						  `fk_admincode` char(2) default NULL,
						  `timezone` int(11) default NULL,
						  `date_updated` date NOT NULL,
						  PRIMARY KEY  (`id`),
						  KEY `geonameid` (`geonameid`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8";

							
			
		$updates[] = "CREATE TABLE `members_groups_subscripted` (
`id` INT NOT NULL ,
`IdSubscriber` INT NOT NULL COMMENT 'Id of the member who is subscribing',
`IdGroup` INT NOT NULL COMMENT 'Id of the group the member is subscribing to',
`ActionToWatch` ENUM( 'replies', 'updates' ) NOT NULL DEFAULT 'replies' COMMENT 'type/scope of subscription',
`UnSubscribeKey` VARCHAR( 20 ) NOT NULL COMMENT 'Key to check when someone click on unsubscribe (to be sure he has right to do so)',
`created` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the subscription was created',
PRIMARY KEY ( `id` )
) ENGINE = MYISAM COMMENT = 'This is the table where are stored the members who are subscribing to a group'" ;


		$updates[] = "RENAME TABLE `members_groups_subscripted`  TO `members_groups_subscribed`" ;
		$updates[] = "ALTER TABLE `geonames_cache` DROP FOREIGN KEY `geonames_cache_ibfk_1` ";	
		$updates[] = "INSERT INTO `geo_type` (
						`id` ,
						`name` ,
						`description`
						)
						VALUES (
						NULL , 'member_primary', 'location referring to the pimarry address of a member'
						), (
						NULL , 'member_secondary', 'location referring to secondary address(es) of a member'
						), (
						NULL , 'trip', 'location to georeference a trip stop'
						), (
						NULL , 'gallery', 'location georeferencing an intem of the gallery (most likely a photo)'
						)";
		$updates[] = "ALTER TABLE `geonames_cache_backup` ADD `parentid` INT NULL COMMENT 'former parent Id '";
		
		$updates[] = "ALTER TABLE `geonames_cache` ADD `parentAdm1Id` INT NOT NULL COMMENT 'geonameId of the parent Adm1 region'";
		$updates[] = "ALTER TABLE `geonames_cache_backup` ADD `parentAdm1Id` INT NOT NULL COMMENT 'geonameId of the parent Adm1 region'";
		$updates[] = "ALTER TABLE `geonames_cache` ADD `parentCountryId` INT NOT NULL COMMENT 'geonameId of the parent country'";
		$updates[] = "ALTER TABLE `geonames_cache_backup` ADD `parentCountryId` INT NOT NULL COMMENT 'geonameId of the parent country'";	



// Switching to view for the cities table

		$updates[] = "ALTER TABLE `cities`  COMMENT = 'Old Previous cities table, now it is a view'" ; 

		$updates[] = "RENAME TABLE `cities`  TO `old_t_cities_old_BW`" ;

		$updates[] = "create algorithm=TEMPTABLE view

cities(id,NbMembers,Name,ansiname,OtherNames,latitude,longitude,
feature_class,feature_code,country_code,
population,
IdRegion,ActiveCity,IdCountry)

AS

select `gc`.`geonameid` ,0 ,`gc`.`name`,`gc`.`name`,`gc`.`name` ,`gc`.`latitude` ,`gc`.`longitude` ,
`gc`.`fclass` ,`gc`.`fcode` ,`gc`.`fk_countrycode` ,
`gc`.`population` ,
`gc`.`parentAdm1Id`,'True',`gc`.`parentCountryId` 

from `geonames_cache` as `gc`" ;
 
		$updates[] = "CREATE TABLE `reports_to_moderators` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the report was updated', `created` TIMESTAMP NOT NULL COMMENT 'when the report was created', `ReporterComment` TEXT NOT NULL COMMENT 'Comment of the reporter', `IdReporter` INT NOT NULL COMMENT 'Id Of the reporter', `ModeratorComment` TEXT NOT NULL COMMENT 'Moderator comment', `IdModerator` INT NOT NULL COMMENT 'Id Of the moderator', `Status` ENUM('Open','OnDiscussion','Closed') NOT NULL DEFAULT 'Open' COMMENT 'Status of the report', `IdPost` INT NOT NULL COMMENT 'Concerned post', `IdThread` INT NOT NULL COMMENT 'Concerned ', `Type` ENUM('SeeText','AllowMeToEdit','Insults','RemoveMyPost') NOT NULL COMMENT 'Type of report', INDEX (`IdReporter`, `IdPost`, `IdThread`) ) ENGINE = myisam COMMENT = 'This table is used to report comments from members to moderator'" ;


		$updates[] = "ALTER TABLE `forums_posts` ADD `OwnerCanStillEdit` ENUM( 'Yes','No') NOT NULL DEFAULT 'Yes' COMMENT 'Wether the user can still edit this post' AFTER `IdContent`" ;

		$updates[] = "CREATE TABLE `counters_cities_nbmembers` (
`IdCity` INT NOT NULL DEFAULT '0' COMMENT 'This is the id of the corresponding city in the cities view table',
`NbMembers` INT NOT NULL DEFAULT '0' COMMENT 'Current number of members in this city (redudancy, it is computed)',
PRIMARY KEY ( `IdCity` )
) ENGINE = MYISAM COMMENT = 'Performances issue : This is a counter table, the content is built by progam. '" ;

		$updates[] = "CREATE TABLE `counters_regions_nbmembers` (
`IdRegion` INT NOT NULL DEFAULT '0' COMMENT 'This is the id of the corresponding region in the regions view table',
`NbMembers` INT NOT NULL DEFAULT '0' COMMENT 'Current number of members in this region (redudancy, it is computed)',
PRIMARY KEY ( `IdRegion` )
) ENGINE = MYISAM COMMENT = 'Performances issue : This is a counter table, the content is built by progam. '" ;
						
		$updates[] = "CREATE TABLE `counters_regions_nbcities` (
`IdRegion` INT NOT NULL DEFAULT '0' COMMENT 'This is the id of the corresponding region in the regions view table',
`NbCities` INT NOT NULL DEFAULT '0' COMMENT 'Current number of cities in this region (redudancy, it is computed)',
PRIMARY KEY ( `IdRegion` )
) ENGINE = MYISAM COMMENT = 'Performances issue : This is a counter table, the content is built by progam. '" ;
						
						
		$updates[] = "CREATE TABLE `recorded_usernames_of_left_members` (
`UsernameNotToUse` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'This is the Username',
PRIMARY KEY ( `UsernameNotToUse` )
) ENGINE = MYISAM COMMENT = 'this table is used to store the username of the members who have left BW'" ;						

		$updates[] = "replace into recorded_usernames_of_left_members(UsernameNotToUse) select Username from members where Status in ('AskToLeave','TakenOut')" ;
	$res = mysql_query( "SELECT version FROM dbversion" );
	
		$updates[] = "ALTER TABLE `members` ADD `NbRemindWithoutLogingIn` INT NOT NULL DEFAULT '0' COMMENT 'This counter stores the number of time a member has been reminded to use BeWelcome'" ;

		$updates[] = "ALTER TABLE `broadcast` CHANGE `Type` `Type` ENUM( 'Normal', 'RemindToLog' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Normal' COMMENT 'Normal or Reinder to logs in'" ;
	
        $updates[] = "CREATE TABLE `shouts` (
`id` INT( 10 ) NOT NULL DEFAULT '0',
`member_id_foreign` INT( 10 ) NOT NULL DEFAULT '0',
`table` VARCHAR( 75 ) NOT NULL ,
`table_id` INT( 10 ) NOT NULL DEFAULT '0',
`created` DATETIME NOT NULL ,
`title` VARCHAR( 75 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`text` MEDIUMTEXT NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_general_ci";
	
	
	
        $updates[] = "	CREATE TABLE `chat_rooms_members` (
`IdRoom` INT NOT NULL COMMENT 'The room where the member is',
`IdMember` INT NOT NULL COMMENT 'The Id of the member',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the members came in',
`created` TIMESTAMP NOT NULL COMMENT 'When the members refresh the room'
) ENGINE = MYISAM COMMENT = 'This table store the presence of members in chat roooms'" ;

        $updates[] = "	ALTER TABLE `chat_rooms_members` ADD PRIMARY KEY ( `IdRoom` , `IdMember` ) " ;


        $updates[] = "	CREATE TABLE `groups_locations` (
`id` INT NOT NULL COMMENT 'unique Id of the record',
`IdGroupMembership` INT NOT NULL COMMENT 'Id of the groupmebership we are refering too',
`IdLocation` INT NOT NULL COMMENT 'for now it is an IdCity or IdCOuntry, but in future it will be any geonameid',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when it was updated',
`AdminComment` TEXT NOT NULL COMMENT 'Dont yet know what it will be used for',
`created` TIMESTAMP NOT NULL COMMENT 'when it was created',
`MemberComment` INT NOT NULL COMMENT 'an (future) additiobal comment of the member (?)'
) ENGINE = MYISAM COMMENT = 'Allows to link a MemberShip in a group to some location (local vols !)'" ;	


        $updates[] = "	ALTER TABLE `groups_locations` ADD INDEX ( `IdGroupMembership` ) " ; 
				
        $updates[] = "	ALTER TABLE `groups_locations` ADD INDEX ( `IdLocation` ) " ;
				
        $updates[] = "  ALTER TABLE `groups_locations` ADD PRIMARY KEY ( `IdGroupMembership` , `IdLocation` )  " ;
				
				$updates[] = "ALTER TABLE `params` ADD `MailToNotifyWhenNewMemberSignup` TEXT NOT NULL COMMENT 'these are the mail addresses which receive notification about new people who have signup'" ;   

				$updates[] = "ALTER TABLE `params` ADD `FeatureForumClosed` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'This will allow to close the forum',
ADD `FeatureAjaxChatClosed` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'This will allow to close the Ajax chat',
ADD `FeatureSignupClose` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'This will allow to close the Signup'" ;

				$updates[] = "ALTER TABLE `params` ADD `AjaxChatSpecialAllowedList` TEXT NOT NULL COMMENT 'This is a special list of username for admin/prog able to use chat even if it is closed'" ;

				$updates[] = "ALTER TABLE `params` ADD `ReloadRightsAndFlags` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'Must be set to No, if Yes force the reload of Flags and Rights for online users'" ;
                $updates[] = "ALTER TABLE membersgroups MODIFY Status ENUM('In','WantToBeIn','Kicked') NOT NULL DEFAULT 'WantToBeIn' COMMENT 'status of appliance some group need an appliance'";



				$updates[] = "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls` (
  `id` int(11) NOT NULL auto_increment COMMENT 'Primary key',
  `IdCreator` int(11) NOT NULL default '0' COMMENT 'Id of the member(if any) who created the poll',
  `IdGroupCreator` int(11) NOT NULL default '0' COMMENT 'Id of the group(if any) who created the poll ',
  `Status` enum('Project','Open','Close') NOT NULL default 'Project' COMMENT 'Status of the poll',
  `ResultsVisibility` enum('Not Visible','Visible','VisibleAfterVisit') NOT NULL default 'VisibleAfterVisit' COMMENT 'Wether the results are  visible or not (usually we wait for the end of the poll)',
  `Type` enum('MemberPoll','OfficialPoll','OfficialVote') NOT NULL default 'MemberPoll' COMMENT 'The kind of poll',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'When teh poll was updated',
  `Started` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'When the poll will start',
  `Ended` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'When the poll will close',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'when the poll was created',
  `Title` int(11) NOT NULL COMMENT 'Title of the poll, this is a forum_trad',
  `ForMembersOnly` enum('Yes','No') NOT NULL default 'Yes' COMMENT 'define if only members can contribute to this poll',
  `IdLocationsList` int(11) NOT NULL default '0' COMMENT 'for future, Id of a location list for people who are allowed to contribute to this poll',
  `IdGroupsList` int(11) NOT NULL default '0' COMMENT 'for future, Id of a group list for people who are allowed to contribute to this poll',
  `IdCountriesList` int(11) NOT NULL default '0' COMMENT 'for future, Id of a country list for people who are allowed to contribute to this poll',
  `OpenToSubGroups` enum('Yes','No') NOT NULL default 'Yes' COMMENT 'wether subgroups of teh group list (if any) can contribute ',
  `TypeOfChoice` enum('Exclusive','Inclusive','Ordered') NOT NULL COMMENT 'Type of the possibel choice for answer (one, several, sorted)',
  `CanChangeVote` enum('Yes','No') NOT NULL default 'No' COMMENT 'State wether member can change their votes for this poll',
  `AllowComment` enum('Yes','No') NOT NULL default 'No' COMMENT 'wether the contributor can make an additional comment or not',
  `Description` int(11) NOT NULL COMMENT 'This is a forum_trad which allows to describe the poll',
  `WhereToRestrictMember` text NOT NULL COMMENT 'This can be a SQL text usable to restrict the member allow to contribute to the poll',
  `Anonym` enum('Yes','No') NOT NULL default 'Yes' COMMENT 'wether the poll is made anonymously',
  PRIMARY KEY  (`id`),
  KEY `IdCreator` (`IdCreator`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Polls main table, allow to describe the main characteristics'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_choices` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `IdPoll` int(11) NOT NULL COMMENT 'Id of the poll',
  `IdChoiceText` int(11) NOT NULL COMMENT 'forum_trad Id',
  `Counter` int(11) NOT NULL default '0' COMMENT 'counter of choice for this record',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'when it was updated',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'when it was updated',
  PRIMARY KEY  (`id`),
  KEY `IdPoll` (`IdPoll`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='This is the table for a certain poll possible choices'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_choices_hierachy` (
  `IdPollChoice` int(11) NOT NULL COMMENT 'This is the choice this records refers to',
  `HierarchyValue` int(11) NOT NULL default '0' COMMENT 'This is the hierarchy value',
  `Counter` int(11) NOT NULL default '0' COMMENT 'This store the number of time this choice was made',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'when it was updated',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'when it was updated',
  PRIMARY KEY  (`IdPollChoice`,`HierarchyValue`),
  KEY `IdPollChoice` (`IdPollChoice`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='This table is used to store counters results for poll choice'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_contributions` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `IdMember` int(11) NOT NULL default '0' COMMENT 'Id of the member if not anonym',
  `Email` tinytext NOT NULL COMMENT 'email of the external contributor (if open to not logged people)',
  `EmailIsConfirmed` enum('Yes','No') NOT NULL default 'No' COMMENT 'State wether the email is confirmed or not',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'when teh record was updated',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'when the record was created',
  `comment` text NOT NULL COMMENT 'possible common (depend on option polls.AllowComment)',
  `IdPoll` int(11) NOT NULL COMMENT 'reference of the poll',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `IdMember` (`IdMember`),
  KEY `idEmail` (`Email`(6)),
  KEY `IdPoll` (`IdPoll`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Here are stored the contribution to the poll'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_list_allowed_countries` (
  `IdPoll` int(11) NOT NULL COMMENT 'id of the poll',
  `IdCountry` int(11) NOT NULL COMMENT 'id of the country',
  KEY `IdPoll` (`IdPoll`,`IdCountry`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='This table allows to define a restrictive list of countries '";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_list_allowed_groups` (
  `IdPoll` int(11) NOT NULL COMMENT 'id of the poll',
  `IdGroup` int(11) NOT NULL COMMENT 'id of the group',
  KEY `IdPoll` (`IdPoll`,`IdGroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='This table allows to define a restrictive list of groups all'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_list_allowed_locations` (
  `IdPoll` int(11) NOT NULL COMMENT 'id of the poll',
  `IdLocation` int(11) NOT NULL COMMENT 'id of the location',
  KEY `IdPoll` (`IdPoll`,`IdLocation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='This table allows to define a restrictive list of location a'";



				$updates[] = "CREATE TABLE IF NOT EXISTS `polls_record_of_choices` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'when the record was updated',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'when the record was created',
  `IdPoll` int(11) NOT NULL default '0' COMMENT 'I of the poll (needed because of blank vote)',
  `IdPollChoice` int(11) NOT NULL COMMENT 'the id of the choice made',
  `HierarchyValue` int(11) NOT NULL COMMENT 'Thi is use to store the hierarchy value (if it is a hierarchic choice)',
  `IdMember` int(11) NOT NULL COMMENT 'Id of the member (if logged in)',
  `Email` tinytext NOT NULL COMMENT 'email of the contributor (if not logged in)',
  PRIMARY KEY  (`id`),
  KEY `IdMember` (`IdMember`),
  KEY `idEmail` (`Email`(6))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='if the poll is not anonym, this table will be use to store t'";

				$updates[] = "INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'Poll', 'This is the right which allow to admin poll Possible Scope are : create : which allow to create a poll update : which allows to update a poll -regardless of its status-'
)" ;
				$updates[] = "ALTER TABLE `polls_contributions` DROP INDEX `IdMember`  " ;
				$updates[] = "ALTER TABLE `polls_contributions` ADD UNIQUE `MemberContribUnique` ( `IdMember` , `IdPoll` )" ;
				
				$updates[] = "ALTER TABLE `chat_rooms_members` ADD `LastWrite` TIMESTAMP NOT NULL COMMENT 'when teh user in th room did his last write'" ;
				
				$updates[] = "ALTER TABLE `params` ADD `AjaxChatDebuLevel` INT NOT NULL DEFAULT '0' COMMENT 'If greater than 0 this value allow to add debug logs in the AjaxChat ' AFTER `AjaxChatSpecialAllowedList` " ;
				$updates[] = "ALTER TABLE `chat_rooms_members` ADD `CountActivity` INT NOT NULL DEFAULT '0' COMMENT 'Number of loop (ie with room window open) for this member'" ;

				$updates[] = "CREATE FUNCTION Next_Forum_trads_IdTrad ()   RETURNS INT  DETERMINISTIC
    BEGIN
     DECLARE res INT;
		 select max(IdTrad)+1 from forum_trads into res ;
     RETURN res;
    END" ;

    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";
    $updates[] = "SELECT NOW()";

    $updates[] = "DROP TABLE IF EXISTS roles, roles_privileges, privileges, members_roles, privilegescopes";

    $updates[] = <<<SQL
CREATE TABLE roles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary key for roles',
    name VARCHAR(128) NOT NULL UNIQUE COMMENT 'Name of the role, must be unique',
    description VARCHAR(256) NOT NULL COMMENT 'Short description of role'
) ENGINE=InnoDB COMMENT 'The roles part of the rights system'
SQL;
    $updates[] = <<<SQL
CREATE TABLE privileges (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary key for privileges',
    controller VARCHAR(64) NOT NULL COMMENT 'Name of the controller the privilege ties to',
    method VARCHAR(64) NOT NULL COMMENT 'Name of the controllers method the privilege ties to',
    type VARCHAR(64) NULL COMMENT 'Type of the object for which the privilege can be scoped, if any',
    CONSTRAINT UNIQUE controller_method (controller, method)
) ENGINE=InnoDB COMMENT 'The privileges part of the rights system'
SQL;
    $updates[] = <<<SQL
CREATE TABLE roles_privileges (
    IdRole INT NOT NULL COMMENT 'Foreign key to the roles table',
    IdPrivilege INT NOT NULL COMMENT 'Foreign key to the privileges table',
    updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PRIMARY KEY role_privilege (IdRole, IdPrivilege)
) ENGINE=InnoDB COMMENT 'N-to-N table for roles and privileges'
SQL;
    $updates[] = <<<SQL
CREATE TABLE members_roles (
    IdMember INT NOT NULL COMMENT 'Foreign key to the members table',
    IdRole INT NOT NULL COMMENT 'Foreign key to the roles table',
    updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PRIMARY KEY role_privilege (IdMember, IdRole)
) ENGINE=InnoDB COMMENT 'N-to-N table for members and roles'
SQL;
    $updates[] = <<<SQL
CREATE TABLE privilegescopes (
    IdMember INT NOT NULL COMMENT 'Foreign key to the members table',
    IdRole INT NOT NULL COMMENT 'Foreign key to the roles table',
    IdPrivilege INT NOT NULL COMMENT 'Foreign key to the privileges table',
    IdType VARCHAR(32) NOT NULL COMMENT 'Id of the scope - either and id or *',
    updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PRIMARY KEY scopeid (IdMember, IdRole, IdPrivilege)
) ENGINE=InnoDB COMMENT 'Lookup table to check the scope of privileges'
SQL;
    $updates[] = <<<SQL
INSERT INTO privileges (controller, method) VALUES ('*', '*')
SQL;
    $updates[] = <<<SQL
INSERT INTO roles (name, description) VALUES ('SysAdmin', 'The omnipotent role of the sysadmin - can do everything')
SQL;
    $updates[] = <<<SQL
INSERT INTO roles_privileges (IdRole, IdPrivilege) VALUES (1,1)
SQL;
    $updates[] = <<<SQL
INSERT INTO members_roles (IdMember, IdRole) VALUES (1,1)
SQL;
    $updates[] = <<<SQL
INSERT INTO privilegescopes (IdMember, IdRole, IdPrivilege, IdType) VALUES (1,1,1,'*')
SQL;

	$updates[] = "DROP FUNCTION IF EXISTS Next_Forum_trads_IdTrad";
	$updates[] = "CREATE FUNCTION Next_Forum_trads_IdTrad ()   RETURNS INT  DETERMINISTIC
    BEGIN
     DECLARE res INT;
		 select max(IdTrad)+1 from forum_trads into res ;
     RETURN res;
    END" ;

    $updates[] = "DROP TABLE IF EXISTS chat_rooms_members";

    $updates[] = "	CREATE TABLE `chat_rooms_members` (
`IdRoom` INT NOT NULL COMMENT 'The room where the member is',
`IdMember` INT NOT NULL COMMENT 'The Id of the member',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the members came in',
`created` TIMESTAMP NOT NULL COMMENT 'When the members refresh the room'
) ENGINE = MYISAM COMMENT = 'This table store the presence of members in chat roooms'" ;

    $updates[] = "	ALTER TABLE `chat_rooms_members` ADD PRIMARY KEY ( `IdRoom` , `IdMember` ) " ;

	$updates[] = "ALTER TABLE `chat_rooms_members` ADD `LastWrite` TIMESTAMP NOT NULL COMMENT 'when teh user in th room did his last write'" ;
				
	$updates[] = "ALTER TABLE `chat_rooms_members` ADD `CountActivity` INT NOT NULL DEFAULT '0' COMMENT 'Number of loop (ie with room window open) for this member'" ;

    $updates[] = "ALTER TABLE `chat_rooms_members` ADD `LastRefresh` TIMESTAMP NOT NULL COMMENT 'when the member has refreshed his room window for the last time' AFTER `LastWrite` " ;

    $updates[] = "DELETE FROM forum_trads WHERE IdLanguage = 0 AND IdOwner = 1 AND Sentence = 'This is the main room chat room for BeWelcome where anybody can talk and start to get contact with people' AND TableColumn = 'chat_rooms.RoomDescription'";
    $updates[] = "DELETE FROM forum_trads WHERE IdLanguage = 0 AND IdOwner = 1 AND Sentence = 'Main Room' AND TableColumn = 'chat_rooms.RoomTitle'";

    $updates[] = "insert into forum_trads(IdLanguage,IdOwner,IdTrad,IdTranslator,created,Type,Sentence,IdRecord,TableColumn) 
		values(0,1,Next_Forum_trads_IdTrad(),1,now(),'admin','Main Room',1,'chat_rooms.RoomTitle')" ;
		
    $updates[] = "insert into forum_trads(IdLanguage,IdOwner,IdTrad,IdTranslator,created,Type,Sentence,IdRecord,TableColumn) 
		values(0,1,Next_Forum_trads_IdTrad(),1,now(),'admin','This is the main room chat room for BeWelcome where anybody can talk and start to get contact with people',1,'chat_rooms.RoomDescription')" ;

    $updates[] = "DROP TABLE IF EXISTS chat_rooms";

    $updates[] = "CREATE TABLE `chat_rooms` (
  `id` int(11) NOT NULL auto_increment COMMENT 'if of the room',
  `RoomTitle` int(11) NOT NULL COMMENT 'This is a forum trad (this title will be used in the header or the room web page)',
  `RoomDescription` int(11) NOT NULL COMMENT 'This is a forum_trad, will be used to describe the purpose of the room',
  `IdRoomOwner` int(11) NOT NULL COMMENT 'This is the member owning the room',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'when the record was updated',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'when the record was created',
  `RoomStatus` enum('Open','Close') NOT NULL default 'Open' COMMENT 'Wether the room is open or Closed',
  `RoomType` enum('Public','GroupDedicated','Private') NOT NULL default 'Private' COMMENT 'Type of the room',
  `IdGroupOwner` int(11) NOT NULL default '0' COMMENT 'Optional group Id for room with Type GroupDedicated',
  `RefreshIntervall` int(11) NOT NULL default '4500' COMMENT 'This is the refresh intervall for the room in second',
  PRIMARY KEY  (`id`),
  KEY `IdRoomOwner` (`IdRoomOwner`,`IdGroupOwner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='This is the table aimed to describe the possible chat rooms' " ;


    $updates[] = "insert into chat_rooms(id,IdRoomOwner,created,RoomStatus,RoomType) values(1,1,now(),'Open','Public')" ; 
		
    $updates[] = "update chat_rooms set RoomTitle=(select IdTrad from forum_trads where IdRecord=1 and TableColumn='chat_rooms.RoomTitle') where chat_rooms.id=1" ;  
    $updates[] = "update chat_rooms set RoomDescription=(select IdTrad from forum_trads where IdRecord=1 and TableColumn='chat_rooms.RoomDescription') where chat_rooms.id=1" ;  

    $updates[] = "INSERT INTO privileges (controller, method) VALUES ('RightsController', '*')";
		
		$updates[] = "ALTER TABLE `chat_messages` CHANGE `author_id` `IdAuthor` INT( 11 ) NOT NULL COMMENT ' who wrote the message' " ;
		$updates[] = "ALTER TABLE `chat_messages` CHANGE `chatroom_id` `IdRoom` INT( 11 ) NOT NULL COMMENT 'chatroom of the message.' " ;
		$updates[] = "ALTER TABLE `chat_rooms_members` ADD `StatusInRoom` ENUM( 'Invited', 'Banned' ) NOT NULL DEFAULT 'Invited' COMMENT 'This is the status of the member in the room, it can be used to ban a member from a room' AFTER `updated` ";
    $updates[] = "ALTER TABLE privilegescopes DROP PRIMARY KEY, ADD CONSTRAINT PRIMARY KEY (IdMember, IdRole, IdPrivilege, IdType)";

    $updates[] = "INSERT INTO roles (name, description) VALUES ('GroupOwner', 'Privileges for group owners')";
    $updates[] = "ALTER TABLE privileges DROP COLUMN type, ADD type VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Type of the object for which the privilege can be scoped, if any', DROP INDEX controller_method, ADD CONSTRAINT UNIQUE (controller, method, type)";
    $updates[] = "INSERT INTO privileges (controller, method, type) VALUES ('GroupsController', '*', 'Group')";
    $updates[] = "INSERT INTO roles_privileges (IdRole, IdPrivilege) VALUES ((SELECT id FROM roles WHERE name = 'GroupOwner'), (SELECT id FROM privileges WHERE controller = 'GroupsController' AND method = '*' AND type = 'Group'))";
    $updates[] = <<<SQL
ALTER TABLE groups
ADD COLUMN VisiblePosts ENUM ('no', 'yes') DEFAULT 'yes' NOT NULL COMMENT 'If the groups posts should be displayed in the public forum',
MODIFY COLUMN Type ENUM ('Public', 'NeedAcceptance', 'NeedInvitation') NOT NULL DEFAULT 'Public' COMMENT 'Defines how public group is'
SQL;
    $updates[] =<<<SQL
ALTER TABLE groups
ADD COLUMN IdDescription INT COMMENT 'Foreign key to memberstrads table'
SQL;

    $updates[] ="drop view cities ";
    $updates[] ="CREATE ALGORITHM=MERGE  VIEW `cities` AS 
select `gc`.`geonameid` AS `id`,`geo_usage`.`count` AS `NbMembers`,`gc`.`name` AS `Name`,`gc`.`name` AS `ansiname`,`gc`.`name` AS `OtherNames`,`gc`.`latitude` AS `latitude`,`gc`.`longitude` AS `longitude`,`gc`.`fclass` AS `feature_class`,`gc`.`fcode` AS `feature_code`,`gc`.`fk_countrycode` AS `country_code`,`gc`.`population` AS `population`,`gc`.`parentAdm1Id` AS `IdRegion`,_utf8'True' AS `ActiveCity`,`gc`.`parentCountryId` AS `IdCountry` 
from (`geonames_cache` `gc` join `geo_usage`) where ((`geo_usage`.`geoId` = `gc`.`geonameid`) and (`geo_usage`.`typeId` = 1) and `gc`.`fclass`='P')";

    $updates[] ="select now()" ;
    $updates[] ="select now()" ;
    $updates[] ="select now()" ;
    $updates[] ="select now()" ;
    $updates[] ="select now()" ;
    $updates[] ="CREATE TABLE `chat_room_moderators` (
`id` INT NOT NULL ,
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the record was updated',
`created` TIMESTAMP NOT NULL COMMENT 'when the record was created',
`IdRoom` INT NOT NULL COMMENT 'The room',
`IdMember` INT NOT NULL COMMENT 'The member',
`MemberCan` SET( 'InviteAndKick', 'CleanRoom' ) NOT NULL COMMENT 'The thing the member is allowed to do in the room'
) ENGINE = MYISAM COMMENT = 'This table describes additional righst for additional chat room moderators'";

    $updates[] ="ALTER TABLE `chat_room_moderators` ADD PRIMARY KEY ( `id` ) " ;
    $updates[] ="ALTER TABLE `chat_room_moderators` ADD INDEX `Id_RoomMember` ( `IdRoom` , `IdMember` ) " ;
	$updates[] ="select now()" ; 
	$updates[] ="select now()" ; 
	$updates[] ="INSERT INTO `preferences` (`id`, `codeName`, `codeDescription`, `Description`, `created`, `DefaultValue`, `PossibleValues`, `EvalString`, `Status`) 
	VALUES (NULL, 'PreferenceForumFirstPage', 'PreferenceForumFirstPageDesc', 'This preference is used to state what is the first page of teh forum for the user', NOW(), 
	'Pref_ForumFirstPageLastPost', 'Pref_ForumFirstPageLastPost;Pref_ForumFirstPageCategory', 
	'echo \"\n<select name=PreferenceForumFirstPage class=\"prefsel\">\" ; echo \"<option value=Pref_ForumFirstPageLastPost\" ; if (\$Value==\"Pref_ForumFirstPageLastPost\") echo \" selected \" ; echo \">\",ww(\"Pref_ForumFirstPageLastPost\"),\"</option>\" ; echo \"<option value=Pref_ForumFirstPageCategory\" ; if (\$Value==\"Pref_ForumFirstPageCategory\") echo \" selected \" ; echo \">\",ww(\"Pref_ForumFirstPageCategory\"),\"</option>\" ; echo \"</select> \" ;', 'Advanced')";
	
	$updates[] ="INSERT INTO `rights` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'ContactLocation', 'This right allows to contact by mail a group of people in a location The scope can be : 'All' for all location 'LocalVol', in this case the location the member can contact will depend on the one he is registered as a local vol for 'IdLocation1','IdLocation2' : a list of integer values which match the geoname id of the corresponding location 'WeekLimit=X' is a value which define the limit in number of mail of this type sent per week by this member 'DoesNotNeedApproval' : a Parameter which allow the mail wrote by the sender to be sent immediately without any need of review by the Local Vol coordinator '
)";
	$updates[] ="ALTER TABLE `messages` ADD `MessageType` ENUM( 'MemberToMember', 'LocalVolToMember' ) NOT NULL DEFAULT 'MemberToMember' COMMENT 'Type of the message, state if it is a memner to member message or something else' AFTER `id` ,
ADD `IdMessageFromLocalVol` INT NOT NULL DEFAULT '0' COMMENT 'Id of to the localvol messages if this is a type LocalVolToMember' AFTER `MessageType` ";
	$updates[] ="CREATE TABLE `localvolmessages_location` (
`IdLocation` INT NOT NULL COMMENT 'Location where the members are supposed to recieve the message',
`IdLocalVolMessage` INT NOT NULL COMMENT 'Id of the message',
INDEX ( `IdLocation` , `IdLocalVolMessage` )
) ENGINE = MYISAM COMMENT = 'Receive the list of location where messages of localvols are to be delivered'";

	$updates[] ="CREATE TABLE `localvolmessages` (
`id` INT NOT NULL AUTO_INCREMENT  PRIMARY KEY,
`Status` ENUM( 'ToApprove', 'ToSend', 'Sent' ) NOT NULL DEFAULT 'ToApprove' COMMENT 'Status of the message (if it is to be approved, to send by mailbot or Sent)',
`MessageText` TEXT NOT NULL COMMENT 'tet of the message as the sender fill it',
`IdSender` INT NOT NULL COMMENT 'Id of the sender of the message',
`Type` ENUM( 'Meeting', 'HelpRequest', 'Info' ) NOT NULL DEFAULT 'Info' COMMENT 'type of the message',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the message was updated',
`created` TIMESTAMP NOT NULL COMMENT 'When the message was created',
`PurposeDescription` TEXT NOT NULL COMMENT 'Purpose of the message, either the creator or the local vol coordinator can add details here, these details are only for them'
) ENGINE = MYISAM COMMENT = 'Table of the messages from LocalVols to member' "; 

	$updates[] ="ALTER TABLE `localvolmessages` ADD `TitleText` TEXT NOT NULL COMMENT 'This is the title of the message' AFTER `MessageText` ,
ADD `IdLocalMessage` INT NOT NULL COMMENT 'This is the Id of the message (it can be translated so several records with same Id)' AFTER `TitleText` ,
ADD `IdLanguage` INT NOT NULL DEFAULT '0' COMMENT 'Language of this message' AFTER `IdLocalMessage` ";
	$updates[] ="ALTER TABLE `localvolmessages` ADD INDEX ( `IdSender` ) ";
	$updates[] ="ALTER TABLE `localvolmessages` ADD UNIQUE `IdOfMess` ( `IdLocalMessage` , `IdLanguage` ) ";
	$updates[] ="INSERT INTO `flags` ( `id` , `created` , `Name` , `Description` )
VALUES (
NULL , NOW( ) , 'RequireCaptchaForContact', 'When this flag is set for a member, he will have to fill a captcha to be able to send a mail This is to reduce the risk of SPAM'
)";
	$updates[] ="select now()  ";
	$updates[] ="select now()  ";
	$updates[] ="select now()  ";
	$updates[] ="select now()  ";
	$updates[] ="RENAME TABLE `forum_trads`  TO `translations` ";
	$updates[] ="ALTER TABLE `translations`  COMMENT = 'Will be used to store general translated data by members'" ;
	$updates[] ="CREATE ALGORITHM=MERGE VIEW `forum_trads` AS select * from translations";
	$updates[] ="ALTER TABLE `localvolmessages_location` DROP INDEX `IdLocation` " ;
	$updates[] ="ALTER TABLE `localvolmessages_location` ADD UNIQUE (
`IdLocation` ,
`IdLocalVolMessage`
) " ;

	$updates[] ="ALTER TABLE `localvolmessages` DROP INDEX `IdOfMess` " ;
	$updates[] ="ALTER TABLE `localvolmessages`
  DROP `IdLocalMessage`,
  DROP `IdLanguage`";
  
$updates[] = "select now() " ;
$updates[] = "select now() " ;
$updates[] = "select now() " ;
$updates[] = "select now() " ;
$updates[] = "select now() " ;
$updates[] = "select now() " ;
$updates[] = <<<SQL
INSERT INTO `preferences` ( `codeName`, `codeDescription`, `Description`, `created`, `DefaultValue`, `PossibleValues`, `EvalString`, `Status`) VALUES 
( 'PreferenceLocalEvent', 'PreferenceLocalEventDesc', 'this preference which is by default set to True will allow the owner to receive the notification for local event in its area (City/Region or Country)', '2009-02-25 12:33:50', 'Yes', 'Yes;No', 
'echo "<select name=\'PreferenceLocalEvent\' class=\'prefsel\'><option value=Yes " ;if (\$Value==\'Yes\') echo " selected " ;echo ">",ww(\'Yes\'),"</option><option value=No" ;if (\$Value==\'No\') echo " selected " ;echo ">",ww(\'No\'),"</option></select>" ;', 'Active'),
( 'PreferenceForumCity', 'PreferenceForumCityDesc', 'this preference which is by default set to Yes will allow the owner to receive the notification for forum post event in its City', '2009-02-25 12:39:33', 'Yes', 'Yes;No', 
'echo "<select name=\'PreferenceForumCity\' class=\'prefsel\'><option value=Yes " ;if (\$Value==\'Yes\') echo " selected " ;echo ">",ww(\'Yes\'),"</option><option value=No" ;if (\$Value==\'No\') echo " selected " ;echo ">",ww(\'No\'),"</option></select>" ;', 'Active'),
( 'PreferenceForumRegion', 'PreferenceForumRegionDesc', 'this preference which is by default set to yes will allow the owner to receive the notification for forum post event in its Region', '2009-02-25 12:39:33', 'Yes', 'Yes;No', 
'echo "<select name=\'PreferenceForumRegion\' class=\'prefsel\'><option value=Yes " ;if (\$Value==\'Yes\') echo " selected " ;echo ">",ww(\'Yes\'),"</option><option value=No" ;if (\$Value==\'No\') echo " selected " ;echo ">",ww(\'No\'),"</option></select>" ;', 'Active'),
( 'PreferenceCountryRegion', 'PreferenceForumCountryDesc', 'this preference which is by default set to No will allow the owner to receive the notification for forum post event in its Region', '2009-02-25 12:39:33', 'No', 'No;Yes', 
'echo "<select name=\'PreferenceForumCountry\' class=\'prefsel\'><option value=Yes " ;if (\$Value==\'Yes\') echo " selected " ;echo ">",ww(\'Yes\'),"</option><option value=No" ;if (\$Value==\'No\') echo " selected " ;echo ">",ww(\'No\'),"</option></select>" ;', 'Active')
SQL;

$updates[] = <<<SQL
ALTER TABLE `forums_posts` ADD `HasVotes` ENUM( 'No', 'Yes' ) NOT NULL DEFAULT 'No' COMMENT 'States if there is a vote connected to this post',
ADD `IdLocalEvent` INT NOT NULL DEFAULT '0' COMMENT 'States if there is a local event connected to this posts'
SQL;

$updates[] = <<<SQL
CREATE TABLE `forums_posts_votes` (
`IdPost` INT NOT NULL COMMENT 'Id of the corresponding post',
`IdContributor` INT NOT NULL COMMENT 'if of the member who contributes',
`Choice` ENUM( 'Yes', 'DontKnow', 'DontCare', 'No' ) NULL DEFAULT NULL COMMENT 'result of vote',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when it was updated',
`created` TIMESTAMP NOT NULL COMMENT 'when it was created',
`NbUpdates` INT NOT NULL DEFAULT '0' COMMENT 'use to count the number of time a guy has voted/updated for a given post'
) ENGINE = MYISAM COMMENT = 'This table is used to store the vote of members about some specific posts'
SQL;

$updates[] = <<<SQL
ALTER TABLE `forums_posts_votes` ADD PRIMARY KEY ( `IdPost` , `IdContributor` ) 
SQL;

$updates[] = "CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL auto_increment,
  `IdMember` int(11) NOT NULL,
  `IdRelMember` int(11) NOT NULL,
  `Type` enum('message','profile_comment','profile_comment_negative','gallery_comment','picture_comment','blog_comment','chat_invitation') character set utf8 collate utf8_unicode_ci NOT NULL,
  `Link` varchar(300) character set utf8 collate utf8_unicode_ci NOT NULL,
  `WordCode` varchar(300) character set utf8 collate utf8_unicode_ci NOT NULL,
  `FreeText` varchar(300) character set utf8 collate utf8_unicode_ci NOT NULL,
  `Checked` tinyint(1) NOT NULL default '0',
  `SendMail` tinyint(1) NOT NULL default '0',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26";

$updates[] = "ALTER TABLE `forums_posts` ADD `IdPoll` INT NOT NULL DEFAULT '0' COMMENT 'optional id of a poll connected to this forum post' AFTER `IdLocalEvent`" ;
$updates[] = "ALTER TABLE `forums_posts` ADD INDEX ( `IdLocalEvent` ) " ;
$updates[] = "ALTER TABLE `forums_posts` ADD INDEX ( `IdPoll` ) " ;

$updates[] = "ALTER TABLE `params` ADD `logs_id_midnight` INT NOT NULL COMMENT 'id of logs table at previous midnight',
ADD `previous_logs_id_midnight` INT NOT NULL COMMENT 'id of logs table at midnight day before'";

$updates[] = "INSERT INTO `sqlforvolunteers` ( `id` , `Name` , `Query` , `updated` , `param1` , `param2` , `LogMe` )
VALUES (
NULL , 'Update the references id in param for yesterday and before yesterday logs (this is helpful for adminlog performances)', 'update params set logs_id_midnight=(select min(id) from logs where created> concat( date( date_sub( now( ) , INTERVAL 1 DAY ) ))), previous_logs_id_midnight=(select min(id) from logs where created>concat( date( date_sub( now( ) , INTERVAL 2 DAY ) ))) ;', NOW( ) , NULL , NULL , 'False'
)";

$updates[] = "ALTER TABLE `countries` ADD `FirstAdminLevel` VARCHAR( 4 ) NOT NULL DEFAULT 'ADM1' COMMENT 'This will allow for fine tunning with sublevel definitions (for France=regions)',
ADD `SecondAdminLevel` VARCHAR( 4 ) NOT NULL DEFAULT 'ADM2' COMMENT 'This will allow for fine tunning with second sublevel definitions (for France=departments)' " ;

$updates[] = "ALTER TABLE `countries` CHANGE `FirstAdminLevel` `FirstAdminLevel` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ADM1' COMMENT 'This will allow for fine tunning with sublevel definitions (for France=regions)' ";

$updates[] = "DROP VIEW IF EXISTS `regions`";

$updates[] = "CREATE ALGORITHM=MERGE  VIEW `regions` AS 
select `gc`.`geonameid` AS `id`,`gc`.`name` AS `Name`,`gc`.`name` AS `ansiname`,`gc`.`name` AS `OtherNames`,
`gc`.`latitude` AS `latitude`,`gc`.`longitude` AS `longitude`,`gc`.`fclass` AS `feature_class`,
`gc`.`fcode` AS `feature_code`,`gc`.`fk_countrycode` AS `country_code`,`gc`.`population` AS `population`,
'0' AS `citiesopen`,`gc`.`parentCountryId` AS `IdCountry`,`counters_regions_nbcities`.`NbCities` AS `NbCities`,
`geo_usage`.`count` AS `NbMembers` from ((`geonames_cache` `gc`,`countries`  join `geo_usage`) 
join `counters_regions_nbcities`) 
where ((`gc`.`fcode` = `countries`.`FirstAdminLevel`) and (`geo_usage`.`geoId` = `gc`.`geonameid`) and (`counters_regions_nbcities`.`IdRegion` = `gc`.`geonameid`) and `gc`.`parentCountryId`=`countries`.`id`)";

$updates[] = "ALTER TABLE `params` ADD `memcache` ENUM( 'False', 'True' ) NOT NULL DEFAULT 'False' COMMENT 'Used to enable the use of memcache'" ;

$updates[] = "ALTER TABLE `forums_posts` ADD `IdLocalVolMessage` INT NOT NULL DEFAULT '0' COMMENT 'Connection with a possible Message to send to a location (to manage thump up/down)' AFTER `HasVotes` ";

$updates[] = "ALTER TABLE `forums_posts` ADD INDEX ( `IdLocalVolMessage` ) ";

$updates[] = "
INSERT INTO `preferences` VALUES (20, 'PreferenceLocalTime', 'PreferenceLocalTimeDesc', 
'This preference allows to choose a reference city for local time', '2009-04-21 21:20:37', '0', '', 
'echo \"\\n<select name=\'PreferenceLocalTime\' class=\'prefsel\'>\" ;
 echo \"<option value=\'-28800\'\"; if (\$Value==-28800) echo  \" selected \";echo \">Los Angeles</option>\\n\";
 echo \"<option value=\'-25200\'\"; if (\$Value==-25200) echo  \" selected \";echo \">Calgari</option>\\n\";
 echo \"<option value=\'-21600\'\"; if (\$Value==-21600) echo  \" selected \";echo \">Mexico</option>\\n\";
 echo \"<option value=\'-18000\'\"; if (\$Value==-18000) echo  \" selected \";echo \">New York</option>\\n\";
 echo \"<option value=\'-14400\'\"; if (\$Value==-14400) echo  \" selected \";echo \">Santiago</option>\\n\";
 echo \"<option value=\'-10800\'\"; if (\$Value==-10800) echo  \" selected \";echo \">Sao Paulo</option>\\n\";
 echo \"<option value=\'-7200\'\"; if (\$Value==-7200) echo  \" selected \";echo \">Fernando de Noronha</option>\\n\";
 echo \"<option value=\'-3600\'\"; if (\$Value==-3600) echo  \" selected \";echo \">Cape Verde</option>\\n\";
 echo \"<option value=\'0\'\"; if (\$Value==0) echo  \" selected \";echo \">London</option>\\n\";
 echo \"<option value=\'3600\'\"; if (\$Value==3600) echo  \" selected \";echo \">Paris, Berlin</option>\\n\";
 echo \"<option value=\'7200\'\"; if (\$Value==7200) echo  \" selected \";echo \">Cairo</option>\\n\";
 echo \"<option value=\'10800\'\"; if (\$Value==10800) echo  \" selected \";echo \">Moscow</option>\\n\";
 echo \"<option value=\'14400\'\"; if (\$Value==14400) echo  \" selected \";echo \">Dubai</option>\\n\";
 echo \"<option value=\'18000\'\"; if (\$Value==18000) echo  \" selected \";echo \">Karachi</option>\\n\";
 echo \"<option value=\'19800\'\"; if (\$Value==19800) echo  \" selected \";echo \">Mumbai</option>\\n\";
 echo \"<option value=\'21600\'\"; if (\$Value==21600) echo  \" selected \";echo \">Dhaka</option>\\n\";
 echo \"<option value=\'25200\'\"; if (\$Value==25200) echo  \" selected \";echo \">Jakarta</option>\\n\";
 echo \"<option value=\'28800\'\"; if (\$Value==28800) echo  \" selected \";echo \">Hong Kong</option>\\n\";
 echo \"<option value=\'32400\'\"; if (\$Value==32400) echo  \" selected \";echo \">Tokyo</option>\\n\";
 echo \"<option value=\'36000\'\"; if (\$Value==36000) echo  \" selected \";echo \">Sydney</option>\\n\";
 echo \"<option value=\'39600\'\"; if (\$Value==39600) echo  \" selected \";echo \">Noumea</option>\\n\";
 echo \"<option value=\'43200\'\"; if (\$Value==43200) echo  \" selected \";echo \">Auckland</option>\\n\";
 echo \"</select>\\n\" ;', 'Active'); 
 " ;
$updates[] = "
INSERT INTO `preferences` VALUES (21, 'PreferenceDayLight', 'PreferenceDayLightDesc', 
'This preference allows to choose if the member wants to use daylight saving orn not', '2009-04-22 15:20:37', 'Yes', 'Yes,No', 
'echo \"\\n<select name=\'PreferenceDayLight\' class=\'prefsel\'>\" ;
 echo \"<option value=\'Yes'\"; if (\$Value=='Yes') echo  \" selected \";echo \">\",ww(\"Yes\"),\"</option>\\n\";
 echo \"<option value=\'No'\"; if (\$Value=='No') echo  \" selected \";echo \">\",ww(\"No\"),\"</option>\\n\";
 echo \"</select>\\n\" ;', 'Active'); 
 " ;
 
 $updates[] = "ALTER TABLE `params` ADD `DayLightOffset` INT NOT NULL DEFAULT '0' COMMENT 'This is the Day light Offset to be added to display dates tiem for members who chose this preference'" ;

$updates[] = <<<SQL
ALTER TABLE membersgroups
MODIFY COLUMN Status ENUM ('In', 'WantToBeIn', 'Kicked', 'Invited') NOT NULL DEFAULT 'WantToBeIn' COMMENT 'Describes the connection between a member and a group'
SQL;

$updates[] = <<<SQL
ALTER TABLE `comments` ADD `DisplayableInCommentOfTheMonth` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'Yes' 
 COMMENT 'State wether this comment can be allowed to be display in comment of the month'
SQL;

$updates[] = <<<SQL
ALTER TABLE `sqlforvolunteers` 
ADD `DefValueParam1` TEXT NOT NULL COMMENT 'The default value for param 1',
ADD `DefValueParam2` TEXT NOT NULL COMMENT 'The default value for param 2',
ADD `Param1Type` ENUM( 'inputtext', 'textarea', 'ListOfChoices' ) NOT NULL DEFAULT 'inputtext' COMMENT 'The form of display for param1',
ADD `Param2Type` ENUM( 'inputtext', 'textarea', 'ListOfChoices' ) NOT NULL DEFAULT 'inputtext' COMMENT 'The form of display for param2'
SQL;

$updates[] = <<<SQL
CREATE TABLE `comments_ofthemomment_votes` (
`IdMember` INT NOT NULL COMMENT 'id of the member',
`IdComment` INT NOT NULL COMMENT 'Id of the comment',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the record was updated',
`created` TIMESTAMP NOT NULL COMMENT 'when the record was created'
) ENGINE = MYISAM COMMENT = 'Counts the votes for comments of the moment'
SQL;

$updates[] = <<<SQL
ALTER TABLE `comments_ofthemomment_votes` ADD UNIQUE `Id_Member_Comment` ( `IdMember` , `IdComment` ) 
SQL;

$updates[] = <<<SQL
ALTER TABLE `params` ADD `IdCommentOfTheMoment` INT NOT NULL DEFAULT '0' COMMENT 'Id of the comment of the month (updated by lastcomments votes)'
SQL;

$updates[] = <<<SQL
ALTER TABLE `params` ADD `NbCommentsInLastComments` INT NOT NULL DEFAULT '20' COMMENT 'This is the number of comments to display in the lastcomments page' AFTER `DayLightOffset` 
SQL;

$updates[]="INSERT INTO `words` VALUES (0,'ago', 'en', 'ago', '2008-06-20 09:33:24', 'no', 0, '', 71, '2008-06-20 09:33:24', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'ago', 'fr', ' ', '2008-06-25 17:57:13', 'no', 1, '', 757, '2008-06-25 17:57:13', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'ago', 'cat', 'fa', '2008-06-30 16:12:26', 'no', 29, '', 3236, '2008-06-30 16:12:26', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'ago', 'de', 'her', '2008-07-09 11:09:45', 'no', 6, '', 3050, '2008-07-09 11:09:45', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'ago', 'pl', 'temu', '2008-08-27 22:47:45', 'no', 19, '', 392, '2008-08-27 22:47:45', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'ago', 'fi', 'sitten', '2009-05-18 21:22:46', 'no', 11, '', 72, '2009-05-18 21:22:46', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'seconds_ago', 'en', '%s seconds ago', '2009-05-21 12:56:10', 'no', 0, 'This is used to display x sconds ago\r\n\r\n%s stands for the number of second', 1, '2009-05-21 12:56:10', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'minutes_ago', 'en', '%s minutes ago', '2009-05-21 12:56:39', 'no', 0, 'This is used to display x minutes ago\r\n\r\n%s stands for the number of minutes', 1, '2009-05-21 12:56:39', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'minute_ago', 'en', '%s minute ago', '2009-05-21 12:57:07', 'no', 0, 'This is used to display 1 minute ago\r\n\r\n%s stands for the number of minutes (which can be 1 or 0)', 1, '2009-05-21 12:57:07', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'hour_ago', 'en', '%s hour ago', '2009-05-21 12:57:26', 'no', 0, 'This is used to display 1 hourago\r\n\r\n%s stands for the number of hour (which can be 1 or 0)', 1, '2009-05-21 12:57:26', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'hours_ago', 'en', '%s hours ago', '2009-05-21 12:57:43', 'no', 0, 'This is used to display x hours ago\r\n\r\n%s stands for the number of hour (which can be 1 or 0)', 1, '2009-05-21 12:57:43', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'days_ago', 'en', '%s days ago', '2009-05-21 12:58:40', 'no', 0, 'This is used to display x days ago\r\n\r\n%s stands for the number of days ', 1, '2009-05-21 12:58:06', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'day_ago', 'en', '%s day ago', '2009-05-21 12:58:24', 'no', 0, 'This is used to display 1 day ago\r\n\r\n%s stands for the number of day (which can be 1 or 0)', 1, '2009-05-21 12:58:24', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'weeks_ago', 'en', '%s weeks ago', '2009-05-21 12:58:59', 'no', 0, 'This is used to display x weeks ago\r\n\r\n%s stands for the number of weeks ', 1, '2009-05-21 12:58:59', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'years_ago', 'en', '%s years ago', '2009-05-21 12:59:22', 'no', 0, 'This is used to display x years ago\r\n\r\n%s stands for the number of years ', 1, '2009-05-21 12:59:22', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'year_ago', 'en', '%s year ago', '2009-05-21 12:59:47', 'no', 0, 'This is used to display 1 year ago\r\n\r\n%s stands for the number of year (1 or 0)', 1, '2009-05-21 12:59:47', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'week_ago', 'en', '%s week ago', '2009-05-21 13:00:16', 'no', 0, 'This is used to display 1 week ago\r\n\r\n%s stands for the number of week  (1 or 0)', 1, '2009-05-21 13:00:16', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'months_ago', 'en', '%s months ago', '2009-05-21 13:00:34', 'no', 0, 'This is used to display x months ago\r\n\r\n%s stands for the number of months', 1, '2009-05-21 13:00:34', 5)";
$updates[]="INSERT INTO `words` VALUES (0,'month_ago', 'en', '%s month ago', '2009-05-21 13:01:00', 'no', 0, 'This is used to display 1 month ago\r\n\r\n%s stands for the number of month (1 or 0)', 1, '2009-05-21 13:01:00', 5)";

$updates[] = "CREATE FUNCTION MemberNbComments(v_IdMember INT)   
	RETURNS INT  
	DETERMINISTIC
    BEGIN
	  DECLARE iRes INT ;
      select count(*) into iRes from comments where IdToMember=v_IdMember ;
	 RETURN iRes ;
    END" ;

$updates[] = "CREATE FUNCTION CommentNbVotes(v_IdComment INT)   
	RETURNS INT  
	DETERMINISTIC
    BEGIN
	  DECLARE iRes INT ;
      select count(*) into iRes from comments_ofthemomment_votes where IdComment=v_IdComment ;
	 RETURN iRes ;
    END" ;
	
	
$updates[] = "CREATE TABLE `members_updating_status` (
`IdMember` INT NOT NULL COMMENT 'id of the members',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the record was updated',
`created` TIMESTAMP NOT NULL COMMENT 'when the record was created',
`OldStatus` TINYTEXT NOT NULL COMMENT 'The value of the status before',
`NewStatus` TINYTEXT NOT NULL COMMENT 'the new value of the status',
INDEX ( `IdMember` , `updated` )
) ENGINE = MYISAM COMMENT = 'This table is used for reporting to accepters, it records members Status changes'" ;

$updates[] = "CREATE TABLE `accepters_reports_schedule` (
`IdAccepter` INT NOT NULL COMMENT 'Id of the accepter',
`TimeToDeliver` TIMESTAMP NOT NULL COMMENT 'datetime for the next delivery of the report'
`DelayInHourForNextOne` INT NOT NULL DEFAULT '24' COMMENT 'datetime for the next delivery of the report'
) ENGINE = MYISAM COMMENT = 'List of accepters with the next time they should receive the accepters report'";


$updates[] = "

CREATE TRIGGER before_members_update BEFORE UPDATE ON members
FOR EACH ROW
BEGIN

IF NEW.Status<>OLD.Status THEN
	insert into members_updating_status(IdMember,updated,created,OldStatus,NewStatus) values (NEW.id,now(),now(),OLD.Status,NEW.Status);
END IF ;
END 

";


$updates[] = "ALTER TABLE `volunteers_reports_schedule`  COMMENT = 'List of volunteers with the next time for receiveving report'" ;

$updates[] = "RENAME TABLE `accepters_reports_schedule`  TO `volunteers_reports_schedule`" ;

$updates[] = "ALTER TABLE `volunteers_reports_schedule` CHANGE `IdAccepter` `IdVolunteer` INT( 11 ) NOT NULL COMMENT 'Id of the accepter'" ;

$updates[] = "ALTER TABLE `volunteers_reports_schedule` ADD `Type` ENUM( 'Accepter', 'Group', 'Abuse' ) NOT NULL COMMENT 'Type of report'";

$updates[] = "ALTER TABLE `volunteers_reports_schedule` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'primary key' FIRST ";

$updates[] = "ALTER TABLE `params` ADD `MailBotMode` ENUM( 'Auto','Manual','Stop' ) NOT NULL DEFAULT 'Auto' COMMENT 'State whether mail bot is running or not'";

$updates[] = "ALTER TABLE `forums_posts` ADD `PostVisibility` ENUM( 'NoRestriction', 'MembersOnly', 'GroupOnly', 'ModeratorOnly' ) NOT NULL DEFAULT 'NoRestriction' COMMENT 'States who can read this post (usually same value as parent thread)' AFTER `threadid` ";

$updates[] = "ALTER TABLE `forums_posts` ADD `PostDeleted` ENUM( 'NotDeleted', 'Deleted' ) NOT NULL DEFAULT 'NotDeleted' COMMENT 'tells if the post was deleted'";

$updates[] = "ALTER TABLE `forums_threads` ADD `ThreadDeleted` ENUM( 'NotDeleted', 'Deleted' ) NOT NULL DEFAULT 'NotDeleted' COMMENT 'tells if the thread was deleted'";

$updates[] = "ALTER TABLE `forums_threads` ADD `Visibility` ENUM( 'NoRestriction', 'MembersOnly', 'GroupOnly', 'ModeratorOnly' ) NOT NULL COMMENT 'Visibility for this thread'" ;

$updates[] = "ALTER TABLE `forums_posts` ADD `Visibility` ENUM( 'NoRestriction', 'MembersOnly', 'GroupOnly', 'ModeratorOnly' ) NOT NULL COMMENT 'Visibility for this post'" ;

$updates[] = "ALTER TABLE `forums_posts` DROP `Visibility`" ; 

$updates[] = "ALTER TABLE `forums_threads` CHANGE `Visibility` `ThreadVisibility` ENUM( 'NoRestriction', 'MembersOnly', 'GroupOnly', 'ModeratorOnly' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NoRestriction' COMMENT 'Visibility for this thread'" ;

$updates[] = "ALTER TABLE `reports_to_moderators` CHANGE `ReporterComment` `PostComment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Comment of the reporter'" ;

$updates[] = "ALTER TABLE `reports_to_moderators` ADD `LastWhoSpoke`  ENUM( 'Member', 'Moderator' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Member' COMMENT 'This field says if it is the member or the moderator who spoke in last'" ;

$updates[] = "ALTER TABLE `words` CHANGE `code` `code` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL " ;

$updates[] = "ALTER TABLE notes DROP COLUMN FreeText, ADD COLUMN TranslationParams TEXT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'Serialised array of wordcode and params for translation'";

$updates[] = <<<SQL
CREATE TABLE forums_threads_votes (
    id INT(11) NOT NULL PRIMARY KEY,
    `IdThread` INT(11) NOT NULL COMMENT 'id of the corresponding thread',
    `IdMember` INT(11) NOT NULL COMMENT 'id of the voting member',
    `Vote` enum('positive','negative','neutral') default 'negative' COMMENT 'result of vote',
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'time of voting',
    CONSTRAINT UNIQUE (`IdThread`,`IdMember`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Stores votes from members on individual forum threads';
SQL;

$updates[] = "ALTER TABLE `geo_usage` CHANGE `typeId` `typeId` INT( 11 ) NOT NULL COMMENT 'id specifying the usage type, eg member, blog or gallery, its a foreign key to geo_types'" ;

$updates[] = "ALTER TABLE `linklist` ADD `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ";

$updates[] = "ALTER TABLE `params` ADD `FeatureSearchPageIsClosed` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'allow to disbale the main search page' AFTER `FeatureSignupClose` ,
ADD `FeatureQuickSearchIsClosed` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'allows to disable the quick search feature' AFTER `FeatureSearchPageIsClosed`" ;

$updates[] = "ALTER TABLE `geonames_alternate_names` ADD INDEX ( `alternateName` ) " ; 

$updates[] = "ALTER TABLE `countries` ADD INDEX ( `isoalpha2` ) " ;
$updates[] = "ALTER TABLE `params` ADD `RssFeedIsClosed` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'Allow to disable the RSS feature' AFTER `FeatureQuickSearchIsClosed` ";

$updates[] = "CREATE FUNCTION NbMembers () returns int
DETERMINISTIC

    BEGIN
       DECLARE iRes INT ;
       SELECT COUNT(*) into iRes from members where Status in('Active','Inactive','OutOfRemind');
       RETURN iRes ;
     END" ;
	 
$updates[] = "ALTER TABLE `languages` ADD `UrlHeader` VARCHAR( 4 ) NULL COMMENT 'If set it means that the value if found in the url, will force this language (like fr.bewelcome.org)'  " ;


$updates[] = "CREATE TABLE `urlheader_languages` (
`urlheader` VARCHAR( 10 ) NOT NULL COMMENT 'url header (www / fr / de ...)',
`IdLanguage` INT NOT NULL COMMENT 'Id of the language',
`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the record was updated',
`created` TIMESTAMP NOT NULL COMMENT 'when the record was created'
) ENGINE = MYISAM COMMENT = 'allows to choose a default labguage for a specific url header'";

$updates[] = "ALTER TABLE `languages` DROP `UrlHeader` " ;

$updates[] = "ALTER TABLE `params` ADD `IsRealProductionDatabase` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' COMMENT 'Says if it is the real database or not' AFTER `updated` ";

$updates[] = "ALTER TABLE `addresses` ADD INDEX ( `IdMember` )  ";
$updates[] = "ALTER TABLE `addresses` ADD INDEX ( `IdCity` ) " ; 


$updates[] = "drop view regions ";
$updates[] = "CREATE ALGORITHM=MERGE VIEW `regions` 
AS select `gc`.`geonameid` AS `id`,`gc`.`name` AS `Name`,`gc`.`name` AS `ansiname`,`gc`.`name` AS `OtherNames`,`gc`.`latitude` AS `latitude`,`gc`.`longitude` AS `longitude`,`gc`.`fclass` AS `feature_class`,`gc`.`fcode` AS `feature_code`,`gc`.`fk_countrycode` AS `country_code`,`gc`.`population` AS `population`,_utf8'0' AS `citiesopen`,`gc`.`parentCountryId` AS `IdCountry`,`counters_regions_nbcities`.`NbCities` AS `NbCities`,`geo_usage`.`count` AS `NbMembers` 
from ((`geonames_cache` `gc` join (`countries` join `geo_usage`)) 
join `counters_regions_nbcities`) 
where ((`gc`.`fcode` = `countries`.`FirstAdminLevel`) and (`geo_usage`.`geoId` = `gc`.`geonameid`) and (`geo_usage`.`typeId` = 1)  and 
(`counters_regions_nbcities`.`IdRegion` = `gc`.`geonameid`) and (`gc`.`parentCountryId` = `countries`.`id`))";

$updates[] = <<<SQL
ALTER TABLE groups ADD IdGeoname INT UNSIGNED, ADD IsLocal TINYINT UNSIGNED NOT NULL DEFAULT FALSE
SQL;

    $updates[] = <<<SQL
ALTER TABLE groups DROP HasMembers
SQL;

    $updates[] = <<<SQL
ALTER TABLE memberslanguageslevel CHANGE Level Level ENUM('MotherLanguage', 'Expert', 'Fluent', 'Intermediate', 'Beginner', 'HelloOnly') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Beginner' COMMENT 'language level'
SQL;

    $updates[] = <<<SQL
ALTER TABLE membersgroups ADD column IsLocal BOOL DEFAULT false NOT NULL COMMENT 'Determines if the member is a local member of a geo group'
SQL;

    $updates[] = <<<SQL
CREATE TABLE IF NOT EXISTS `volunteers_reports_schedule` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `IdVolunteer` int(11) NOT NULL COMMENT 'Id of the accepter',
  `TimeToDeliver` timestamp NOT NULL default '0000-00-00 00:00:00' COMMENT 'datetime for the next delivery of teh report',
  `DelayInHourForNextOne` int(11) NOT NULL default '24' COMMENT 'delay to schedule the next report',
  `Type` enum('Accepter','Group','Abuse') NOT NULL COMMENT 'Type of report',
  PRIMARY KEY  (`id`),
  KEY `IdAccepter` (`IdVolunteer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='List of volunteerswith the next time they should receive the'
SQL;

	$updates[]= "
	 ALTER TABLE `memberstrads` ADD UNIQUE `Unique_entry` ( 
	 `IdTrad` , `
	 IdOwner` , 
	 `IdLanguage` )  
";

	$updates[]= "
 ALTER TABLE `translations` ADD UNIQUE `unique_entry` ( 
 `IdTrad` , 
 `IdOwner` , 
 `IdLanguage` )  
";

	$updates[] = "DROP FUNCTION IF EXISTS fUserName";
	$updates[]="
CREATE or REPLACE FUNCTION fUserName (s VARCHAR(32)) RETURNS VARCHAR(32) DETERMINISTIC
/* this function allows to retrieve a Username from the IdMember */ 
begin
declare sResult VARCHAR(32) ;
if ( s REGEXP ('[0-9]')) then
	select Username from members where members.id=s into sResult ;
else
	set sResult:=s ;
end if ;
return sResult ;
end " ;

	$updates[] = "DROP FUNCTION IF EXISTS mInTrad";
	$updates[]="
CREATE FUNCTION mInTrad (iTrad INT,iLang int) RETURNS VARCHAR(1024) DETERMINISTIC
/* 
this function allows to retrieve the data behind a members trad in a given language
if this language is not found, the english language will be tried, and if still nothing is found, teh first available language will be used 
*/ 
begin
declare sResult VARCHAR(1024) ;
declare v_id INT ;
select Sentence,id from memberstrads where IdTrad=ITrad and  memberstrads.IdLanguage=iLang into sResult,v_id ;

if ( v_id IS NULL) then
	if (iLang<>0) then
		select Sentence,id from memberstrads where IdTrad=ITrad and  memberstrads.IdLanguage=0 into sResult,v_id ;
		if ( v_id IS NULL) then
			select Sentence,id from memberstrads where IdTrad=ITrad order by id asc limit 1 into sResult,v_id ;
			if ( v_id IS NULL) then
				return('') ;
			else
				return(sResult) ;
			end if ;
		else 
			return(sResult) ;
		end if ;
	else
		return('') ;
	end if ;
else
	return (sResult);
end if ;
end " ;

	$updates[]="
 ALTER TABLE `previousversion` 
 CHANGE `Type` `Type` ENUM( 'DoneByMember', 'DoneByOtherMember', 'DoneByVolunteer', 'DoneByAdmin', 'DoneByModerator' ) 
 CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DoneByMember' 
 COMMENT 'Here are stored the previous version of updated translation (this can help to rollback problem -mannually- if need)' 
 " ;

    $updates[] = <<<SQL
ALTER TABLE blog ADD COLUMN IdMember INT COMMENT 'References members table'
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_categories ADD COLUMN IdMember INT COMMENT 'References members table'
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_comments ADD COLUMN IdMember INT COMMENT 'References members table'
SQL;

    $updates[] = <<<SQL
UPDATE blog, (SELECT user.id AS user_id, members.id AS member_id FROM user, members WHERE user.handle = members.username) AS temp SET blog.IdMember = temp.member_id WHERE temp.user_id = blog.user_id_foreign;
SQL;

    $updates[] = <<<SQL
UPDATE blog_categories, (SELECT user.id AS user_id, members.id AS member_id FROM user, members WHERE user.handle = members.username) AS temp SET blog_categories.IdMember = temp.member_id WHERE temp.user_id = blog_categories.user_id_foreign;
SQL;

    $updates[] = <<<SQL
UPDATE blog_comments, (SELECT user.id AS user_id, members.id AS member_id FROM user, members WHERE user.handle = members.username) AS temp SET blog_comments.IdMember = temp.member_id WHERE temp.user_id = blog_comments.user_id_foreign;
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_comments DROP FOREIGN KEY `blog_comments_ibfk_2`
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_categories DROP FOREIGN KEY `blog_categories_ibfk_1`
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_categories DROP KEY `user_id_foreign`
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_comments DROP KEY `user_id_foreign`
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog ADD KEY (IdMember)
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_categories ADD KEY (IdMember)
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_comments ADD KEY (IdMember)
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog DROP COLUMN user_id_foreign
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_categories DROP COLUMN user_id_foreign
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog_comments DROP COLUMN user_id_foreign
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog DROP FOREIGN KEY `blog_ibfk_1`
SQL;

    $updates[] = <<<SQL
ALTER TABLE blog DROP KEY `user_id_foreign`
SQL;

    $updates[] = <<<SQL
ALTER TABLE trip ADD COLUMN IdMember INT COMMENT 'References members table'
SQL;

    $updates[] = <<<SQL
UPDATE trip, (SELECT user.id AS user_id, members.id AS member_id FROM user, members WHERE user.handle = members.username) AS temp SET trip.IdMember = temp.member_id WHERE temp.user_id = trip.user_id_foreign;
SQL;

    $updates[] = <<<SQL
ALTER TABLE trip DROP FOREIGN KEY `trip_ibfk_1`
SQL;

    $updates[] = <<<SQL
ALTER TABLE trip DROP KEY `user_id_foreign`
SQL;

    $updates[] = <<<SQL
ALTER TABLE trip DROP COLUMN user_id_foreign
SQL;

    $updates[] = <<<SQL
ALTER TABLE trip ADD KEY (IdMember)
SQL;

    $updates[] = <<<SQL
ALTER TABLE `blog_data` ADD PRIMARY KEY ( `blog_id` )
SQL;

    $updates[] = <<<SQL
ALTER TABLE `blog_data` DROP INDEX `blog_id`
SQL;

    $updates[] = <<<SQL
ALTER TABLE `gallery_comments` ADD PRIMARY KEY ( `id` )
SQL;

    $updates[] = <<<SQL
ALTER TABLE `gallery_comments` DROP INDEX `id`
SQL;

    $updates[] = <<<SQL
ALTER TABLE `trip_data` ADD PRIMARY KEY ( `trip_id` )
SQL;

    $updates[] = <<<SQL
ALTER TABLE `trip_data` DROP INDEX `trip_id`
SQL;

    $updates[] = "select now() ";
    $updates[] = "select now() ";
    $updates[] = "select now() ";
	
    $updates[] = "deleted from `volunteer_boards` where Name='translator_board'" ;

    $updates[] = "INSERT INTO `volunteer_boards` (`id`, `Name`, `updated`, `PurposeComment`, `TextContent`, `created`) 
	VALUES (NULL, 'translator_board', CURRENT_TIMESTAMP, 'used for admin words', '', '0000-00-00 00:00:00')" ;
	
    $updates[] = "CREATE TABLE `sqlforgroupsmembers` (
`IdGroup` INT NOT NULL COMMENT 'Id of the group ',
`IdQuery` INT NOT NULL COMMENT 'Id of the corresponding query',
`created` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
INDEX ( `IdGroup` , `IdQuery` )
) ENGINE = MYISAM COMMENT = 'Allows to define that some groups members will be allowed to run a specifc query'" ; 

    if (empty($res)) {
        $version = 0;
    } else {
        $row = mysql_fetch_assoc( $res );
        if (!empty($row)) {
            $version = (int)$row['version'];
        } else {
            bw_error("Error: Could not retrieve DB version.", true);
        }
    }
    
    assert( isset( $version ) );
    
    while (isset($updates[$version+1])) { // While they are updates to do
        print("updating DB to version ".($version+1)."\n<br>");
        
        if (empty($updates[$version+1])) {
            bw_error("The database needs update but it cannot be done automatically. Do the changes manually or get the latest DB from the repository (<a href=\"https://www.bewelcome.org/svn/develstuff/trunk/testdb/\">https://www.bewelcome.org/svn/develstuff/trunk/testdb/</a>).", true);
        }
        
        $qry = mysql_query($updates[$version+1]);
        $qry = mysql_query("UPDATE dbversion SET version=version+1");
        $version++;
    } // end of while they are updates to do
}

?>
