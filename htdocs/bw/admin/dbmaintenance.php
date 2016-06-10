<?php
chdir("..") ;
require_once "lib/init.php";
$title = "DB Maintenance";
require_once "layout/menus.php";


function InsertInFTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}

	if ($_IdLanguage == -1)
		$IdLanguage = $_SESSION['IdLanguage'];
	else
		$IdLanguage = $_IdLanguage;

	if ($IdTrad == -1) { // if a new IdTrad is needed
		// Compute a new IdTrad
		$rr = LoadRow("select max(IdTrad) as maxi from forum_trads");
		if (isset ($rr->maxi)) {
			$IdTrad = $rr->maxi + 1;
		} else {
			$IdTrad = 1;
		}
	}

	$IdOwner = $IdMember;
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$Sentence = str_replace("\\","",$ss);
	$str = "insert into forum_trads(TableColumn,IdRecord,IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
	$str .= "Values('".$TableColumn."',".$IdRecord.",". $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . addslashes($Sentence) . "\",now())";
	sql_query($str);
	return ($IdTrad);
} // end of InsertInFTrad

function ReplaceInFTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
	if ($IdOwner == 0) {
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $IdOwner;
	}
	//  echo "in ReplaceInMTrad \$ss=[".$ss."] \$IdTrad=",$IdTrad," \$IdOwner=",$IdMember,"<br />";
	$IdLanguage = $_SESSION['IdLanguage'];
	if ($IdTrad == 0) {
		return (InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember)); // Create a full new translation
	}
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$str = "select * from forum_trads where IdTrad=" . $IdTrad . " and IdLanguage=" . $IdLanguage;
	$rr = LoadRow($str);
	if (!isset ($rr->id)) {
		//	  echo "[$str] not found so inserted <br />";
		return (InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember, $IdLanguage, $IdTrad)); // just insert a new record in memberstrads in this new language
	} else {
		if ($ss != addslashes($rr->Sentence)) { // Update only if sentence has changed
			MakeRevision($rr->id, "forum_trads"); // create revision
			$str = "update forum_trads set TableColumn='".$TableColumn."',IdRecord=".$IdRecord.",IdTranslator=" . $IdTranslator . ",Sentence='" . $ss . "' where id=" . $rr->id;
			sql_query($str);
		}
	}
	return ($IdTrad);
} // end of ReplaceInFTrad


MustLogIn(); // Need to be logged

require_once "layout/header.php";

// It is always to consider that dbmaintenance works in english
$this->getSession->set( 'lang', CV_def_lang )
$this->getSession->set( 'IdLanguage', 0 ) // force English for menu



Menu1("", "DB_MAINTENANCE"); // Displays the top menu

Menu2("main.php", "DB_MAINTENANCE"); // Displays the second menu

if (!HasRight("Admin")) {
	echo("<p> this need Admin rights</p>") ;
	require_once "layout/footer.php";
	die(1) ;
}

$MenuAction  = "            <li><a href=\"".bwlink("admin/dbmaintenance.php")."\">db maintenance</a></li>\n";
$MenuAction  .= "            <li><a href=\"".bwlink("admin/dbmaintenance.php?action=updateid")."\">update new ids</a></li>\n";
$MenuAction  .= "            <li><a href=\"".bwlink("admin/dbmaintenance.php?action=filltrads")."\">fill the forum_trads</a></li>\n";
$MenuAction  .= "            <li><a href=\"".bwlink("admin/dbmaintenance.php?action=filltag_threads")."\">recreate tags_threads</a></li>\n";
$MenuAction  .= "            <li><a href=\"".bwlink("admin/dbmaintenance.php?action=updatetagcounters")."\">update tags counters</a></li>\n";

DisplayHeaderShortUserContent("Db Maintenance",$MenuAction,""); // Display the header
ShowLeftColumn($MenuAction,"");

$action=GetStrParam("action","") ;

