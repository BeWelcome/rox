<?php

/**
 * This page allows to create a new group
 *
 */
class NotifyAdminPage extends NotifyBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>

        <div>
            <h1><a href="notify">Notes</a> &raquo; <a href="">Admininstrate notes</a></h1>
        </div>

        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'admin';
    }
    
    protected function notesActions($callback_tag)
    {
        $words = $this->getWords();
        require_once 'templates/actions.php';
    }    
    
    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('NotifyController', 'NotifyAdminCallback');
                
        $formstart = '<form name="msgform" id="msgform" action="'.$page_url.'" method="post">';
        $formstart .= $callback_tag;
        $formend = '</form>';
        
        $widget = $layoutkit->createWidget(new NotifyAdminWidget());
        $widget->model = $model;
        $widget->items_per_page = 20;
        $widget->active_page = $this->active_page;
        $widget->visible_range = 2;
          
        ?>

        <div id="notes">
            <h3>Administer notes</h3>
            <form action="<?=$page_url?>" method="post">
                <?php
                if ($widget->needsPagination()) {
                    $widget->showPagination();
                }
                ?>
                <? $widget->render() ?>
                <?php
                if ($widget->needsPagination()) {
                    $widget->showPagination();
                }
                ?>
                <input type="hidden" name="action" value="delete"/>
                <?=$callback_tag?>
                <input type="submit" class="button" name="button" value="Delete" id="button" />
            </form>
                <hr />
            <form action="<?=$page_url?>" method="post">
                <? $this->notesActions($callback_tag)?>
            </form>
        </div>
        <?php
    }

}

?>
