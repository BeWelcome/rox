<?php
require_once ("Menus.php");

function DisplayIndex() {
	global $title;
	$title = ww('IndexPage') ;

	include "header.php";

	Menu1("", $title); // Displays the top menu
	Menu2("", ww('MainPage')); // Displays the second menu
?>



<div id="maincontent"> 
  <div id="topcontent"> 
    <div id="main_index">
		 <h2><?php echo ww("IndexPageWord1");?></h2>
          <h1><?php  echo ww("IndexPageWord2");?></h1>
	</div>
  </div>
</div>

  <div id="columns-top" class="notabs index"> 

  	</div>
<!-- MENU 2 -->
<div id="columns"> 
  <div id="columns-low"> 
  
    <!-- MAIN begin 3-column-part -->
    <div id="main"> 
      <!-- MAIN right column -->
      <div id="col2" class="index"> 
          <div id="col2_content" class="clearfix">
		<div id="content"> 
              <div class="info index"> 
<form method=POST action=login.php>
<h3>Login</h3>
<input type=hidden name=action value=login>
<input type=hidden name=nextlink value="main.php?action">
<p><?php  echo ww("Username");?><br /><input name=Username type=text value=''><br /></p>
<p><?php  echo ww("password");?><br /><input type=password name=password><br /></p>
<input type=submit value='submit'>
<p><?php  echo ww("IndexPageWord18");?></a>
</p>
</form>

<h3><?php  echo ww("SignupNow");?></h3>
<p><?php  echo ww("IndexPageWord17");?></p>
				</div>
			</div>
		  </div>
      </div>
      <!-- MAIN middle column -->
      <div id="col3" class="index"> 
	      <div id="col3_content" class="clearfix">
		  
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
		<div id="content"> 
              <div class="info index" id=\"langbox\"> 
               <div class="floatbox"><img src="images/index_find.gif" alt="Find" />
			   <h3><?php  echo ww("IndexPageWord3");?></h3>
			   </div>
				<p><?php  echo ww("IndexPageWord4");?></p>

			  
<?php			  
			  function DisplayFlag($ShortLang,$png,$title) {
$langurl = $_SERVER['PHP_SELF'] . "?";
if ($_SERVER['QUERY_STRING'] != "") {
	$QS = explode('&', $_SERVER['QUERY_STRING']);
	for ($ii = 0; $ii < count($QS); $ii++) {
		if (strpos($QS[$ii], "lang=") === false)
			$langurl = $langurl . $QS[$ii] . "&";
	}
}

if ($_SESSION['lang'] == $ShortLang)
	echo "  <span><a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"images/",$png,"\" title=\"",$title,"\"></a></span>\n";
else
	echo "  <a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"images/",$png,"\" title=\"",$title,"\"></a>\n";
} // end of DisplayFlag

echo "\n<div class=\"floatbox\"><img src=\"images/index_meet.gif\" alt=\"Home\" />
			   <h3>".ww("IndexPageWord19")."</h3>
			   </div>\n"; 
echo "<p>".ww("ToChangeLanguageClickFlag")."</p>";

// Just add add the bottom the language switch trick
DisplayFlag("en","en.png","English") ;
DisplayFlag("fr","fr.png","French") ;
DisplayFlag("esp","esp.png","Español") ;
DisplayFlag("de","de.png","Deutsh") ;
DisplayFlag("it","it.png","Italian") ;
DisplayFlag("ru","ru.png","Russian") ;
DisplayFlag("espe","esper.png","Esperanto") ;
DisplayFlag("pl","pl.png","Polish") ;
DisplayFlag("tr","tr.png","Turkish") ;
DisplayFlag("lt","lt.png","Lithuanian") ;
DisplayFlag("nl","nl.png","Dutch") ;
DisplayFlag("dk","dk.png","Danish") ;
DisplayFlag("cat","cat.png","Catalan") ;
DisplayFlag("fi","fi.png","Finnish") ;
DisplayFlag("pt","pt.png","Portuguese") ;

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;" ;
if ($_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;" ;
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "  <a href=\"adminwords.php?showtransarray=1&pagetotranslate=" . $pagetotranslate . "\" target=new><img border=0 height=10 src=\"images/switchtrans.gif\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" width=16></a>\n";
}
?>			  
			    </div>
			  
            </div>
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
		<div id="content"> 
              <div class="info index"> 
               <div class="floatbox"><img src="images/index_home.gif" alt="Home" />
			   <h3><?php  echo ww("IndexPageWord9");?></h3>
			   </div>
				<p><?php  echo ww("IndexPageWord10");?></p>
               <div class="floatbox"><img src="images/index_meet.gif" alt="Home" />
			   <h3><?php  echo ww("IndexPageWord11");?></h3>
			   </div>
				<p><?php  echo ww("IndexPageWord12");?></p>
				
              </div>
          </div>
    </div>
  </div>
</div>

<!-- Next row -->

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
		<div id="content"> 
              <div class="info index"> 
				<h3><?php  echo ww("IndexPageWord5");?></h3>
				<p><?php  echo ww("IndexPageWord6");?></p>
				<h3><?php  echo ww("IndexPageWord7");?></h3>
				<p><?php  echo ww("IndexPageWord8");?></p>
              </div>
            </div>
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
		<div id="content"> 
              <div class="info index"> 
			   <h3><?php  echo ww("IndexPageWord13");?></h3>
				<p><?php  echo ww("IndexPageWord14");?></p>
			   <h3><?php  echo ww("IndexPageWord15");?></h3>
				<p><?php  echo ww("IndexPageWord16");?></p>
              </div>
          </div>
    </div>
  </div>
</div>
            
	      </div> 
        <!-- IE Column Clearing -->
        <div id="ie_clearing">&nbsp;</div>
        <!-- End: IE Column Clearing -->
      </div>
      <!-- End MAIN 3-columns-part -->
    </div>

    <!-- Footer -->
    <div id="footer"> ... </div>
  </div> <!-- columns-low -->
</div> <!-- columns -->
</div> <!-- main-content -->
</body>
</html>
<?php
	include "footer.php";
exit(0) ;

//	include "footer.php";

} // end of DisplayIndex


function DisplayIndexLogged($Username) {
	global $title;
	$title = ww('IndexPage') ;

	include "header.php";

	Menu1("", $title); // Displays the top menu
	Menu2("", ww('MainPage')); // Displays the second menu

	echo "<br><br><br><br><br><br><br><br><center><table width=\"60%\">" ;
	echo "\n<tr><td colspan=2 align=center>" ;
	echo "Hello <b>",$Username,"</b>\n";
	echo "</td>" ;
	echo "</table>\n" ;
	echo "</center>\n";

	echo "</center>\n";
	include "footer.php";
}

function DisplayNotLogged() {
	global $title;
	$title = ww('IndexPage') ;

	include "header.php";

	Menu1("", $title); // Displays the top menu
	Menu2("", $title); // Displays the second menu

    DisplayHeaderIndexPage($title);

	echo "<center><table width=\"60%\">" ;
	echo "\n<tr><td colspan=2>" ;
	echo ww("AboutUsText");
	echo "</td>" ;
	echo "\n<tr align=center><td>" ;
	echo "<a href=\"login.php\">",ww("Login"),"</a>" ;
	echo "</td>" ;
	echo "<td>" ;
	echo " <a href=\"signup.php\">",ww("Signup"),"</a>" ;
	echo "</td>\n" ;
	echo "</table>\n" ;
	echo "</center>\n";
	include "footer.php";
}
?>
