<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MediawikiPage extends RoxPageView
{
    protected function getPageTitle()
    {
        return $this->headline;
    }
    
    
    protected function teaserHeadline()
    {
        echo $this->headline;
    }
    
    
    /**
     * content of the middle column - this is the most important part
     */
    protected function columnsArea()
    {
        // get the translation module
        $words = $this->getWords();
        
        //TODO: caching, fix redirect, css
        
        echo '
<style>
.editsection { display:none; }

a.wikibuttons {
-moz-border-radius:1.0em;
background:white none repeat scroll 0%;
border:1px solid #bbb;
display:block;
margin-left:auto;
margin-right:auto;
margin-top:1em;
padding:0.3em;
text-align:center;
width:100px;
float:right;
}

</style>
<div style="margin: 20px">'.$this->replace_links($this->contents).'</div>
<div style="text-align:right">
<a class="wikibuttons" href="'. $this->edit_url .'">edit</a> 
<a class="wikibuttons" href="'. $this->history_url .'">article history</a>
</div>
';

    }

    /* TODO: this should set the right URLs */
    public function replace_links($content) {

        //$content = str_replace($this->replace_url, "", $content);
	return $content;
    }

}




?>