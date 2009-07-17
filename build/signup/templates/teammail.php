<?php
 /*
 *  Signup Team Mail template
 */
 
?>
Candidate: <?=$vars['firstname']?> <?=$vars['lastname']?><br/>
Country: <?=$vars['countryname']?><br/>
City: <?=$vars['geonamename']?><br/>
E-mail: <?=$vars['email']?><br/>
Language used: <?=$language?><br/>
Feedback: <?=$vars['feedback']?><br/>
<br/>
<a href="<?=PVars::getObj('env')->baseuri?>bw/admin/adminaccepter.php">Check and accept this member</a>