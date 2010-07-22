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
 * @author Matthias Heﬂ <globetrotter_tt>
 */
$words = new MOD_words();

?>

<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'/script/tiny_mce';
tinyMCE.init({
    mode: "exact",
    elements: "massmailbody",
    plugins : "advimage,preview,fullscreen",
    theme: "advanced",
    content_css : "styles/css/minimal/screen/content_minimal.css",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1 : "formatselect,bold,italic,underline,strikethrough,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,forecolor,backcolor,separator,link,image,charmap,separator,preview,cleanup,code,fullscreen",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true,
    theme_advanced_resize_horizontal : false,
    plugin_preview_width : "800",
    plugin_preview_height : "600",
});

function toggleEditor(id) {
    if (!tinyMCE.get(id))
        tinyMCE.execCommand('mceAddControl', false, id);
    else
        tinyMCE.execCommand('mceRemoveControl', false, id);
}
//-->
</script>

<form method="post" action="" class="yform full">
        <div class="type-text">
            <label for="massmailsubject"><?php echo $words->get('MassmailSubject')?></label>
            <input id="Massmailsubject" type="text" />
        </div>
        
        <div class="type-text">
            <label for="massmailbody"><?php echo $words->get('MassmailBody')?></label>
            
            <textarea id="massmailbody" name="massmailbody" cols="65" rows="15"></textarea>
            <p class="small"><?php echo $words->get('MassmailBodyDesc')?><a class="float_right" href="javascript:toggleEditor('massmailbody');">Toogle Editor</a></p>
            
        </div>

        <div class="type-text">
            <label for="massmailcode"><?php echo $words->get('MassmailCode')?></label>
            <input id="Massmailcode" type="text" />
            <p class="small"><?php echo $words->get('MassmailCodeDesc')?></p>
        </div>
        
        <div class="type-text">
            <label for="massmaildescription"><?php echo $words->get('MassmailDescription')?></label>
            <textarea id="massmaildescription" cols="65" rows="5"></textarea>
        </div>
</form>

