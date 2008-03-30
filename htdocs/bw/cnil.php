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

require_once "lib/init.php";
require_once "layout/menus.php";

global $title;
$title = ww('cnil');
require_once "layout/header.php";

Menu1("", ""); // Displays the top menu
Menu2("cnil.php", ww('MainPage')); // Displays the second menu
DisplayHeaderShortUserContent(ww("cnil"));

?>

<div id="col3">
<div id="col3_content" class="clearfix" >
<div class="info">
<p>CNIL registration in progress - Enregistrement &agrave; la CNIL en cours<br />
<br />
<?php echo ww("SignupEmail"); ?>: info@bevolunteer.org<br /> </p>

<br />
<p>The layout is based on <a href="http://www.yaml.de/">YAML</a> &copy; 2005-2006 by <a href="http://www.highresolution.info">Dirk Jesse</a></p>
</div> <!-- #info: - end -->
</div> <!-- #col3_content: - end -->
<div id="ie_clearing">&nbsp;</div>
</div>
<!-- #col3: - End -->

<?php
require_once "layout/footer.php";
?>
