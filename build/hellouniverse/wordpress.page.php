<?php


class HellouniverseWordpressPage extends HellouniversePage
{
    protected function column_col3()
    {
        $widget = new ExternalContentWidget();
        $GET = $this->get;
        if (isset($GET['wp_url'])) {
            $inclusion_url = str_replace(';', '/', $this->get['wp_url']);
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
        $widget->render('#content');
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