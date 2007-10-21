<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

/*
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


echo "<h1>Profile importer</h1>";

$p = new Profile;
$p->load();
$p->show_friends();


class Profile {
    // it's probably nicer to subclass DOMDocument
    function load() {
        $this->dom = new DOMDocument();
        $this->dom->load('csprofile.xml');
    }
    function show_friends() {
        show_friends($this->dom);
    }
}


function cs_id($id_cs) {
    return base_convert ($id_cs, 35, 10) / 12345;
}


function show_friends($dom) {
  $params = $dom->getElementsByTagName('friend');

  foreach ($params as $param) {
    # echo $param->getElementsByTagName('member')->nodeValue;
    echo $param->nodeValue . '<br / ><br />';
  }
}


?>