switch ($action) {
		case "updateid" :
		  sql_query("update forums_tags set id=tagid") ; // dealing with redundant values
		  sql_query("update forums_threads set id=threadid") ; // dealing with redundant values
		  sql_query("update forums_posts set id=postid") ; // dealing with redundant values
		  echo "<br />Id updated</p>" ;
		  break ;
		case "updatetagcounters" :
			 echo "<p>updating tags counter according to new tags_threads<br />" ;
			 $str=" UPDATE forums_tags SET counter = (select count(*) from tags_threads where forums_tags.id=tags_threads.IdTag)" ;
			 echo "<br />".$str."<br />" ;
			 sql_query($str) ;
			 echo "</p>" ;
			 break ;
		case "filltrads" : 
		  sql_query("truncate forum_trads") ; // dealing with redundant values
		  echo "previous values have been truncated<br />" ;
		  sql_query("update forums_tags set id=tagid") ; // dealing with redundant values
		  sql_query("update forums_threads set id=threadid") ; // dealing with redundant values
		  sql_query("update forums_posts set id=postid") ; // dealing with redundant values
		  $s1="select * from forums_tags" ;
		  $qry1=sql_query($s1) ;
		  echo "<p> processing tags<br />" ;
		  $count=0 ;
		  while ($r1=mysql_fetch_object($qry1)) { 
			  $rr=LoadRow("select * from forum_trads where IdLanguage=0 and Sentence='".mysql_real_escape_string($r1->tag)."' and TableColumn='forums_tags.IdName'") ;
			  if (isset($rr->IdTrad)) {
			  	 if ($r1->id==0) {
				 	die ("first you must create the id") ;
				 }
			  	 $IdTradIdName=ReplaceInFTrad(mysql_real_escape_string($r1->tag),"forums_tags.IdName",$r1->id,$rr->IdTrad,$rr->IdOwner) ;
			  }
			  else {
			  	 $IdTradIdName=ReplaceInFTrad($r1->tag,"forums_tags.IdName",$r1->id) ;
			  }
			  $IdTradIdDescription=0 ;
			  if (!empty($r1->tag_description)) {
			  	 $rr=LoadRow("select * from forum_trads where IdLanguage=0 and Sentence='".mysql_real_escape_string($r1->tag_description)."' and TableColumn='forums_tags.IdDescription'") ;
			  	 if (isset($rr->IdTrad)) {
			  	 	$IdTradIdDescription=ReplaceInFTrad(mysql_real_escape_string($r1->tag_description),"forums_tags.IdDescription",$r1->IdName,$rr->IdTrad,$rr->IdOwner) ;
			  	 }
			  	 else {
			  	 	$IdTradIdDescription=ReplaceInFTrad(mysql_real_escape_string($r1->tag_description),"forums_tags.IdDescription",$r1->IdName) ;
			  	 }
			  }


			  sql_query("update forums_tags set IdName=".$IdTradIdName.",IdDescription=".$IdTradIdDescription." where id=".$r1->id) ;
		  	  $count++ ;
			  echo $count,"creating tag #".$r1->id." [".$r1->tag."] (<i>",$r1->tag_description,"</i>)<br />" ;
		  } 
		  echo "</p><hr />" ;

		  $s1="select * from forums_threads" ;
		  $qry1=sql_query($s1) ;
		  echo "<p> processing forums_threads<br />" ;
		  $count=0 ;
		  while ($r1=mysql_fetch_object($qry1)) { 
			  if (!empty($r1->title)) {
			  	 $rr=LoadRow("select * from forum_trads where IdLanguage=0 and Sentence='".mysql_real_escape_string($r1->title)."' and TableColumn='forums_threads.IdTitle'") ;
			  	 if (isset($rr->IdTrad)) {
			  	 	$IdTradIdTitle=ReplaceInFTrad(mysql_real_escape_string($r1->title),"forums_threads.IdTitle",$r1->id,$rr->IdTrad,$rr->IdOwner) ;
			  	 }
			  	 else {
			  	 	$IdTradIdTitle=ReplaceInFTrad(mysql_real_escape_string($r1->title),"forums_threads.IdTitle",$r1->id) ;
			  	 }
			  }
		 	  sql_query("update forums_threads set IdTitle=".$IdTradIdTitle." where id=".$r1->id) ;
			  echo "done for #".$r1->id." <b>".$r1->title."</b><br />" ;
		  }

		  echo "</p><hr />" ;
		  $s1="select * from forums_posts" ;
		  $qry1=sql_query($s1) ;
		  echo "<p> processing forums_posts<br />" ;
		  $count=0 ;
		  while ($r1=mysql_fetch_object($qry1)) { 
			  if (!empty($r1->message)) {
			  	 $rr=LoadRow("select * from forum_trads where IdLanguage=0 and Sentence='".mysql_real_escape_string($r1->message)."' and TableColumn='forums_postst.IdContent'") ;
			  	 if (isset($rr->message)) {
			  	 	$IdTradIdContent=ReplaceInFTrad(mysql_real_escape_string($r1->message),"forums_posts.IdContent",$r1->id,$rr->IdTrad,$rr->IdOwner) ;
			  	 }
			  	 else {
			  	 	$IdTradIdContent=ReplaceInFTrad(mysql_real_escape_string($r1->message),"forums_posts.IdContent",$r1->id) ;
			  	 }
			  }
		  	  sql_query("update forums_posts set IdContent=".$IdTradIdContent." where id=".$r1->id) ;
			  echo "done for #".$r1->id." post <b>".htmlentities($r1->message)."</b><br />" ;
		  }

		  echo "</p>" ;
		 break ;
		  
		case "filltag_threads" :
		  $s1="select * from forums_threads" ;
		  $qry1=sql_query($s1) ;
		  $count=0 ;
		  echo "<p>" ;
		  while ($r1=mysql_fetch_object($qry1)) {
		  		if (!empty($r1->tag1)) {
		  		   sql_query("replace into tags_threads(IdTag,IdThread) values(".$r1->tag1.",".$r1->threadid.")") ;
				   echo "Inserting one tag1  for thread #".$r1->threadid."<br />" ;
				}  
		  		if (!empty($r1->tag2)) {
		  		   sql_query("replace into tags_threads(IdTag,IdThread) values(".$r1->tag2.",".$r1->threadid.")") ;
				   echo "Inserting one tag2  for thread #".$r1->threadid."<br />" ;
				}  
		  		if (!empty($r1->tag3)) {
		  		   sql_query("replace into tags_threads(IdTag,IdThread) values(".$r1->tag3.",".$r1->threadid.")") ;
				   echo "Inserting one tag3  for thread #".$r1->threadid."<br />" ;
				}  
		  		if (!empty($r1->tag4)) {
		  		   sql_query("replace into tags_threads(IdTag,IdThread) values(".$r1->tag4.",".$r1->threadid.")") ;
				   echo "Inserting one tag4  for thread #".$r1->threadid."<br />" ;
				}  
		  		if (!empty($r1->tag5)) {
		  		   sql_query("replace into tags_threads(IdTag,IdThread) values(".$r1->tag5.",".$r1->threadid.")") ;
				   echo "Inserting one tag5  for thread #".$r1->threadid."<br />" ;
				}  
		  		if (!empty($r1->tag6)) {
		  		   sql_query("replace into tags_threads(IdTag,IdThread) values(".$r1->tag6.",".$r1->threadid.")") ;
				   echo "Inserting one tag6  for thread #".$r1->threadid."<br />" ;
				}  
			} 
		  	echo "</p>" ;
			break ;
			
			default :
					echo "<p>this is a maintenance module be careful</p>" ;
					break ;

} // end of switch

chdir("..") ;
require_once "layout/footer.php";

?>


