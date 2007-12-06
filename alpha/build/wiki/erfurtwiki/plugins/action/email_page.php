<?

 #  This plug-in will eMail the page to the specified eMail address
 #  Plug-in written by Alfred Sterphone, III

#-- text
$ewiki_t["en"]["EMAILPAGE"] = "This will e-mail the current page to the specified address.<br />When you are ready, click the \"_{EMAIL_THIS_PAGE}\" button.<hr>";
$ewiki_t["en"]["EMAIL_THIS_PAGE"] = "Email this page";

#-- glue
if(function_exists("mail"))
{
    $ewiki_plugins["action"]['emailpage'] = "ewiki_page_wiki_email_page";
    $ewiki_config["action_links"]["view"]["emailpage"] =  "EMAIL_THIS_PAGE";
}

$ewiki_t["c"]["EWIKIEMAILCSS"] = '
  <style  TYPE="text/css">
  <!--
  body {
    background-color:#eeeeff;
    padding:2px;
  }	
  
  H2 {
    background:#000000;
    color:#ffffff;
    border:1px solid #000000;
  }
  -->
  </style>
  ';

function isRequestNotSet($email_page,$email_address)
{
    return ( empty($email_page) || empty($email_address) );
}

function checkEmailField($email_page_to,$not_first_time)
{
    if ( empty($email_page_to) && $not_first_time == "1" )
        return "<p><b>Please enter a valid e-mail address</b>";
    else
        return "";
}

function isUserInfoDefined($from_email,$from_name)
{
    return ( !empty($from_email) && !empty($from_name) );
}

function getUserInfo(&$email,&$name)
{
    global $ewiki_uservars;
    
    $email = $ewiki_uservars["E-Mail Address"];
    $name = $ewiki_uservars["Name"];
}
    
function htmlFormGenerate($defined,$id,$warning,$from_email,$from_name)
{
    $url = ewiki_script("emailpage", "$id");
    $info_url = ewiki_script("view","UserInfo");
    
    if ($defined)
    {
        return(ewiki_make_title($id, $id, 2) . ewiki_t(
//--------------------------------------------
<<<END
_{EMAILPAGE}
    $warning
    <p>
    <form action="$url" method="POST" enctype="multipart/form-data">
    <p>
        Your e-mail address: <b><i>$from_email</i></b>.<br />Your name: <b><i>$from_name</i></b><br />These will be included in the e-mail content and used in the "From" header of the e-mail.
    <p>
        If this is incorrect, please <a href="$info_url">update your information</a>
    <p>
        Email address:<br />
        <input type="text" name="email_address">
    <p>
        Send an additional note to the sender:<br />
        (Use &lt;br&gt; for a new line if necessary)<br />
        <textarea rows="5" cols="40" name="email_text"></textarea>
    <p>
        <input type="hidden" name="not_first_time" value="1">
        <input type="submit" name="email_page" value= "_{EMAIL_THIS_PAGE}">
    </form>
END
));
//--------------------------------------------
    }
    else
    {
        return(ewiki_make_title($id, $id, 2) . ewiki_t(
//--------------------------------------------
<<<END
_{EMAILPAGE}
    <p>
        <b>Your e-mail address and/or name are currently not defined.</b>
    <p>
        In order to use this feature you must <a href="$info_url">update your information</a> and fill out both fields.
    </form>
END
));
//--------------------------------------------
    }
}

function buildEmail($id,$data,$emailText)
{
    $top = getTop();
    $preamble = getPreamble($id,$emailText);
    $page = renderPage($id,$data);
    $disclaimer = getDisclaimer();    
    $bottom = getBottom();
    
    $body = $top.$preamble.$page.$disclaimer.$bottom;

    return($body);
}

