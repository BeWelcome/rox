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
$words = new MOD_words($this->getSession());
?>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
      <!-- Content of left block -->
        <h2><a href="forums"><?php echo $words->get('CommunityDiscussions') ?></a></h2>
        <p><?php echo $words->get('ExploreForumDescription') ?></p>
        </div> <!-- subcl -->
  </div> <!-- c33l -->

  <div class="c50r">
    <div class="subcr">
      <!-- Content of right block -->
        <h2><a href="groups/forums"><?php echo $words->get('Groups') ?></a></h2>
        <p><?php echo $words->get('ExploreGroupsDescription') ?></p>
    </div> <!-- subcr -->
  </div> <!-- c33r -->
</div>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
      <!-- Content of left block -->
        <h2><a href="activities"><?php echo $words->get('Activities') ?></a></h2>
        <p><?php echo $words->get('ExploreActivitiesDescription') ?></p>
        </div> <!-- subcl -->
  </div> <!-- c33l -->

  <div class="c50r">
    <div class="subcr">
      <!-- Content of right block -->
        <h2><a href="suggestions"><?php echo $words->get('Suggestions') ?></a></h2>
        <p><?php echo $words->get('ExploreSuggestionsDescription') ?></p>
    </div> <!-- subcr -->
  </div> <!-- c33r -->
</div>

<div class="subcolumns">
<div class="c50l">
<div class="subcl">
      <!-- Content of left block -->
        <h2><a href="trip"><?php echo $words->get('Trips') ?></a></h2>
        <p><?php echo $words->get('ExploreTripsDescription') ?></p>
    </div> <!-- subcl -->
  </div> <!-- c33l -->

  <div class="c50r">
     <div class="subcr">
        <!-- Content of right block -->
        <h2><a href="wiki"><?php echo $words->get('Wiki') ?></a></h2>
        <p><?php echo $words->get('ExploreWikiDescription') ?></p>
     </div> <!-- subcr -->
  </div> <!-- c33r -->
</div> <!-- subcolumns -->

<div class="subcolumns">
<div class="c50l">
<div class="subcl">
      <!-- Content of left block -->
        <h2><a href="blog"><?php echo $words->get('Blogs') ?></a></h2>
        <p><?php echo $words->get('ExploreBlogsDescription') ?></p>
    </div> <!-- subcl -->
  </div> <!-- c33l -->

</div> <!-- subcolumns -->





