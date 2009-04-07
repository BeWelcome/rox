<?php

/**
 * Explore controller
 *
 * @package explore
 * @author Micha (lupochen)
 * @copyright hmm
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ExploreController extends RoxControllerBase
{
    public function index($args = false)
    {
        $request = $args->request;
                
        if (!isset($request[0])) {
            // then who activated the about controller?
            $page = new ExplorePage();
        } else {
            $page = new ExplorePage();
        }
        return $page;
    }
}


?>