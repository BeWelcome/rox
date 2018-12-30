<?php


class MessagesPageWithMailbox extends MessagesBasePage
{
    protected function getMailboxWidget()
    {
        $widget = $this->layoutkit->createWidget($this->getMailboxWidgetClassname());
        $widget->delegate = $this;  // delegate
        $widget->delegate_prefix = 'mailbox';
        $widget->model = $this->getModel();
        $widget->model->sort_element = $this->sort_element;
        $widget->model->sort_dir = $this->sort_dir;
        $widget->items_per_page = 20;
        $widget->active_page = $this->active_page;
        $widget->visible_range = 2;
        return $widget;
    }
    
    protected function messageActions()
    {
        $request = PRequest::get()->request;
        $message_page = (isset($request[1])) ? $request[1] : '';
        $words = $this->getWords();
        require SCRIPT_BASE . 'build/messages/templates/actions.php';
    }
        
    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();
        
        $page_url = PVars::getObj('env')->baseuri . htmlspecialchars(implode('/', PRequest::get()->request), ENT_QUOTES);
        
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MessagesController', 'mailBoxCallback');
        
        $this->mailboxDescription();
        $formstart = '<div class="col-12"><form name="msgform" id="msgform" action="'.$page_url.'" method="post">';
        $formstart .= $callback_tag;
        $formend = '</form></div>';
        $widget = $this->getMailboxWidget();
        if ($widget->needsPagination()) {
            echo $formstart;
            echo '<div class="subcolumns">';
            echo '<div class="c50l">';
            $this->messageActions();
            echo '</div>';
            echo '<div class="c50r">';
            $widget->showPagination();
            echo '</div>';
            echo '</div>';
            $widget->render();
            echo '<div class="subcolumns">';
            echo '<div class="c50l">';
            $this->messageActions();
            echo '</div>';
            echo '<div class="c50r">';
            $widget->showPagination();
            echo '</div>';
            echo '</div>';
            echo $formend;
        } else {
            echo $formstart;
            $this->messageActions();
            $widget->render();
            $this->messageActions();
            echo $formend;
        }
    }
      
}
