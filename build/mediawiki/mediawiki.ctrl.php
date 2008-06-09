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
            case 'press':
                $mwiki_title = "Press";
                /* no break here */
            case 'bvwiki':
            default:
                $page->wikiname = 'BeVolunteer Wiki';
                $page->base_url = 'http://www.bevolunteer.org/wiki/';
        }

        $page->base_url .= 'index.php?title='.$mwiki_title;
        $page->inclusion_url = $page->base_url .'&action=render';

        $page->contents = file_get_contents($page->inclusion_url);

	if (preg_match('/(\<span class="redirectText">)(\<a href="(.*)" title="(.*)">)(.*)\<\/a>\<\/span>/', $page->contents, $matches)) {
	  //if (preg_match('/\<span class="redirectText">\<a href="http://www.bevolunteer.org/wiki/Our_mission_and_objectives" title="Our mission and objectives">Our mission and objectives</a></span>/', $page->contents)) {
	  echo "WQEQIWJEWQIEJQWIEJQW";
	  var_dump($matches);
	}


	if ($redirPos = strstr($page->contents, '<span class="redirectText">')) {

            echo "redir!" . $redirPos;
	    $redirTitle = substr($page->contents, $redirPos);
	    echo $redirTitle;
	}

        $page->history_url = $page->base_url .'&action=history';
        $page->edit_url = $page->base_url .'&action=edit';
        $page->headline = str_replace("_", " ", $mwiki_title);
        return $page;
    }

}


?>
