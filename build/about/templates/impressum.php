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
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>
    
<h3><?=$words->get('LegalNote'); ?></h3>
    <p><?=$words->get('BeWelcomeOwner'); ?></p>
    <p><strong><?=$words->get('Address'); ?>:</strong><br />
        BeVolunteer<br />
        c/o Jean-Yves Hegron<br />
        19 rue de Paris<br />
        35500 Vitre<br />
        France
    </p>
    <p><strong><?=$words->get('SignupEmail'); ?>: </strong><script type="text/javascript"><!--
            var summvvx = ['i','c','o','n','o','e','.','o','n','a','s','@','n','e','t','o','a','e','a','e','i','u','g','n','"','.','r',' ','@','=','a','l','u','f','l',':','"','"',' ','e','e','l','s','f','o','e','r','b','l','<','>','>','v','i','a','l','o','t','m','i','b','t','g','r','"','m','=','h','r','r','/','e','f','v','<','o'];var kcriicv = [48,38,55,17,24,58,32,60,27,40,42,20,53,65,64,69,1,66,10,5,16,62,35,63,44,68,67,37,56,7,74,61,26,6,25,15,50,8,2,45,30,49,41,18,33,22,4,21,12,72,75,51,59,52,47,39,14,28,9,11,57,13,71,31,36,46,43,3,70,34,73,29,54,23,0,19];var gjzaytd= new Array();for(var i=0;i<kcriicv.length;i++){gjzaytd[kcriicv[i]] = summvvx[i]; }for(var i=0;i<gjzaytd.length;i++){document.write(gjzaytd[i]);}
            // --></script>
<noscript><?= $words->get('EmailAddressNoJavascript'); ?></noscript>
</p>
    <p><strong><?=$words->get('PhoneNumber'); ?>: </strong>+49 211 26130480</p>
    <p><?=$words->get('YamlLayout'); ?></p>
