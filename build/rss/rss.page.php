<?php
/** RSS view
 * 
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class PageWithGivenRSS
{
    public function render()
    {
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>';
        echo $this->content_string;
        echo '</channel>
</rss>';
        PVars::getObj('page')->output_done = true;
    }
}


?>