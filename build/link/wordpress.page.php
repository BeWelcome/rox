<?php


class HellouniverseWordpressPage extends HellouniversePage
{
    function setExternalURL($default_inclusion_url, $GET)
    {
        $widget = new ExternalContentWidget();
        if (isset($GET['wp_url'])) {
            $inclusion_url = str_replace(';', '/', $GET['wp_url']);
            unset($GET['wp_url']);
            if (!empty($GET)) {
                $inclusion_url.='?'.http_build_query($GET);
            }
        } else {
            $inclusion_url = 'http://blogs.bevolunteer.org/';
        }
        $widget->inclusion_url = $inclusion_url;
        // showing only the node with id="content"
        $widget->link_replace_callback = array($this, 'replaceLink');
        // echo '<pre>'; print_r($widget); echo '</pre>';
        $this->ecwidget = $widget;
    }
    
    protected function column_col3()
    {
        $this->ecwidget->render('#content');
    }
    
    protected function leftSidebar()
    {
        $this->ecwidget->render('#sidebar');
    }
    
    function replaceLink($href) {
        $parsed = explode('?', $href);
        return
            'hellouniverse/wordpress?'.
            (empty($parsed[1]) ? '' : $parsed[1].'&amp;').
            'wp_url='.str_replace('/', ';', $parsed[0])
        ;
    }
}


?>