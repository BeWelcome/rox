<?php
$userbarText = array();
$words = new MOD_words();

?>
<div class="info">
	<?php echo $words->get("Volunteer_SearchIntro");?>
</div>
<!-- Google CSE Search Box Begins  -->
<form action="forums/search" id="cse-search-box">
  <div>
    <input type="hidden" name="cx" value="003793464580395137050:n7s_x10-itw" />
    <input type="hidden" name="cof" value="FORID:11" />
    <input type="hidden" name="ie" value="UTF-8" />
    <input type="text" name="q" size="31" />
    <input type="submit" name="sa" value="Search" />
  </div>
</form>
<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=cse-search-box&lang=en"></script>

<!-- Google CSE Search Box Ends -->



<!-- Google Search Result Snippet Begins -->
<div id="cse-search-results"></div>
<script type="text/javascript">
  var googleSearchIframeName = "cse-search-results";
  var googleSearchFormName = "cse-search-box";
  var googleSearchFrameWidth = 600;
  var googleSearchFrameborder = 0;  
  var googleSearchDomain = "www.google.com";
  var googleSearchPath = "/cse";
</script>
<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>
<!-- Google Search Result Snippet Ends -->

