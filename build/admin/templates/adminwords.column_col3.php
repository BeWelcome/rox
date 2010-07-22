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
    elements: "translation",
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
//-->
</script>

<form method="post" action="" class="yform full">
        
        <div class="type-text">
            <label>English source</label>
            <textarea cols="30" rows="5" readonly="readonly">FIXME</textarea>
        </div>
        
        

        <div class="type-text">
            <label>Description</label>
            <textarea cols="30" rows="3">FIXME</textarea>
        </div>
        
        <div class="type-text">
            <label>Translation</label>
            <textarea id="translation" name="txt" cols="30" rows="10">FIXME</textarea>
        </div>

        <div class="subcolumns">
            <div class="c25l">
                <div class="subcl type-select">
                    <label>Language</label>
                    <select>
                        <option>FIXME</option>
                    </select>
                </div>
            </div>

            <div class="c75l">
                <div class="subcolumns">
                    <div class="c75l">
                        <div class="subcl type-text">
                            <label>Code</label>
                            <input name="code" type="text" size="25"/>
                         </div>
                    </div>
                    <div class="c25r">
                        <div class="subcr type-button inline">
                            <br />
                            <input type="submit" value="find" id="find" name="find" />
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="subcolumns">
            <div class="c33l">
                <div class="subcl type-select">
                    <label>Priority</label>
                    <select>
                        <option>FIXME High</option>
                        <option>FIXME Low</option>
                    </select>
                </div>
            </div>
            <div class="c66r">
                <div class="subcr type-check">
                    <br />
                    <input type="radio" id="translatable" />
                    <label for="translatable">Translatable</label>
                    <input type="radio" id="untranslatable"  />
                    <label for="untranslatable">Not Translatable</label>
                </div>
            </div>
        </div>

        <div class="type-button center">
          <input type="submit" value="delete" id="delete" name="delete" />
          <input type="submit" value="submit" id="submit" name="submit" />
        </div>

</form>

