<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class NotifyBasePage extends PageWithActiveSkin
{

    protected function leftSidebar()
    {

    }
    
    
    #[\Override]
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <h1><a href="notify">Notify</a></h1>
        </div>
        <?php
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'groups';
    }
    
    #[\Override]
    protected function getSubmenuItems()
    {

    }



}

?>
