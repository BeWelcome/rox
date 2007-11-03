<?php
$ewiki_plugins["handler"][] = "ewiki_password_status";

function ewiki_password_status($id, &$data, $action){
  global $liveuser;
  $passwd_status=ewiki_get_uservar("passwdstatus", 'expired');
  if($passwd_status!='good' && $id!="Logout" && $id!="ChangePassword" && $liveuser->isLoggedIn()){
    return ewiki_make_title($id, "Change Password:")."<p>You password has expired</p>".ewiki_t("CHPW_FORM");
  }
  return 0;
}

?>