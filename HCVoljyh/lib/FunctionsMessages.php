<?php


//------------------------------------------------------------------------------
// This library file contains message relative files 
//------------------------------------------------------------------------------



//------------------------------------------------------------------------------
// ComputeSpamCheck read a message in the database and according to specific rules
// set the SpamInfo  
Function ComputeSpamCheck($IdMes) {
  $rr=LoadRow("select * from messages where id=".$IdMes) ;
	if (isset($rr->id)) {
	  $str="update messages set where id=".$IdMes." and sent=
	  // to do : implement spamchecking verification
	}
} // end of ComputeSpamCheck


?>
