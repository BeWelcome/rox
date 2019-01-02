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
?>

    <div class="col-12 col-md-6 col-lg-4 py-2">
        <div class="card">
            <a href="/forums" class="list-group-item list-group-item-action">
                <h5 class="list-group-item-heading"><i class="fa fa-comment mr-1"></i><?php echo $words->get('CommunityDiscussions') ?></h5>
                <p class="list-group-item-text"><?php echo $words->get('ExploreForumDescription') ?></p>
            </a>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 py-2">
        <div class="card">
            <a href="/groups/search" class="list-group-item list-group-item-action">
                <h5 class="list-group-item-heading"><i class="fa fa-users mr-1"></i><?php echo $words->get('Groups') ?></h5>
                <p class="list-group-item-text"><?php echo $words->get('ExploreGroupsDescription') ?></p>
            </a>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 py-2">
        <div class="card">
            <a href="/activities" class="list-group-item list-group-item-action">
                <h5 class="list-group-item-heading"><i class="fa fa-calendar mr-1"></i><?php echo $words->get('Activities') ?></h5>
                <p class="list-group-item-text"><?php echo $words->get('ExploreActivitiesDescription') ?></p>
            </a>
        </div>
    </div>