#!/usr/bin/perl
#
# PHP versions before 4.1 dont know the superglobals for incoming variables
# as introduced by later versions. PHP5 then incompatibilized further with
# making the older equivalents less accessible.
# 
# Therefore you have to run a rewrite script over ewiki and its plugins to
# make them compatible, if your provider only has an outdated version on
# the webserver.
#
# - exec this from the ewiki/ base directory
#
# - then place following into your config.php script:
#
#      $HTTP_POST_VARS = array_merge($HTTP_POST_VARS, $HTTP_GET_VARS);
#
# - a few plugins still won't work correctly under PHP 4.0
#
# - load the "plugins/lib/upgrade.php" script for enhancing compatibility
#


foreach $file (split /\s+/,`find .|grep php`) {

  if ($file =~ /old_php_compat/) {
     next;
  }
  print "$file\n";

  open F, "$file";
  $text = "";
  while (<F>) {
     $text .= $_;
  }
  close F;

  $text =~ s/\$_GET/\$HTTP_GET_VARS/;
  $text =~ s/\$_POST/\$HTTP_POST_VARS/;
  $text =~ s/\$_REQUEST/\$HTTP_POST_VARS/;
  $text =~ s/\$_SERVER/\$HTTP_SERVER_VARS/;
  $text =~ s/\$_FILES/\$HTTP_POST_FILES/;
  $text =~ s/\$_COOKIE/\$HTTP_COOKIE_VARS/;
  $text =~ s/\$_ENV/\$HTTP_ENV_VARS/;

  open F, ">$file";
  print F $text;
  close F;
}

