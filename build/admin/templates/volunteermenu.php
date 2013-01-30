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
 *
 * @author fvanhove@gmx.de, Andreas (lemon-head)
 * @see /htdocs/bw/layout/menus.php method VolMenu
 *
 * Some of these admin tools could allow severe violations of member privacy.
 * We should take care of that as soon as possible!
 * JeanYves : they are protected by the right system, the only important question IMHO is to
 * discuss case by case, if needed, the relevance and the need for displaying the information then provide
 * still IMHO, this need is real and the displayed data are not, in the context they are used, privacy abuse
 */
    $R = MOD_right::get();
    $res = "";
    $request = PRequest::get()->request;
    $link = ""; // FIXME: all link checks should be transfered to be "rox style"
    if (count($request) > 1) {
        $link = $request[0] . '/' . $request[1];
    }
    $words = new MOD_words();

    echo
        '<ul>'
    ;
   echo
    '<li><a href="volunteer">'. $words->get("VolunteerpageLink") . '</a></li>';

    $array_of_items =
        array(
            array(
                'Words',
                'bw/admin/adminwords.php',
                'AdminWord',
                'Words management'
            ),
            array(
                'Accepter',
                'bw/admin/adminaccepter.php',
                'Pending ('.$numberPersonsToBeAccepted.')',
                'accept new member accounts'
            ),
            array(
                'Accepter',
                'bw/admin/adminmandatory.php',
                'AdminMandatory('.$numberPersonsToBeChecked.')',
                'check member accounts'
            ),
            array(
                'Grep',
                'bw/admin/admingrep.php',
                'AdminGrep',
                'grep files'
            ),
            array(
                'Group',
                'bw/admin/admingroups.php',
                'AdminGroups('.$numberPersonsToAcceptInGroup.')',
                'manage groups'
            ),
            array(
                'Flags',
                'bw/admin/adminflags.php',
                'AdminFlags',
                'administrate member flags'
            ),
            array(
                'Rights',
                'bw/admin/adminrights.php',
                'AdminRights',
                'manage admin rights of other members'
            ),
            array(
                'Logs',
                'bw/admin/adminlogs.php',
                'AdminLogs',
                'logs of member activity'
            ),
            array(
                'Comments',
                'bw/admin/admincomments.php',
                'AdminComments',
                'manage comments'
            ),
            array(
                'Pannel',
                'bw/admin/adminpanel.php',
                'AdminPanel',
                'managing panel'
            ),
            array(
                'Checker',
                'bw/admin/adminchecker.php',
                'AdminSpam('.$numberMessagesToBeChecked.'/'.$numberSpamToBeChecked.')',
                'check spam reports'
            ),
            array(
                'Debug',
                PVars::getObj('env')->baseuri . 'bw/admin/phplog.php?showerror=10',
                'php error log',
                'Show last 10 php errors in log'
            ),
            array(
                'MassMail',
                'admin/massmail',
                'Mass mailings',
                'broadcast messages'
            ),
            array(
                'Treasurer',
                'admin/treasurer',
                'Treasurer',
                'Manage donations, start/stop donation campaign'
            ),
            array(
                'SqlForVolunteers',
                'bw/admin/adminquery.php',
                'Queries for volunteers',
                'access to volunteers dedicated queries'
            ),
        )
    ;
    foreach($array_of_items as $item) {
        if ($R->hasRight($item[0])) {
            echo '<li><a href="'.$item[1].'" title="'.$item[3].'">'.$item[2].'</a></li>';
        }
    }
?>
</ul>




