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

    echo '<ul class="dropdown-menu">';
    echo '<li><a href="volunteer">'. $words->get("VolunteerpageLink") . '</a></li>';

    $array_of_items =
        array(
            array(
                'Words',
                'AdminWord',
                'admin/word'
            ),
            array(
                'Flags',
                'AdminFlags',
                'admin/flags'
            ),
            array(
                'Rights',
                'AdminRights',
                'admin/rights'
            ),
            array(
                'Logs',
                'AdminLogs',
                'bw/admin/adminlogs.php'
            ),
            array(
                'Comments',
                'AdminComments',
                'bw/admin/admincomments.php'
            ),
            array(
                'NewMembersBeWelcome',
                'AdminNewMembers',
                'admin/newmembers',
            ),
            array(
                'MassMail',
                'AdminMassMail',
                'admin/massmail'
            ),
            array(
                'Treasurer',
                'AdminTreasurer',
                'admin/treasurer'
            ),
            array(
                'FAQ',
                'AdminFAQ',
                'bw/faq.php'
            ),
            array(
                'SqlForVolunteers',
                'AdminSqlForVolunteers',
                'bw/admin/adminquery.php'
            ),
            array(
                'ManageSubscriptions',
                'AdminManageSubscriptions',
                'admin/subscriptions'
            ),
        )
    ;

    foreach($array_of_items as $item) {
        if ($R->hasRight($item[0])) {
            $text = $words->getSilent($item[1]);
            // $info = $words->getSilent($item[1] . 'Info');
            echo '<li><a href="'. $item[2] . '">' . $text . '</a></li>';
        }
    }
?>
</ul>




