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
     * display the content of the record of the database for a location
     *
     * @author JeanYves
     * @author Fake51
     */
		
?>


<h2>Location records description</h2>
<p>
<?php
    if (empty($data))
    {
		echo "sorry no record found" ;
	}
	else
    {
		echo "<table align=\"left\"><tr><th>id</th><th>name</th><th>bw geo Type</th><th>Class</th><th>admincode</th><th>usage</th><th>Other names</th></tr>\n" ;
		foreach ($data as $loc)
        {
			echo <<<HTML
            <tr>
                <td>{$loc->geonameid}</td>
                <td>{$loc->name}</td>
                <td>{$loc->fclass} {$loc->fcode}</td>
                <td>{$loc->fk_admincode}</td>
                <td>
HTML;
			foreach ($loc->getUsageForAllTypes()  as $usage)
            {
                switch($usage->typeId)
                {
                    case 1:
                        echo "members ";
                        break;
                    case 2:
                        echo "blogs ";
                        break;
                    case 3:
                        echo "galleries ";
                        break;
                    default:
                        echo $usage->typeId ;
				}
				echo " - {$usage->count}<br />";
			}
			echo "</td>" ;
			echo "<td>" ;
			foreach ($loc->alternate_names as $alternate_name)
            {
				echo $alternate_name->alternateName," (",$alternate_name->isoLanguage,")<br />" ;
			}
			echo "</td>" ;
			echo "</tr>" ;
		}
		echo "</table>\n"  ;
	}
?>
</p>
