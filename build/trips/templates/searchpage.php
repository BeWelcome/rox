<?
$words = new MOD_words($this->getSession());
$search = $_GET['s'];

if (!$search) {}
else {
?>
<h2><?=$words->get('TripSearchPage',$search)?></h2>
<div id="searchable">
<?php
if ($trips == false ) echo '<p style="margin: 2em 0"><span class="note"><img src="images/icons/exclamation.png"> '.$words->get('Tripsearch_NoResults').'</span></p>';
else {
?>
<h3><?=$words->get('TripsearchPosts',$search)?></h3>
<?php
foreach($trips as $trip) {
    require 'tripitem.php';
}
?>

<?php if (isset($tags)) {
    echo '<h3>'.$words->get('TripsearchTagsPosts',$search).'</h3>';
    foreach($tagsposts as $blog) {
        require 'tripitem.php';
    }
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
    window.onload = highlightOnLoad('<?=htmlspecialchars($search, ENT_QUOTES)?>');

    // -->
  </script>
<?php
}
}
?>
