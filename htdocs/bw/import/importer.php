<?php

# (c) 2007 Kasper Souren
# GPL (please add the whole header)

# PHP5, so we're using the DOM extension
# http://www.php.net/manual/en/ref.dom.php


/*
 I don't know too much about the BW framework, and I don't have
 the thing installed yet. So this will just be some hackerish stuff 
 to get started at least.
   20070728 gka
*/


$dom = get_profile();
echo "<h1>Profile importer</h1>";
show_friends($dom);



function get_profile() {

  # the profile XML data should come from the Java importer
  # see http://bevolunteer.org/wiki/Java_importer

  $doc = new DOMDocument();
  $doc->load('csprofile.xml');
  # echo $doc->saveXML();

  # in case we'll have a DTD one day:
  # if (false and $doc->validate()) 
  #  echo "valid XML";

  return $doc;	
}

	


function show_friends($dom) {
  $params = $dom->getElementsByTagName('friend');

  foreach ($params as $param) {
    # echo $param->getElementsByTagName('member')->nodeValue;
    echo $param->nodeValue . '<br / ><br />';
  }
}





?>