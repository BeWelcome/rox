<?php

/**
 * Hello universe controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MediawikiController extends RoxControllerBase
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args = false)
    {
        $request = $args->request;
        $page = new MediawikiPage();
        $mwiki_title = isset($args->request[1]) ? $args->request[1] : 'Main_Page';
        $mwiki_title = str_replace(" ", "_", $mwiki_title); 
        switch (isset($request[0]) ? $request[0] : '') {
            case 'ocswiki':
                $page->base_url = 'http://www.opencouchsurfing.org/w/';
                $page->wikiname = 'OpenCouchSurfing Wiki';
                break;
            case 'hitchwiki':
                $page->base_url = 'http://en.hitchwiki.org/';
                $page->wikiname = 'Hitchwiki';
				$page->replace_url = 'http://en.hitchwiki.org?title=';
                break;
            case 'bvwiki':
            default:
                $page->wikiname = 'BeVolunteer Wiki';
                $page->base_url = 'http://www.bevolunteer.org/wiki/';
        }
        $page->base_url .= 'index.php?title='.$mwiki_title;
        $page->inclusion_url = $page->base_url .'&action=render';
        $page->history_url = $page->base_url .'&action=history';
        $page->edit_url = $page->base_url .'&action=edit';
        $page->headline = str_replace("_", " ", $mwiki_title);
        return $page;
    }

}


?>
