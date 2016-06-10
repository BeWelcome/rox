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
/** 
 * @author Felix van Hove <fvanhove@gmx.de>
 */

$words = new MOD_words($this->getSession());
$title = "Admin logs";

    $infoStyles = array(0 => "              <tr class=\"blank\" align=\"left\" valign=\"center\">\n",
                        1 => "              <tr class=\"highlight\" align=\"left\" valign=\"center\">\n");

?>
	<div id="col3"> 
		<div id="col3_content" class="clearfix"> 
			<div class="info">
				<table cellspacing="10" cellpadding="10" style="font-size:11px;">
					<tr>
					<?php
	if (empty($username)) {
		echo "              <th>Username</th>\n";
		echo "              <th>Type</th>\n";
		echo "              <th>Str</th>\n";
		echo "              <th>created</th>\n";
		echo "              <th>ip</th>\n";
	} else {
		echo "              <th colspan=4 align=center> Logs for ", LinkWithUsername(fUsername($username)), "</th>\n";
	}
	                ?>
					</tr>
					<?php
	$ii=0;
	while ($logs = $tData->fetch(PDB::FETCH_OBJ)) {
	    $ii++;
		echo $infoStyles[($ii%2)]; // this displays the <tr>
		if (!empty($logs->Username)) {
			echo "<td>";
			echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "&Type=" . $logs->Type . "\">" . $logs->Username . "</a>";
			echo "</td>";
		}
		echo "<td>";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "&Type=" . $logs->Type . "\">" . $logs->Type . "</a>";
		//		echo $logs->Type;
		echo "</td>";
		echo "<td>";
		echo $logs->Str;
		echo "</td>";
		echo "<td>$logs->created</td><td>&nbsp;";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "&ip=" . long2ip($logs->IpAddress) . "&Type=" . $logs->Type . "\">" . long2ip($logs->IpAddress) . "</a>";
		echo "</td>";
		echo "</tr>\n";
	}
	echo "          </table>\n<br>";
	if ($ii>0) {
	    echo $this->_pager($totalNumber, $vars);
	}

	echo "          <hr />\n";
	echo "          <table>\n";
	echo "            <form method='post' action='adminlogs.php'>\n";
	
	if ($level > 1) {
		echo "              <tr>\n";
		echo "                <td>Username</td><td><input type=\"text\" name=\"Username\" value=\"" . (!empty($username)?$username:'') . "\"></td>\n";
	} else {
		echo "              <tr>\n";
		echo "                <td>Username</td><td><input type=\"text\" name=\"Username\" readonly=\"readonly\" value=\"" . $username . "\"></td>";
	}
	echo "                <td>Type</td><td><input type=text name=type value=\"" . $vars['type'] . "\"></td>\n";
	echo "                <td>Ip</td><td><input type=text name=ip value=\"" . $vars['ip'] . "\"></td>\n";
	echo "              </tr>\n";
	echo "              <tr><td>    Having</td><td><input type=text name=andS1 value=\"" . $vars['andS1'] . "\"></td></tr>" ;
	echo "				<tr><td>and Having</td><td><input type=text name=andS2 value=\"" . $vars['andS2'] . "\"></td></tr>" ;
	echo "				<tr><td>and not Having</td><td><input type=text name=notAndS1 value=\"" . $vars['notAndS1'] . "\"></td></tr>" ;
	echo "				<tr><td>and not Having</td><td><input type=text name=notAndS2 value=\"" . $vars['notAndS2'] . "\"></td></tr>" ;
	echo "                <tr><td colspan=2 align=center>";
	?>
	
	        <input type="hidden" name="<?php 
echo $callbackId; ?>" value="1"/>
	
<?php
	echo "<input type=submit id=submit>";
	echo "</td>\n";
	echo "              </tr>\n";
	echo "            </form>\n";
	echo "          </table>\n";
	echo "        </div>\n";
?>
