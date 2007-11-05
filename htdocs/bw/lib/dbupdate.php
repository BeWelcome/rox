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


	// $updates[20] = "blahblah"
	
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
