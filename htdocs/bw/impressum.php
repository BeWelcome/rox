<?php
require_once "lib/init.php";
require_once "layout/menus.php";

	global $title;
	$title = ww('Impressum');
	require_once "layout/header.php";

	Menu1("", ""); // Displays the top menu

	Menu2("inviteafriend.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent(ww("Impressum")); // Display the header


echo "	<div id=\"col3\">\n";
echo "		<div id=\"col3_content\" class=\"clearfix\" >\n";
echo "			<div class=\"info\">\n";
echo "				<p>Anbieterkennzeichung nach &sect;6 Teledienstgesetz (information provider identification according to &sect;6 Teledienstgesetz) :<br />\n";
echo "				<br />\n";
echo "				BeVolunteer<br />\n";
echo "				c/o Jean-Yves Hegron<br />\n";
echo "				19 rue de Paris<br />\n";
echo "				35500 Vitre<br />\n";
echo "				France<br />\n";
echo "				<br />\n";
echo "				" . ww("SignupEmail") . ": info@bevolunteer.org<br /> </p>\n";
echo "			</div> <!-- #info: - end -->\n";
echo "		</div> <!-- #col3_content: - end -->\n";
echo "		<div id=\"ie_clearing\">&nbsp;</div>\n";
echo "	</div> <!-- #col3: - End -->\n";

	require_once "layout/footer.php";



?>
