<?php


/**
 * About page
 * Base class for other pages in about application
 *
 * @package externalcontent
 * @author Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ExternalContentPage extends RoxPageView
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
            $inclusion_url = $default_inclusion_url;
        }
        $widget->inclusion_url = $inclusion_url;
        // showing only the node with id="content"
        $widget->link_replace_callback = array($this, 'replaceLink');
        // echo '<pre>'; print_r($widget); echo '</pre>';
        $this->ecwidget = $widget;
    }

    protected function getStylesheets() {
        $stylesheets[] = 'styles/css/bewelcome.css';
        return $stylesheets;
    }

    protected function teaserHeadline()
    {

    }

    protected function column_col2()
    {

    }

    protected function column_col3()
    {
        $this->ecwidget->render('#content #loop_articles #loop_single');
    }

    protected function leftSidebar()
    {
        $this->ecwidget->render('#sidebar #post_meta #widgets #CommentForm');
    }

    function replaceLink($href) {
        $parsed = explode('?', $href);
        return
            'externalcontent?'.
            (empty($parsed[1]) ? '' : $parsed[1].'&amp;').
            'wp_url='.str_replace('/', ';', $parsed[0])
        ;
    }
}


?>
