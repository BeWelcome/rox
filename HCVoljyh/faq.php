<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;
  require_once "layout/faq.php" ;

  switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
			
	  case "insert" :
		  if (!HasRight("Faq")>0) { // only people with suficient right can edit FAQ
	      $errcode="ErrorNeedRight" ; // initialise global variable
	      DisplayError(ww($errcode,"Faq")) ;
			}
			$str="insert into faq(created,IdCategory) values(now(),".GetParam("IdCategory").")" ;
			sql_query($str) ;
			$LastInsert=mysql_insert_id() ;

      // Load the available faq categories  
			$TCategory=array() ;
			$qry=sql_query("select * from faqcategories order by SortOrder asc") ;
			while ($rr=mysql_fetch_object($qry))  {
	      array_push($TCategory,$rr) ;
			}
			
			// Load the data for teh current Faq to edit
			$rr=LoadRow("select faq.*,faqcategories.Description as CategoryName from faq,faqcategories where faq.IDCategory=faqcategories.id and faq.id=".$LastInsert) ;

      DisplayEditFaq($rr,$TCategory) ; // call the display
			exit(0) ;
			break ;
			
	  case "edit" :
		  if (!HasRight("Faq")>0) { // only people with suficient right can edit FAQ
	      $errcode="ErrorNeedRight" ; // initialise global variable
	      DisplayError(ww($errcode,"Faq")) ;
			}

      // Load the available faq categories  
			$TCategory=array() ;
			$qry=sql_query("select * from faqcategories order by SortOrder asc") ;
			while ($rr=mysql_fetch_object($qry))  {
	      array_push($TCategory,$rr) ;
			}
			
			// Load the data for teh current Faq to edit
			$rr=LoadRow("select faq.*,faqcategories.Description as CategoryName from faq,faqcategories where faq.IDCategory=faqcategories.id and faq.id=".GetParam("IdFaq")) ;

      DisplayEditFaq($rr,$TCategory) ; // call the display
			exit(0) ;
			break ;

	  case "update" :
		  if (!HasRight("Faq")>0) { // only people with suficient right can edit FAQ
	      $errcode="ErrorNeedRight" ; // initialise global variable
	      DisplayError(ww($errcode,"Faq")) ;
			}
			
			$Faq=LoadRow("select * from faq where id=".GetParam("IdFaq")) ;
			$rwq=LoadRow("select * from words where code='"."FaqQ_".GetParam("QandA")."' and IdLanguage=0") ;
			$rwa=LoadRow("select * from words where code='"."FaqA_".GetParam("QandA")."' and IdLanguage=0") ;
			
			if (!isset($rwq->id)) {
			  $str="insert into words(code,Description,IdLanguage,ShortCode) values('"."FaqQ_".GetParam("QandA")."','This is a question for a Faq',0,'eng')" ;
				sql_query($str) ;
			}
			if (!isset($rwa->id)) {
			  $str="insert into words(code,Description,IdLanguage,ShortCode) values('"."FaqA_".GetParam("QandA")."','This is an an answer for a Faq',0,'eng')" ;
				sql_query($str) ;
			}

			// reload for case it was just inserted before
			$rwq=LoadRow("select * from words where code='"."FaqQ_".GetParam("QandA")."' and IdLanguage=0") ;
			$rwa=LoadRow("select * from words where code='"."FaqA_".GetParam("QandA")."' and IdLanguage=0") ;

			
			$str="update words set Description='".addslashes($rwq->Description)."',Sentence='".addslashes(GetParam("Question"))."' where id=".$rwq->id ;
			sql_query($str) ;
			$str="update words set Description='".addslashes($rwa->Description)."',Sentence='".addslashes(GetParam("Answer"))."' where id=".$rwa->id ;
			sql_query($str) ;
			
			$str="update faq set IdCategory=".GetParam("IdCategory").",QandA='".GetParam("QandA")."',Active='".GetParam("Active")."' where id=".$Faq->id ; 
			sql_query($str) ;
			
			LogStr("updating Faq #".$Faq->id,"Update Faq") ;
			
			break ;

	}
	

// prepare the countries list
	
  if (GetParam("IdCategory")) {
	  $FilterCategory=" and IdCategory=".GetParam("IdCategory") ;
	}
	else {
	  $FilterCategory="" ;
	}
	$FilterActive=" and Active='Active'" ;
	if (HasRight("Faq")) { // Dont fiter if has right to modify Faq
	  $FilterActive="" ;
	}
	
  $str="select faq.*,faqcategories.Description as CategoryName from faq,faqcategories  where faqcategories.id=faq.IdCategory ".$FilterCategory.$FilterActive." order by SortOrder" ;
  $qry=sql_query($str) ;
	$TData=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TData,$rWhile) ;
	}
  DisplayFaq($TData,$rCat) ; // call the layout with the selected parameters

?>
