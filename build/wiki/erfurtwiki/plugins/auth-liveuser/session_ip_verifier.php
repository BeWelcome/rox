<?
/* checks to see if the user's ssl_session_id or ip_address has changed.
   if it has, log them out.

   written by: Jeffrey Engleman
*/

$ewiki_plugins["init"][-6] = "ewiki_session_ip_verify";

function ewiki_session_ip_verify(){

  global $liveuser;
  
  $logindata=ewiki_liveuser_get_login_data();

  if($_SESSION['loginInfo']['ip_address']!==$logindata['ip_address'] || $_SESSION['loginInfo']['ssl_session_id']!==$logindata['ssl_session_id']){
    //they've changed...this is weird...log em out
    $liveuser->logout();
  }
  
}

?>