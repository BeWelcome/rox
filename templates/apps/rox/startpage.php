<?php
$words = new MOD_words();
?>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
		<div id="content"> 
              <div class="info index" id=\"langbox\"> 
               <div class="floatbox"><img src="images/index_find.gif" alt="Find" />
			   <h3><?php  echo $words->get('IndexPageWord3');?></h3>
			   </div>
				<p><?php  echo $words->get('IndexPageWord4');?></p>

			  
<?php			  
			  
echo "\n<div class=\"floatbox\"><img src=\"images/index_meet.gif\" alt=\"Home\" />
			   <h3>".$words->get('IndexPageWord19')."</h3>
			   </div>\n"; 
echo "<p>".$words->get('ToChangeLanguageClickFlag')."</p>";
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
			   <h3><?php  echo $words->get('IndexPageWord9');?></h3>
			   </div>
				<p><?php  echo $words->get('IndexPageWord10');?></p>
               <div class="floatbox"><img src="images/index_meet.gif" alt="Home" />
			   <h3><?php  echo $words->get('IndexPageWord11');?></h3>
			   </div>
				<p><?php  echo $words->get('IndexPageWord12');?></p>
				
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
				<h3><?php  echo $words->get('IndexPageWord5');?></h3>
				<p><?php  echo $words->get('IndexPageWord6');?></p>
				<h3><?php  echo $words->get('IndexPageWord7');?></h3>
				<p><?php  echo $words->get('IndexPageWord8');?></p>
              </div>
            </div>
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
		<div id="content"> 
              <div class="info index"> 
			   <h3><?php  echo $words->get('IndexPageWord13');?></h3>
				<p><?php  echo $words->get('IndexPageWord14');?></p>
			   <h3><?php  echo $words->get('IndexPageWord15');?></h3>
				<p><?php  echo $words->get('IndexPageWord16');?></p>
              </div>
          </div>
    </div>
  </div>
</div>