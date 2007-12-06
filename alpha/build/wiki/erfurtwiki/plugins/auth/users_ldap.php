<?php

/*
   Check username and password by connecting to LDAP server.
*/


#-- config
define("EWIKI_LDAP_SERVER", "ldap.example.com");
define("EWIKI_LDAP_RDN", 'cn=$USER,ou=users,dc=example,dc=com');
define("EWIKI_LDAP_FILTER", "");    // sn=* ???
define("EWIKI_LDAP_RING", 2);


#-- glue
$ewiki_plugins["auth_userdb"][] = "ewiki_auth_userdb_ldap";



function ewiki_auth_userdb_ldap($username, $password=NULL) {

   #-- connect   
   if ($conn = ldap_connect(EWIKI_LDAP_SERVER)) {

      #-- vars
      $rdn = preg_replace('/[$%_]+\{USER\}|[$%]+USER[$%]?/i', $username, EWIKI_LDAP_RDN);
      $search = EWIKI_LDAP_SEARCH;

      #-- bind to domain
      if (ldap_bind($conn, $rdn, $password)) {

         #-- connected == authenticated
         if (!$search || ldap_count_entries($conn, ldap_search($conn, $rdn, $search)) ) {

            ldap_close($conn);

            #-- return password array() as true value for userdb plugins
            return(array($password, EWIKI_LDAP_RING));
         }

      }

      ldap_close($conn);
   }
   return(false);
}

?>