<?
/////////////////////////////////////////////
/*
Small wiki admin for ErfurtWiki using mysql
can delete pages, and rename pages, with the option of replacing the renamed page with a reference to the new one
made for The Arrow Project site.
Several parts of this are Arrow Specific. 
I've commented them with ARROW in the line so people can adapt it to use outside arrow.

In the spirit of ErfurtWiki this page is Public Domain.

Menno Lodder
menno_lodder at hotmail dot com
www.arrowproject.net

*/
//////////////////////////////////////////////


//ARROW some protection to ensure libraries aren't called directly
define('IN_ARROW', true);

//ARROW includes libraries and shows the admin section headers, also opens html tags upto <body> 
require_once('header.php');

//ARROW if statement checks if the persion has permission
if(!has_perm("WIKI_ADMIN"))
{
	echo("No permission");
	include('footer.php');
	exit();
}
//**************************************
//ARROW to use this outside of arrow, comment out the above and the footer.php include at the bottom
//      then uncomment the following 2 methods, and set the settings variables.
//**************************************

$wikitable = "wiki"; //the table name of the table containing the wiki info (ewiki by default)
$wikiurl = "../wiki.php"; //the relative URL of the wiki pages to the admin page, this is used to allow linking to the wiki pages

/*
These functions are untested

//logs a message
function logMsg($type, $message)
{
	//log whatever you want here, or do nothing
}

//connects to the db
function connect_db()
{
	//set these variables
	$db = "dbname";
	$dbhost = "localhost";
	$dbuser = "username";
	$dbpass = "password";
	
	$conn = mysql_connect($dbhost,$dbuser,$dbpass) or die("Error - Connection to database is not established !");
	@mysql_select_db($db, $conn) or die("Error - Can't open the database !");	
}
*/


$showdetails = false;
$showindex = false;

//set the page in advance, so it can be changed by the functions below
$page = false;
if(isset($_REQUEST['page']))
{
	$page = $_REQUEST['page'];
}

if(isset($_REQUEST['function']))
{
	if($_REQUEST['function'] == "details")
	{
		$showdetails = true;
 	}
 	else if(($_REQUEST['function'] == "rename") && isset($_REQUEST['oldname']) && isset($_REQUEST['newname']))
 	{
 		$old = $_REQUEST['oldname'];
 		$new = $_REQUEST['newname'];

 		$shadow = isset($_REQUEST['leaveshadow']);
		
		$shadowtext = "not making shadow topic";
		if($shadow)
		{
			$shadowtext = "making shadow topic";
  		}
		//ARROW makes a database connection to the mysql database (which is from now on the default one)
 		connect_db();
 		//check if the name already exists
 		$checkresult = mysql_query("SELECT * FROM $wikitable WHERE pagename = '".addslashes($new)."'") or die("Error check if name already exists: ".mysql_error());
 		if(!mysql_fetch_assoc($checkresult))
 		{
 			
 			//get the last version of the old page
 			$oldresult = mysql_query("SELECT * FROM $wikitable WHERE pagename = '".addslashes($old)."' ORDER BY version DESC LIMIT 1") or die("Error check if name already exists: ".mysql_error());
 			
 			if($oldpage = mysql_fetch_assoc($oldresult))
 			{
 				//old page exists
		 		mysql_query("UPDATE $wikitable SET pagename= '".addslashes($new)."' WHERE pagename= '".addslashes($old)."'") or die("Error renaming the page: ".mysql_error());
		 		echo("<b>Renamed $old to $new $shadowtext</b>,<br />");
		 		
				//ARROW logs a message under the header "wikiadmin"
				logMsg("wikiadmin",	"Renamed $old to $new $shadowtext");
					
				if($shadow)
				{
					$shadowcontent = "This page was renamed to [$new]";
					//this just takes the last author and meta info, cause thats rather complicated to change
					mysql_query("INSERT INTO $wikitable (pagename, version, flags, content, author, created, lastmodified, refs, meta, hits)".
								" VALUES ('".addslashes($old)."', '".($oldpage['version']+1)."', '".$oldpage['flags']."', '".
								addslashes($shadowcontent)."', '".$oldpage['author']."', '".$oldpage['created']."', '".time()."', '"."\n\n".addslashes($new)."\n\n\n"."', '".
								$oldpage['meta']."', '".$oldpage['hits']."')") or die("Error making shadow page: ".mysql_error());
		  		}
    		}
    		else
    		{
    			echo("<b>Page named $old not found.</b><br />");
      		}
   		}
   		else
   		{
   			//name already exists
   			echo("<b>A page named $new already exists</b><br />");
     	}
    	
    	//change the page to show
    	$page = $new;
     	$showdetails = true;
  	}
  	elseif(($_REQUEST['function'] == "delete") && isset($_REQUEST['page']))
  	{
  		$page = $_REQUEST['page'];
  		
  		mysql_query("DELETE FROM $wikitable WHERE pagename = '".addslashes($page)."'") or die("Error deleting page: ".mysql_error());
 		
  		echo("<br /><b>Deleted $page</b>");
   	}
 	else
 	{
 		echo("Unknown function or not enough parameters");
  	}
	
}
else
{
	//no function show index
	$showindex = true;
}

