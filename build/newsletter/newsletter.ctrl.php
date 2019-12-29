<?php

/**
 * Simple page to display a ginev news letter
 *
 * @package newsletter
 * @author jeanyves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class NewsletterController extends RoxControllerBase
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args = false)
    {
        $request = PRequest::get()->request;
        $model = new NewsletterModel();

        // look at the request.
        if (!isset($request[1])) {
			$Data=$model->PreviousLetters() ;
            $page = new NewsletterPage($Data);
        } else  {
			$Data=$model->Load(($request[1])) ;
			if (isset($request[2])) {
				$Data->Lang=$request[2] ;
			}
			else {
				$Data->Lang='en' ;
			}
			if (empty($Data)) {
				$page = new EmptyLetterPage($request[1]);
			}
			else {
				$page = new OneNewsLetterPage($Data);
			}
        }

        // return the $page object, so the "$page->render()" function can be called somewhere else.
        return $page;
    }
}
?>
