<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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

//------------------------------------------------------------------------------------
/**
 * base class for lastcomment page,
 *
 */

class LastCommentsPage extends PageWithActiveSkin {

    /**
    Constructor

    @$_Data has been previously filled with the dynamy data to display
    @type is the request parameter 1 and allows to choose for a speciic tem^late

    **/
    public function __construct($_Data,$type="LastComments") {
        $this->BW_Right = MOD_right::get();
        $this->Data=$_Data;
        $this->Type=$type ;
    }

    protected function leftSidebar() {
/*
        ?>
        <h3><?= $this->getWords()->get('Actions'); ?></h3>
        <ul class="linklist">
        <?php
        if ($this->Type=="LastComments") {
            ?>
                <li><a href="lastcomments/commentofthemoment"><?= $this->getWords()->get('commentofthemomentTitlePage'); ?></a></li>
            <?php
        }
        else if ($this->Type=="commentofthemoment") {
            ?>
                <li><a href="lastcomments"><?= $this->getWords()->get('LastCommentsTitlePage'); ?></a></li>
            <?php
        }
        ?>
            <li><a href="bw/viewcomments.php?MyComment=1"><?= $this->getWords()->get('MyComments'); ?></a></li>
        </ul>
        <?
*/
    }



    protected function getLastCommentsTitle() {

        if ($this->Type=="LastComments") {
            return $this->getWords()->get('LastCommentsTitlePage');
        }
        else if ($this->Type=="commentofthemoment") {
            return $this->getWords()->get('commentofthemomentTitlePage');
        }
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return  $this->getLastCommentsTitle();
    }


    protected function column_col2() {
    }

    protected function column_col3() {
        $data=$this->Data ;
        $styles = array( 'highlight', 'blank' ); // alternating background for table rows
        $words = new MOD_words();

        $iiMax = count($data) ; // This retrieve the number of comments
        if ($this->Type=="LastComments") {
            require ('templates/lastcomments.php');
        }
        else if ($this->Type=="commentofthemoment") {
            require ('templates/commentofthemoment.php');
        }

    } // end of column_col3


    protected function teaserContent() {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
            <div id="teaser_l1">
                <h1><?= $this->getLastCommentsTitle();?></h1>
            </div>
        </div>
        <?php
    }

    protected function getTopmenuActiveItem()    {
        return ;


    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets(); 
       $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
       return $stylesheets;
    }

    protected function getStylesheetPatches() {
       $stylesheets[] = 'styles/css/minimal/screen/patches/patch_3col.css';
       return $stylesheets;
    }

}

?>
