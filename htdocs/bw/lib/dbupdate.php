<?php
/*
 * Created on 6.7.2007
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
			bw_error("The database needs update but it cannot be done automatically. Do the changes manually or get the latest DB from the repository.", true);
		
		$qry = sql_query($updates[$version+1]);
		$qry = sql_query("UPDATE dbversion SET version=version+1");
		$version++;
	}
}

?>
