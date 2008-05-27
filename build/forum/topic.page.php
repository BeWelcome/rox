<?php


class ForumTopicPage extends ForumBasePage
{
    protected function column_col3()
    {
        $widget = new ForumTopicWidget();
        $widget->model = $this->model;
        $widget->topic = $this->topic;
        $widget->active_page = $this->active_page;
        
        // only to test the pagination
        // TODO: set to a higher value when finished.
        $widget->items_per_page = 4;
        
        if ($widget->needsPagination()) {
            $widget->showPagination();
            echo '<br style="clear:both">';
            $widget->render();
            $widget->showPagination();
            echo '<br style="clear:both">';
        } else {
            $widget->render();
        }
        
        echo '<pre>Tags:<br>'; print_r($this->topic->tags); echo '</pre>';
    }
}


?>