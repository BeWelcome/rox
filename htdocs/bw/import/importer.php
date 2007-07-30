<?php

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * PHP version 5
 *
 * LICENSE: 

   Foobar is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   Foobar is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.

 *
 * @author     Kasper Souren <kasper.souren@gmail.com>
 * @copyright  2007 Kasper Souren
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version    CVS: $Id:$
 * @link       http://www.bevolunteer.org/wiki/Java_importer
 */


# PHP5, so we're using the DOM extension
# http://www.php.net/manual/en/ref.dom.php

/*
 I don't know too much about the BW framework, and I don't have
 the thing installed yet. So this will just be some hackerish stuff 
 to get started at least.
   20070728 gka
*/


require_once "../lib/init.php";
require_once "../lib/FunctionsLogin.php";
require_once "../layout/error.php";
require_once "../lib/prepare_profile_header.php";


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