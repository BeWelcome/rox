<?php

function SetupSession()
{
	if (!isset ($_GET['showtransarray'])) 
	{
		$_SESSION['TranslationArray'] = array (); // initialize $_SESSION['TranslationArray'] if not currently switching to adminwords
	}
}

?>