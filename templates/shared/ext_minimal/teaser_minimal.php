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

<div id="teaser" class="clearfix">

        <div style="float:left; padding: 40px 0; width: 60%">
        <h1 style="font-size: 50px;"><span id="something" style="display:none;" >Share something</span><br /> <span id="real" style="display:none;" >REAL.</span>&nbsp;</h1>
        <h2 style="font-size: 20px;">No, not files, not pictures. BeWelcome is a culture crossing network that lets you share a place to sleep, meet up and help others on their way.</h2>
        </div>
        <div style="float:right; width: 40%">
            <img src="images/page/share4.jpg">
        </div>
        <script type="text/javascript">
        <!--
        function realeffect() {
            new Effect.toggle('real', 'appear', {duration: 2})
        }
        window.onload = function () {
            new Effect.toggle('something', 'appear', {duration: 2});
            setTimeout('realeffect()',2000);
        };
        
        // -->
        </script>
    
</div>