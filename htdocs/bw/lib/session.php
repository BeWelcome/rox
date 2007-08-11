<?php

function SetupSession()
{
	global $_SYSHCVOL;
	
	// using another dedicated session directory
	if (!empty($_SYSHCVOL['SessionDirectory']))
		session_save_path($_SYSHCVOL['SessionDirectory']); 

	session_cache_expire(30); // session will expire after 30 minutes

	ini_set ('session.name',SESSION_NAME);
	session_start();
	
	if (!isset ($_GET['showtransarray'])) 
	{
		$_SESSION['TranslationArray'] = array (); // initialize $_SESSION['TranslationArray'] if not currently switching to adminwords
	}
}

?>