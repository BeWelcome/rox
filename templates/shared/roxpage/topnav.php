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
$words = new MOD_words();

$model = new VolunteerbarModel();

$numberReportedComments = -1235711; // $model->getNumberReportedComments();
$numberSpamToBeChecked = -1235711; // $model->getNumberSpamToBeChecked() ;

$R = MOD_right::get();
?>

<?php
if ($logged_in) {
    $roxModel = new RoxModelBase();
    if ($R->hasRight('Comments') || ($R->hasRight('Checker'))) {
        echo '<div class="container-lg"><div class="row"><ul class="nav nav-inline pull-right mt-1 mb-1">';
    }
    if ($R->hasRight('Comments')) {
        echo '<li class="nav-item"><a class="nav-link" href="admin/comment/negative" title="Review negative comments">Negative comments <span class="badge badge-primary">' . $numberReportedComments . '</span></a></li>';
    }
    if ($R->hasRight('Checker')) {
        echo '<li class="nav-item"><a class="nav-link" href="admin/checker" title="Review messages reported by users as spam">Reported messages <span class="badge badge-primary">' . $numberSpamToBeChecked . '</span></a></li>';
    }
    if ($R->hasRight('Comments') || ($R->hasRight('Checker'))) {
        echo '</ul></div></div></div>';
    }
}