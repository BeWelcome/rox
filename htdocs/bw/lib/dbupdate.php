<?php

/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foobar is distributed in the hope that it will be useful,
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
	// $updates[7] = "blahblah"
	
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
