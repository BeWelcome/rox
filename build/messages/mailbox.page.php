<?php


class MessagesPageWithMailbox extends MessagesBasePage
{
    protected function getMailboxWidget()
    {
        $widget = $this->layoutkit->createWidget($this->getMailboxWidgetClassname());
        $widget->delegate = $this;  // delegate
        $widget->delegate_prefix = 'mailbox';
        $widget->model = $this->getModel();
        $widget->items_per_page = 7;
        $widget->active_page = $this->active_page;
        $widget->visible_range = 2;
        echo 'widget';
        return $widget;
    }
        
    protected function column_col3()
    {
        $this->mailboxDescription();
        
        $widget = $this->getMailboxWidget();
        if ($widget->needsPagination()) {
            $widget->showPagination();
            echo '<br style="clear:both">';
            $widget->render();
            $widget->showPagination();
            echo '<br style="clear:both">';
        } else {
            $widget->render();
        }
    }
}



?>