<?php
/**
 * blog controller
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: blog.ctrl.php 56 2006-06-21 13:53:57Z roland $
 */
class ImagesController extends PAppController {
    //private $_model;
    //private $_view;
    
    public function __construct() {
        parent::__construct();
        //$this->_model = new Images();
        //$this->_view =  new ImagesView($this->_model);
    }
    
    public function index()
	{
		$img_filepath = $this->_getImageFilepath();
		header('Content-type: '.image_type_to_mime_type('jpg'));
		@copy($img_filepath, 'php://output');
		//echo $img_filepath;
		exit(0);
	}
	
	
	/**
	 * Determine which image in the filesystem should be displayed.
	 *
	 * @return string the absolute filesystem path of the image, including filename and extension
	 */
	private function _getImageFilepath()
	{
	    $request = PRequest::get()->request;
	    
	    $rel_path = '';
	    
	    switch($request[1]) {
	        case 'avatar':
	        case 'avatars':  // both is allowed ?
	            $rel_path = MOD_layoutbits::userPic_username($request[2]);
	            //echo $rel_path.'<br>';
	            return SCRIPT_BASE.'htdocs/bw'.$rel_path;
	            break;
	        default:
	    }
	    return SCRIPT_BASE.'htdocs/bw/memberphotos/et.jpg';
        //return 'C:/wamp/www/dossier.gif';
	}
}
?>
