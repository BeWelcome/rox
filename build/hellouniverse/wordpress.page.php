<?php


class HellouniverseWordpressPage extends HellouniversePage
{
    protected function column_col3()
    {
        $widget = new ExternalContentWidget();
        $widget->inclusion_url = 'http://blogs.bevolunteer.org/';
        // showing only the node with id="content"
        $widget->link_replace_callback = array($this, 'replaceLink');
        $widget->render('#content');
    }
    
    function replaceLink($href) {
        return 'hellouniverse/wordpress';
    }
}


?>