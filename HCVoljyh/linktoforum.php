<?php
// This page is intended to link to the TB forum

include "lib/dbaccess.php" ;

// here define the link 
$linkname="link_to_something.php" ;

if (IsLogged()) { // if the memebr is logged send link with parameters to log on TB 
  // We will not use the password stored but a constant created one for this member
  $password=md5($_SESSION["Username"].$_SESSION["IdMember"]) ;
  header("Location: ".$linkname."?username=".$_SESSION["Username"]."&pw=".$password) ;
  exit(0) ;
}
else {
  header("Location: ".$linkname) ;  // link without being log for public forum only
  exit(0) ;
}

?>
