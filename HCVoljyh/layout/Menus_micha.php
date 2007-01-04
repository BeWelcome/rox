<?php

// This menu is the top menu
function Menu1($link="",$tt="") {
echo "<div id=\"header\">";
echo "  <div id=\"logo\">";
echo "    <div id=\"logo-placeholder\"><img alt=\"logo\" height=30 src=\"images/logo.png\" /></div>";
echo "  </div>";
echo "  <div id=\"navigation-functions\">";
echo "    <ul>";
echo "      <li",factive($link,"faq.php"),"><a href=\"faq.php\">",ww('faq'),"</a></li>";
echo "      <li ",factive($link,"feedback.php"),"><a href=\"feedback.php\">",ww('ContactUs'),"</a></li>";
if (IsLogged()) {
  echo "      <li><a href=\"mypreferences.php\">",ww('MyPreferences'),"</a></li>";
  echo "      <li><a href=\"main.php?action=logout\" id=\"header-logout-link\">",ww("Logout"),"</a></li>";
}
else {
  echo "      <li",factive($link,"login.php"),"><a href=\"login.php\" >",ww("Login"),"</a></li>";
  echo "      <li",factive($link,"signup.php"),"><a href=\"signup.php\">",ww('Signup'),"</a></li>";
}
echo "    </ul>";
echo "  </div>";

	
echo "    <br class=\"clear\"/>" ;
echo "    <div id=\"navigation-access\">";
echo "    <ul>";
echo "    <li><a href=\"membersbycountries.php\">",ww('MembersByCountries'),"</a></li>";

echo "    <li><a href=\"todo.php\">Map</a></li>";
echo "    <li>";
echo "    <form action=\"quicksearch.php\" id=\"form-quicksearch\">";
echo "    <fieldset id=\"fieldset-quicksearch\">";
echo "    <a href=\"search.php\">",ww('SearchPage'),"</a>";
echo "    <input type=\"text\" name=\"searchtext\" size=\"10\" maxlength=\"30\" id=\"text-field\" />";
echo "    <input type=\"hidden\" name=\"action\" value=\"quicksearch\" />";
echo "    <input type=\"image\" src=\"images/icon_go.png\" id=\"submit-button\" />";
echo "    </fieldset>";

echo "    </form>";
echo "    </li>";
echo "    </ul>";
echo "    </div>\n ";

} // end of Menu1


function Menu2($link="",$tt="") {
echo "<div id=\"navigation-main\">";
echo "    <ul>";
echo "      <li ",factive($link,"main.php"),"><a href=\"main.php\">Menu</a></li>";
echo "      <li ",factive($link,"member.php"),"><a href=\"member.php\">Members</a></li>";
echo "      <li ",factive($link,"groups.php"),"><a href=\"groups.php\">",ww('Groups'),"</a></li>";
echo "      <li ",factive($link,"forum.php"),"><a href=\"todo.php\">Forum</a></li>";
echo "      <li ",factive($link,"blogs.php"),"><a href=\"todo.php\">Blogs</a></li>";
echo "      <li ",factive($link,"gallery.php"),"><a href=\"todo.php\">Gallery</a></li>";
echo "    </ul>";
echo "  </div>";
  
echo "  <div class=\"clear\" />" ;
echo "</div>" ;
} // end of Menu2

function menumember($link="",$IdMember=0,$NbComment) {
echo "	<div id=\"columns-top\">" ;
echo "				<ul id=\"navigation-content\">" ;
echo "				<li ",factive($link,"member.php?cid=".$IdMember),"><a href=\"member.php?cid=".$IdMember,"\">",ww('MemberPage'),"</a></li>" ;
echo "				<li",factive($link,"contactmember.php?cid=".$IdMember),"><a href=\"","contactmember.php?cid=".$IdMember,"\">",ww('ContactMember'),"</a></li>" ;
echo "				<li",factive($link,"viewcomments.php?cid=".$IdMember),"><a href=\"viewcomments.php?cid=".$IdMember,"\">",ww('ViewComments'),"(",$NbComment,")</a></li>" ;
echo "				<li",factive($link,"blog.php"),"><a href=\"todo.php\">Blog</a></li>" ;
echo "				<li",factive($link,"map.php"),"><a href=\"todo.php\">Map</a></li>" ;
echo "			</ul>" ;
echo "	</div>" ;
} // end of menumember


function factive($link,$value) {
  if (strstr($link,$value)!==False) {
	  return("class=\"active\"") ;
	}
	else return("") ;
} // end of factive
?>