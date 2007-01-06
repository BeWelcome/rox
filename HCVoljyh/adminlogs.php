<?php
include "lib/dbaccess.php" ;
require_once "layout/error.php" ;

  $RightLevel=HasRight('Logs'); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the suffcient <b>Logs</b> rights<br>" ;
	  exit(0) ;
  }
	
	$cid=GetParam("Username","") ;
	if ($cid!="") {
	  if (!is_numeric($cid)) {
		  $rr=LoadRow("select id as cid from members where Username='".$cid."'") ;
			if (isset($rr->cid)) $cid=$rr->cid ;
			else $cid==0 ;
	  }
	  $where.=" and IdMember=".$cid ;
	}
	
	$limit=GetParam("limit",50) ;

	$andS1=GetParam("andS1","") ;
	if ($andS1!="") {
	  $where.=" and Str like='%".$andS1."'%" ;
	}

	$andS2=GetParam("andS2","") ;
	if ($andS2!="") {
	  $where.=" and Str like='%".$andS2."'%" ;
	}

	$NotandS1=GetParam("NotandS1","") ;
	if ($NotandS1!="") {
	  $where.=" and Str not like='%".$NotandS1."'%" ;
	}

	$NotandS2=GetParam("NotandS2","") ;
	if ($NotandS2!="") {
	  $where.=" and Str not like='%".$NotandS2."'%" ;
	}

	$ip=GetParam("ip","") ;
	if ($ip!="") {
	  $where.=" and ip=".$ip."" ;
	}

	$type=GetParam("type","") ;
	if ($type!="") {
	  $where.=" and Type='".$type."'" ;
	}


  switch(GetParam("action")) {
		
		case "del" :  // case a new signupper confirm his mail
			break ;
	}
	
	
	$TData=array() ;

	$str="select logs.*,Username from logs,members where members.id=logs.IdMember ".$where."  order by created desc limit 0,".$limit; 
  $qry=sql_query($str) ;
  while ($rr=mysql_fetch_object($qry)) {
    array_push($TData,$rr) ;
  } 

  include "layout/adminlogs.php" ;
  DisplayAdminLogs($TData) ;


?>