if($showdetails)
{
	//show the details of a page
	
	//check if a page is given
	if($page)
	{
		connect_db();
		//select the pages with the given name, first is the most recent one
		$pagequery = "SELECT * FROM $wikitable WHERE pagename = '".addslashes($page)."' ORDER BY version DESC";
		$pageresult = mysql_query($pagequery) or die("Error getting page: ".mysql_error());
		
		if($recentpagerow =  mysql_fetch_assoc($pageresult))
		{
			echo("<br /><a href=\"wikiadmin.php\">Wiki Index</a>");
			echo("<br /><h2><a target=\"blank\" href=\"$wikiurl?id=".urlencode($recentpagerow['pagename'])."\">".$recentpagerow['pagename']."</a></h2>\n");
			echo("<b>Rename</b>\n");
			echo("<form method=\"post\"><input type=\"text\" name=\"newname\" size=\"30\" value=\"".$recentpagerow['pagename']."\" />&nbsp;\n");
			echo("<input type=\"checkbox\" name=\"leaveshadow\" value=\"true\" checked=\"checked\" />Leave shadow page&nbsp;\n");
			echo("<input value=\"Rename\" type=\"submit\" /><input type=\"hidden\" name=\"oldname\" value=\"".$recentpagerow['pagename']."\" /><input type=\"hidden\" name=\"function\" value=\"rename\" /></form>\n");
			echo("<small>Shadow page is a page in the place of the old name, that points to the new name.</small>");
			echo("<br /><br />\n");
			echo("<form method=\"post\" onSubmit=\"return confirm('Are you sure you want permanently delete this page?');\">\n");
			echo("<input value=\"Delete\"  type=\"submit\" /><input type=\"hidden\" name=\"function\" value=\"delete\" />\n");
			echo("<input type=\"hidden\" name=\"page\" value=\"".$recentpagerow['pagename']."\" />\n");
			echo("</form>");
			//put pointer back to start of results
			mysql_data_seek($pageresult, 0);
  		}
  		else
  		{
  			echo("No page found with that title: ". $page);	
    	}
 	}
 	else
 	{
 		//no pagename given
		echo("No page field found");
  	}	
}

if($showindex)
{
	//show index

	//ARROW makes a database connection to the mysql database (which is from now on the default one)
	connect_db();
	$pagesquery = "SELECT pagename, MAX(version) AS version, MAX(lastmodified) AS lastmodified FROM $wikitable GROUP BY pagename";
	$pagesresult = mysql_query($pagesquery) or die("Error getting pages: ".mysql_error());
	
	//echo the table
	echo("<table>\n");
	echo("<tr>\n");
	echo("<th>Name</th><th>view</th><th>Version</th><th>LastUpdate</th>\n");
	echo("</tr>\n");
	while($row = mysql_fetch_assoc($pagesresult))
	{
		$name = $row['pagename'];
		$time = timeString($row['lastmodified']);
		$version = $row['version'];
		
		echo("<tr>\n");
		echo("<td><a href=\"wikiadmin.php?function=details&page=".urlencode($name)."\">".htmlspecialchars($name)."</a></td>\n");
		//ARROW ../wiki.php?id= is the base of the link to the added wiki page.
		echo("<td><a target=\"_blank\" href=\"$wikiurl?id=".urlencode($name)."\">view</a></td>\n");
		echo("<td>".$version."</td>\n");
		echo("<td>".$time."</td>\n");		
		echo("</tr>\n");
 	}
	echo("</table>\n");
}     	


//ARROW closes all html of the header.php
include('footer.php');
?>
