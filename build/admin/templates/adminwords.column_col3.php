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

<form method="post" action="" class="yform full">
    <fieldset>
        <legend>Admin Words</legend>
       
        
        <div class="subcolumns">
            <div class="c33l">
                 <div class="subcl type-text">
                    <label>Code</label>
                    <input type="text" size="25"/>
                 </div>
            </div>
            <div class="c33l">
                <div class="subcl type-select">
                    <label>Priority</label>
                    <select>
                        <option></option>
                    </select>
                </div>
            </div>
            <div class="c33l">
                <div class="subcl type-select">
                    <label>Language</label>
                    <select>
                        <option></option>
                    </select>
                </div>
            </div>
        </div>
                <div class="subcl type-check">
                    <input type="radio" id="translatable" />
                    <label for="translatable">Translatable</label>
                    <input type="radio" id="untranslatable"  />
                    <label for="untranslatable">Not Translatable</label>
                </div>

        <div class="type-text">
            <label>Description</label>
            <textarea cols="30" rows="6"></textarea>
        </div>
        
        <div class="type-text">
            <label>Translation</label>
            <textarea cols="30" rows="10"></textarea>
        </div>
    </fieldset>
</form>

