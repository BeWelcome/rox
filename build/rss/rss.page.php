<?php
/** RSS view
 * 
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class PageWithXML_parameterized
{
  public function render()
  {
  	header('Content-type: text/xml');
	//echo "FOO";
    echo $this->content_string;
    PVars::getObj('page')->output_done = true;
  }
}



?>