function renderPage($id,$data)
{
    global $ewiki_plugins, $ewiki_links;
    $ewiki_links = true;
    
    getUserInfo($from_email,$from_name);

    //allowed html tags for the email
    $allow = "<p> <b> <i> <ul> <li> <br /> <hr> <em> <tt> <h1> <h2> <h3> <h4> <h5> <font> <ol> <div> <dl> <dt> <dd> <font>";
    
    //Assume all links in page exist (eliminate the ? after undefined pages)
    $render_args = array("scan_links" => 0,"html" => (EWIKI_ALLOW_HTML||(@$data["flags"]&EWIKI_DB_F_HTML)),);

    $page = ewiki_make_title($id, $id, 2);
    $page .= $ewiki_plugins["render"][0] ($data["content"], $render_args);
    $page = strip_tags($page,$allow);
    
    return($page);
}

function getNote($emailText)
{
    if (empty($emailText))
    {
        return NULL;
    }
    else
    {
        //allowed html tags for the note
        $allow_note = "<br />";
        
        $note = "<p>Here is a small message from the sender:<br />$emailText<hr>";
        $note = strip_tags($note,$allow_note);
        
        return($note);
    }
}

function getTop()
{
    $top  = "<html><head>";
    $top .= ewiki_t("EWIKIEMAILCSS");
    $top .= "<title>[LiveWeb Snapshot]</title></head><body><div id=\"PageText\">";
    return($top);
}

function getPreamble($id,$emailText)
{
    getUserInfo($from_email,$from_name);
    
    $link_url = ewiki_script("view","$id");
    $link="https://www.burgiss.com/liveweb/$link_url";
    
    $note = getNote($emailText);
    
    $preamble  = "<p>The following e-mail is a stripped down snapshot of the \"$id\" page from Burgiss LiveWeb. ";
    $preamble .= "If you have an account with us, click <a href=\"$link\">here</a> to view it. ";
    $preamble .= "This was sent from $from_name with an e-mail address registered as $from_email</p>";
    $preamble .= "$note<hr><font face=\"Arial\" size=\"-1\">";
    
    return($preamble);
}

function getDisclaimer()
{
    $disclaimer  = "</font><hr><p><tt><b>NOTICE:</b> If you have received this in error, rest asured that you are not on any mailing lists. ";
    $disclaimer .= "If you have any concerns, please e-mail the <a href=\"mailto:webmaster@burgiss.com\">webmaster</a>.</tt></p>";
}

function getBottom()
{
    $bottom  = "</div></body></html>";
}

function getSubject($id)
{
    $subject = "[LiveWeb Snapshot] \"$id\"";
    return($subject);
}

function getHeaders($from_email,$from_name)
{
    /* To send HTML mail, you can set the Content-type header. */
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    
    /* additional headers */
    $headers .= "From: \"$from_name\" <$from_email>\r\n";
    $headers .= "X-Mailer: ErfurtWiki/".EWIKI_VERSION."\r\n";
    
    return($headers);
}

function ewiki_page_wiki_email_page($id=0, $data=0, $action=0)
{
    if ( isRequestNotSet($_REQUEST["email_page"],$_REQUEST["email_address"]) )
    {
        if($action == "emailpage")
        {
            getUserInfo($from_email,$from_name);
            $defined = isUserInfoDefined($from_email,$from_name);
            
            $warning = checkEmailField($_REQUEST["email_address"],$_REQUEST["not_first_time"]);
            
            $html = htmlFormGenerate($defined,$id,$warning,$from_email,$from_name);
            
            return($html);
        }
        else
        {
            return "You shouldn't be here.";
        }
    }
    
    #-- email generation and sending
    else
    {
        $emailAddress = $_REQUEST["email_address"];
        $emailText = $_REQUEST["email_text"];
        
        getUserInfo($from_email,$from_name);
        $headers = getHeaders($from_email,$from_name);
        $subject = getSubject($id);
        
        $body = buildEmail($id,$data,$emailText);        

        mail($emailAddress,$subject,$body,$headers);
        
        $success_message  = "<p><h4>Success!</h4></p><p>Page sent to <a href=mailto:$emailAddress>$emailAddress</a></p>";
        $success_message .= "<p>Click <a href=\"" . ewiki_script("view",$id) . "\">here</a> to return to the page you just sent.</p>";
        
        return ( ewiki_t($success_message) );
    }
}

?>