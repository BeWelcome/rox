<?php
require_once ("menus.php");

function DisplayAboutUs() {
	global $title;
	$title = ww('AboutUsPage');
	require_once "header.php";
	Menu1("aboutus.php", ww('AboutUsPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";

	menugetanswers("aboutus.php" . $menutab, $title);

//	ShowLeftColumn($MenuAction)  ; // Show the Actions
	ShowAds(); // Show the Ads

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

?>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
<?php
	echo "<div class=\"info\">\n";
	echo "<h3>", ww("AboutUs_TheIdea"),"</h3>";
	echo "<p>",ww("AboutUs_TheIdeaText"),"</p>";
	echo "<h3>", ww("AboutUs_GetActive"),"</h3>";
	echo "<p>",ww("AboutUs_GetActiveText"),"</p>";
	echo "<p>",ww("AboutUs_Greetings"),"</p>";
	echo "</div>\n";
?>
    </div>
   </div>


  <div class="c50r">
    <div class="subcr">
<?php	
	echo "<div class=\"info\">\n";
	echo "<h3>", ww("AboutUs_HowOrganized"),"</h3>";
	echo "<p>",ww("AboutUs_HowOrganizedText"),"</p>";
	echo "<h3>", ww("AboutUs_GiveFeedback"),"</h3>";
	echo "<p>",ww("AboutUs_GiveFeedbackText"),"</p>";
	echo "</div>\n";
?>			  
    </div>
  </div>
</div>	
<?php

	require_once "footer.php";
}
?>