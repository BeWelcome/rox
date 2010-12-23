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
chdir("..") ;
require_once "lib/init.php";
require_once "layout/error.php";

// test if is logged, if not logged and forward to the current page
MustBeAdmin(); // need to be log
echo "<h2>phpinfo logged as [".$_SESSION["Username"]."]</h2>" ;
echo "<p>This version was updated and committed with Git by jeanYves 23/12/2010</p>" ;
phpinfo();

?>
