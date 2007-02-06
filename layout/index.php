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
		 <h2>Are you travelling around the globe?</h2>
          <h1>Be welcome!</h1>
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
<p>Username<br /><input name=Username type=text value=''><br /></p>
<p>Password<br /><input type=password name=password><br /></p>
<input type=submit value='submit'>
<p>Forgot your login? Get a new one <a href="#">here!</a>
</p>
</form>

<h3>Sign up</h3>
<p><a href="signup.php">Create a profile</a> without obligations. Joining and using the network is free!</p>
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
              <div class="info index"> 
               <div class="floatbox"><img src="images/index_find.gif" alt="Find" />
			   <h3>Find a place to stay</h3>
			   </div>
				<p>On your travel, wouldn't it be great not to spend a lot of money for accomodation but meet locals and even let them show you their place?<br /><br />With BeWelcome you are able to find members all over the world that welcome you to stay for free and help you out in many different ways.<br /><br />
				How that? Just <a href="signup.php">create a profile</a> or <a href="#">take a tour</a> first and see for yourself...</p>
              </div>
            </div>
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
		<div id="content"> 
              <div class="info index"> 
               <div class="floatbox"><img src="images/index_home.gif" alt="Home" />
			   <h3>Share your place</h3>
			   </div>
				<p>If you have some space left, you can offer to host somebody at your place. No matter if it's a bed, a matress or space on the floor - you can help others on their travels easily.</p>
               <div class="floatbox"><img src="images/index_meet.gif" alt="Home" />
			   <h3>Get in touch with other cultures</h3>
			   </div>
				<p>Wether you stay with others or let others stay with you, it will always be a great experience. It's your chance: Get to know another perspective, step away from common tourist paths and dive into another culture.</p>
				
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
				<h3>How is it financed?</h3>
				<p>In a first time, BeVolunteer plan to get funds from: Ads on the site and members donations.<br />
				Accounts will be published at the occasion of our next ordinary GA.<br /></p>
				<h3>More information</h3>
				<p>All the info related to BeVolunteer can be found on <a href="http://www.bevolunteer.org">www.BeVolunteer.org</a>.<br />
				You can also <a href="feedback.php">Contact us</a>.</p>
              </div>
            </div>
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
		<div id="content"> 
              <div class="info index"> 
			   <h3>Eventually another title</h3>
				<p>Wether you stay with others or let others stay with you, it will always be a great experience. It's your chance: Get to know another perspective, step away from common tourist paths and dive into another culture.</p>
			   <h3>Eventually another title</h3>
				<p>Wether you stay with others or let others stay with you, it will always be a great experience. It's your chance: Get to know another perspective, step away from common tourist paths and dive into another culture.</p>
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
