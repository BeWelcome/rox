<?
/**
 * user blog page template controller
 *
 * defined vars:
 * $blogIt     - iterator over the blogs to display.
 * $userId     - user ID
 * $userHandle - handle of the user.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$words = new MOD_words($this->getSession());

if (!$search) {}
else {
?>
<h2><?=$words->get('BlogSearchPage',$search)?></h2>
<div id="searchable">
<?php
if ($posts == false ) echo '<p style="margin: 2em 0"><span class="note"><img src="images/icons/exclamation.png"> '.$words->get('BlogSearch_NoResults').'</span></p>';
else {
?>
<h3><?=$words->get('BlogSearchPosts',$search)?></h3>
<?php
foreach($posts as $blog) {
    require 'blogitem.php';
}
?>

<h3><?=$words->get('BlogSearchTagsPosts',$search)?></h3>
<?php
foreach($tagsposts as $blog) {
    require 'blogitem.php';
}
?>
</div>
  <script src="scripts/searchhi.js" type="text/javascript" language="JavaScript"></script>
  <script language="JavaScript"><!--
    function highlightOnLoad(searchTerms) {
        // Starting node, parent to all nodes you want to search
        var textContainerNode = document.getElementById("searchable");
        // Split search terms on '|' and iterate over resulting array
        //var searchTerms = searchString.split('|');
        //for (var i in searchTerms) 	{
          // The regex is the secret, it prevents text within tag declarations to be affected
          var regex = new RegExp(">([^<]*)?("+searchTerms+")([^>]*)?<","ig");
          highlightTextNodes(textContainerNode, regex, searchTerms);
          // Add to info-string
        //}
    }

    function highlightTextNodes(element, regex, termid) {
      var tempinnerHTML = element.innerHTML;
      // Do regex replace
      // Inject span with class of 'highlighted termX' for google style highlighting
      element.innerHTML = tempinnerHTML.replace(regex,'>$1<span class="highlighted term'+termid+'">$2</span>$3<');
    }

    // Call this onload, I recommend using the function defined at: http://untruths.org/technology/javascript-windowonload/
    window.onload = highlightOnLoad('<?=$search?>');

    // -->
  </script>
<?php
}
}
?>
