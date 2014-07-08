<?php @define("EWIKI_VERSION", "R1.02b");

/*
  ErfurtWiki - a pretty flexible, fast and user-friendly wiki framework
  
  Is PUBLIC DOMAIN (no license, no warranty); feel free to redistribute
  under any other license, if you want. (c) 2003-2005 WhoEver wants to.

  project+help:
    http://erfurtwiki.sourceforge.net/
    http://ewiki.berlios.de/
  lead by:
    Mario Salzer <mario*erphesfurtde>
    Andy Fundinger <andy*burgisscom>

  call it from within yoursite.php / layout script like this:
    <?php
       include("ewiki.php");
       $CONTENT = ewiki_page();
    ? >
    <HTML>...<BODY>...
    <?php
       echo $CONTENT;
    ? >
    ...</HTML>
*/

#-- for future backwards compatibility to R1.02b (temporary file dependencies)
if (!function_exists("ewiki_page_edit")) { include_once("plugins/edit.php"); }
if (!function_exists("ewiki_format")) { include_once("plugins/format.php"); }
if (!function_exists("ewiki_binary")) { include_once("plugins/feature/binary.php"); }
if (!function_exists("ewiki_author")) { include_once("plugins/misc.php"); }
if (!class_exists("ewiki_database_mysql")) { include_once("plugins/db/mysql.php"); }

        #-------------------------------------------------------- config ---

        #-- this disables most PHPs debugging (_NOTICE) messages
        error_reporting(0x0000377 & error_reporting());
#    error_reporting(E_ALL^E_NOTICE);  // development

    #-- the location of your ewiki-wrapper script
    define("EWIKI_SCRIPT", "?id=");            # relative to docroot
#    define("EWIKI_SCRIPT_URL", "http://../?id=");    # absolute URL

# now let's make use our $words-function for it
        $words = new MOD_words();
        #-- change to your needs (site lang)
    define("EWIKI_NAME", $words->getFormatted("WikiUnnamedWiki"));        # Wiki title
    define("EWIKI_PAGE_INDEX", "WikiFrontPage");    # default page
    define("EWIKI_PAGE_LIST", "PageIndex");
    define("EWIKI_PAGE_SEARCH", "SearchPages");
    define("EWIKI_PAGE_NEWEST", "NewestPages");
    define("EWIKI_PAGE_HITS", "MostVisitedPages");
    define("EWIKI_PAGE_VERSIONS", "MostOftenChangedPages");
    define("EWIKI_PAGE_UPDATES", "UpdatedPages");    # like RecentChanges

    #-- default settings are good settings - most often ;)
        #- look & feel
    define("EWIKI_PRINT_TITLE", 2);        # <h2>WikiPageName</h2> on top
    define("EWIKI_SPLIT_TITLE", 0);        # <h2>Wiki Page Name</h2>
    define("EWIKI_CONTROL_LINE", 1);    # EditThisPage-link at bottom
    define("EWIKI_LIST_LIMIT", 10);        # listing limit
        #- behaviour
    define("EWIKI_AUTO_EDIT", 1);        # edit box for non-existent pages
    define("EWIKI_EDIT_REDIRECT", 1);    # redirect after edit save
    define("EWIKI_DEFAULT_ACTION", "view"); # (keep!)
    define("EWIKI_CASE_INSENSITIVE", 1);    # wikilink case sensitivity
    define("EWIKI_HIT_COUNTING", 1);
    define("EWIKI_RESOLVE_DNS", 0);        # gethostbyaddr() when editing
    define("UNIX_MILLENNIUM", 1000000000);
        #- rendering
    define("EWIKI_ALLOW_HTML", 1);        # often a very bad idea
    define("EWIKI_HTML_CHARS", 1);        # allows for &#200;
    define("EWIKI_ESCAPE_AT", 1);        # "@" -> "&#x40;"
        #- http/urls
        define("EWIKI_SUBPAGE_LONGTITLE", 0);
        define("EWIKI_SUBPAGE_START", ".:/");   # set to "" to disable [.Sub] getting a link to [CurrentPage.Sub]
#        define("EWIKI_SUBPAGE_CHARS", ".:/-!");
    define("EWIKI_HTTP_HEADERS", 1);    # most often a good thing
    define("EWIKI_NO_CACHE", 1);        # browser+proxy shall not cache
    define("EWIKI_URLENCODE", 1);        # disable when _USE_PATH_INFO
    define("EWIKI_URLDECODE", 1);
#new!
    define("EWIKI_URL_UTF8", 1);        # fix UTF-8 parameters
    define("EWIKI_USE_PATH_INFO", 1);
    define("EWIKI_USE_ACTION_PARAM", 1);    # 2 for alternative link style
    define("EWIKI_ACTION_SEP_CHAR", "/");
        define("EWIKI_ACTION_TAKE_ASIS", 1);
    define("EWIKI_UP_PAGENUM", "n");    # _UP_ means "url parameter"
    define("EWIKI_UP_PAGEEND", "e");
    define("EWIKI_UP_BINARY", "binary");
    define("EWIKI_UP_UPLOAD", "upload");
    define("EWIKI_UP_PARENTID", "parent_page");
    define("EWIKI_UP_LISTLIM", "limit");
        #- other stuff
        define("EWIKI_DEFAULT_LANG", "en");
        define("EWIKI_CHARSET", "ISO-8859-1");  # nothing else supported
    #- user permissions
    define("EWIKI_PROTECTED_MODE", 0);    # disable funcs + require auth
    define("EWIKI_PROTECTED_MODE_HIDING", 1);  # hides disallowed actions
    define("EWIKI_AUTH_DEFAULT_RING", 3);    # 0=root 1=priv 2=user 3=view
    define("EWIKI_AUTO_LOGIN", 1);        # [auth_query] on startup

    #-- allowed WikiPageNameCharacters
    define("EWIKI_CHARS_L", "a-z_ÔøΩ$");    # \337-\377
    define("EWIKI_CHARS_U", "A-Z0-9");    # \300-\336
    define("EWIKI_CHARS", EWIKI_CHARS_L.EWIKI_CHARS_U);

        #-- database
    @define("EWIKI_DB_TABLE_NAME", "ewiki");    # MySQL / ADOdb
    @define("EWIKI_DBFILES_DIRECTORY", "/tmp");    # see "db_flat_files.php"
    define("EWIKI_DBA", "/tmp/ewiki.db3");        # see "db_dba.php"
    define("EWIKI_DBQUERY_BUFFER", 512*1024);    # 512K
    define("EWIKI_INIT_PAGES", "./init-pages");    # for initialization

    define("EWIKI_DB_F_TEXT", 1<<0);
    define("EWIKI_DB_F_BINARY", 1<<1);
    define("EWIKI_DB_F_DISABLED", 1<<2);
    define("EWIKI_DB_F_HTML", 1<<3);
    define("EWIKI_DB_F_READONLY", 1<<4);
    define("EWIKI_DB_F_WRITEABLE", 1<<5);
    define("EWIKI_DB_F_APPENDONLY", 1<<6);
    define("EWIKI_DB_F_SYSTEM", 1<<7);
    define("EWIKI_DB_F_PART", 1<<8);
    define("EWIKI_DB_F_MINOR", 1<<9);
    define("EWIKI_DB_F_HIDDEN", 1<<10);
    define("EWIKI_DB_F_ARCHIVE", 1<<11);
    define("EWIKI_DB_F_EXEC", 1<<17);
    define("EWIKI_DB_F_TYPE", EWIKI_DB_F_TEXT | EWIKI_DB_F_BINARY | EWIKI_DB_F_DISABLED | EWIKI_DB_F_SYSTEM | EWIKI_DB_F_PART);
    define("EWIKI_DB_F_ACCESS", EWIKI_DB_F_READONLY | EWIKI_DB_F_WRITEABLE | EWIKI_DB_F_APPENDONLY);
    define("EWIKI_DB_F_COPYMASK", EWIKI_DB_F_TYPE | EWIKI_DB_F_ACCESS | EWIKI_DB_F_HIDDEN | EWIKI_DB_F_HTML | EWIKI_DB_F_ARCHIVE);

    define("EWIKI_DBFILES_NLR", '\\n');
    define("EWIKI_DBFILES_ENCODE", 0 || (DIRECTORY_SEPARATOR != "/"));
    define("EWIKI_DBFILES_GZLEVEL", "2");

    #-- internal, auto-discovered
    define("EWIKI_ADDPARAMDELIM", (strstr(EWIKI_SCRIPT,"?") ? "&" : "?"));
    define("EWIKI_SERVER", ($_SERVER["HTTP_HOST"] ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"]) . ( ($_SERVER["SERVER_PORT"] != "80") ? (":" . $_SERVER["SERVER_PORT"]) : ""));
    define("EWIKI_BASE_URL", (@$_SERVER["HTTPS"] ? "https" : "http") . "://" . EWIKI_SERVER . substr(realpath(dirname(__FILE__)), strlen(realpath($_SERVER["DOCUMENT_ROOT"]))) . "/");    # URL to ewiki dir
    define("EWIKI_BASE_DIR", dirname(__FILE__));

    #-- binary content (images)
    define("EWIKI_ENGAGE_BINARY", 1);
    @define("EWIKI_SCRIPT_BINARY", /*"/binary.php?binary="*/  ltrim(strtok(" ".EWIKI_SCRIPT,"?"))."?".EWIKI_UP_BINARY."="  );
    define("EWIKI_CACHE_IMAGES", 1  &&!headers_sent());
    define("EWIKI_IMAGE_MAXSIZE", 64 *1024);
    define("EWIKI_IMAGE_MAXWIDTH", 3072);
    define("EWIKI_IMAGE_MAXHEIGHT", 2048);
    define("EWIKI_IMAGE_MAXALLOC", 1<<19);
    define("EWIKI_IMAGE_RESIZE", 1);
    define("EWIKI_IMAGE_ACCEPT", "image/jpeg,image/png,image/gif,application/x-shockwave-flash");
    define("EWIKI_IDF_INTERNAL", "internal://");
    define("EWIKI_ACCEPT_BINARY", 0);   # for arbitrary binary data files

    #-- misc
        define("EWIKI_TMP", isset($_SERVER["TEMP"]) ? $_SERVER["TEMP"] : "/tmp");
        define("EWIKI_VAR", "./var");        # should be world-writable
    define("EWIKI_LOGLEVEL", -1);        # 0=error 1=warn 2=info 3=debug
    define("EWIKI_LOGFILE", "/tmp/ewiki.log");

    #-- plugins (tasks mapped to function names)
    $ewiki_plugins["database"][] = "ewiki_database_mysql";
    $ewiki_plugins["edit_preview"][] = "ewiki_page_edit_preview";
    $ewiki_plugins["render"][] = "ewiki_format";
    $ewiki_plugins["init"][-5] = "ewiki_localization";
    if (EWIKI_ENGAGE_BINARY)
        $ewiki_plugins["init"][-1] = "ewiki_binary";
        $ewiki_plugins["handler"][-105] = "ewiki_eventually_initialize";
        $ewiki_plugins["handler"][] = "ewiki_intermap_walking";
    $ewiki_plugins["view_append"][-1] = "ewiki_control_links";
        $ewiki_plugins["page_final"][] = "ewiki_http_headers";
        $ewiki_plugins["page_final"][99115115] = "ewiki_page_css_container";
    $ewiki_plugins["edit_form_final"][] = "ewiki_page_edit_form_final_imgupload";
        $ewiki_plugins["format_block"]["pre"][] = "ewiki_format_pre";
        $ewiki_plugins["format_block"]["code"][] = "ewiki_format_pre";
        $ewiki_plugins["format_block"]["htm"][] = "ewiki_format_html";
        $ewiki_plugins["format_block"]["html"][] = "ewiki_format_html";
        $ewiki_plugins["format_block"]["comment"][] = "ewiki_format_comment";

    #-- internal pages
    $ewiki_plugins["page"][EWIKI_PAGE_LIST] = "ewiki_page_index";
    $ewiki_plugins["page"][EWIKI_PAGE_NEWEST] = "ewiki_page_newest";
    $ewiki_plugins["page"][EWIKI_PAGE_SEARCH] = "ewiki_page_search";
    if (EWIKI_HIT_COUNTING) $ewiki_plugins["page"][EWIKI_PAGE_HITS] = "ewiki_page_hits";
    $ewiki_plugins["page"][EWIKI_PAGE_VERSIONS] = "ewiki_page_versions";
    $ewiki_plugins["page"][EWIKI_PAGE_UPDATES] = "ewiki_page_updates";

    #-- page actions
    $ewiki_plugins["action"]["edit"] = "ewiki_page_edit";
    $ewiki_plugins["action_always"]["links"] = "ewiki_page_links";
    $ewiki_plugins["action"]["info"] = "ewiki_page_info";
    $ewiki_plugins["action"]["view"] = "ewiki_page_view";

    #-- helper vars ---------------------------------------------------
    $ewiki_config["idf"]["url"] = array("http://", "mailto:", EWIKI_IDF_INTERNAL, "ftp://", "https://", "data:", "irc://", "telnet://", "news://", "chrome://", "file://", "gopher://", "httpz://");
    $ewiki_config["idf"]["img"] = array(".jpeg", ".png", ".jpg", ".gif", ".j2k");
    $ewiki_config["idf"]["obj"] = array(".swf", ".svg");

    #-- entitle actions
    $ewiki_config["action_links"]["view"] = array(
        "edit" => "EDITTHISPAGE",    # ewiki_t() is called on these
        "links" => "BACKLINKS",
        "info" => "PAGEHISTORY",
        "like" => "LIKEPAGES",
    ) + (array)@$ewiki_config["action_links"]["view"];
    $ewiki_config["action_links"]["info"] = array(
        "view" => "browse",
        "edit" => "fetchback",
    ) + (array)@$ewiki_config["action_links"]["info"];

        #-- variable configuration settings (go into '$ewiki_config')
        $ewiki_config_DEFAULTS_tmp = array(
           "edit_thank_you" => 1,
           "edit_box_size" => "77x17",
           "print_title" => EWIKI_PRINT_TITLE,
           "split_title" => EWIKI_SPLIT_TITLE,
           "control_line" => EWIKI_CONTROL_LINE,
           "list_limit" => EWIKI_LIST_LIMIT,
           "script" => EWIKI_SCRIPT,
           "script_url" => (defined("EWIKI_SCRIPT_URL")?EWIKI_SCRIPT_URL:NULL),
           "script_binary" => EWIKI_SCRIPT_BINARY,
           "qmark_links" => "",
    #-- heart of the wiki -- don't try to read this! ;)
           "wiki_pre_scan_regex" =>    '/
        (?<![!~\\\\])
        ((?:(?:\w+:)*['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+){2,}[\w\d]*(?<!_))
        |\^([-'.EWIKI_CHARS_L.EWIKI_CHARS_U.']{3,})
        |\[ (?:"[^\]\"]+" | \s+ | [^:\]#]+\|)*  ([^\|\"\[\]\#]+)  (?:\s+ | "[^\]\"]+")* [\]\#] 
        |(\w{3,9}:\/\/[^\s\[\]\'\"()<>]+[^\s\[\]\'\"()<>!,.\-:;?])
                /x',
           "wiki_link_regex" => "\007 [!~\\\\]?(
        \#?\[[^<>\[\]\n]+\] |
        \w[-_.+\w]+@(\w[-_\w]+[.])+\w{2,} |
        \^[-".EWIKI_CHARS_U.EWIKI_CHARS_L."]{3,} |
        \b([\w]{3,}:)*([".EWIKI_CHARS_U."]+[".EWIKI_CHARS_L."]+){2,}\#?[\w\d]* |
        ([a-z]{2,9}://|mailto:|data:)[^\s\[\]\'\"()<>]+[^\s\[\]\'\"()<>,.!\-:;?]
        ) \007x",
    #-- rendering ruleset
           "wm_indent" => '<div style="margin-left:15px;" class="indent">',
           "wm_table_defaults" => 'cellpadding="2" border="1" cellspacing="0"',
           "wm_whole_line" => array("&gt;&gt;" => 'div align="right"'),
           "wm_max_header"=>3,
           "wm_publishing_headers"=>0,
           "htmlentities" => array(
        "&" => "&amp;",
        ">" => "&gt;",
        "<" => "&lt;",
           ),
           "wm_source" => array(
        "%%%" => "<br />",
        "&lt;br&gt;" => "<br />",
        "\t" => "        ",
        "\n;:" => "\n      ",   # workaround, replaces the old ;:
           ),
           "wm_list" => array(
        "-" => array('ul type="square"', "", "li"),
        "*" => array('ul type="circle"', "", "li"),
        "#" => array("ol", "", "li"),
        ":" => array("dl", "", "dd"),
        ";" => array("dl", "", "dt"),
           ),
           "wm_style" => array(
/*        "'''''" => array("<b><i>", "</i></b>"),
        "'''" => array("<b>", "</b>"),
        "''" => array("<em>", "</em>"),
        "__" => array("<strong>", "</strong>"),
        "^^" => array("<sup>", "</sup>"),
        "==" => array("<tt>", "</tt>"),*/
    #<off>#    "___" => array("<i><b>", "</b></i>"),
    #<off>#    "***" => array("<b><i>", "</i></b>"),
    #<off>#    "###" => array("<big><b>", "</b></big>"),
 #<broken+bug>#    "//" => array("<i>", "</i>"),   # conflicts with URLs, could only be done with regex
/*        "**" => array("<b>", "</b>"),
        "##" => array("<big>", "</big>"),
        "" => array("<small>", "</small>"),*/
           ),
           "wm_start_end" => array(
    #<off># array("[-", "-]", "<s>", "</s>"),
    #<off># array("(*", "*)", "<!--", "-->"),
           ),
    #-- rendering plugins
           "format_block" => array(
        "html" => array("&lt;html&gt;", "&lt;/html&gt;", "html", 0x0000),
        "htm" => array("&lt;htm&gt;", "&lt;/htm&gt;", "html", 0x0003),
        "code" => array("&lt;code&gt;", "&lt;/code&gt;", false, 0x0004),
        "pre" => array("&lt;pre&gt;", "&lt;/pre&gt;", false, 0x0027|4),
        "comment" => array("\n&lt;!--", "--&gt;", false, 0x0030),
                #<off>#  "verbatim" => array("&lt;verbatim&gt;", "&lt;/verbatim&gt;", false, 0x0030),
           ),
           "format_params" => array(
        "scan_links" => 1,
        "html" => EWIKI_ALLOW_HTML,
        "mpi" => 1,
           ),
        );
        #-- copy above settings into real _config[] array
        foreach ($ewiki_config_DEFAULTS_tmp as $set => $val) {
           if (!isset($ewiki_config[$set])) {
              $ewiki_config[$set] = $val;
           }
           elseif (is_array($val)) foreach ($val as $vali=>$valv) {
              if (is_int($vali)) {
                 $ewiki_config[$set][] = $valv;
              }
              elseif (!isset($ewiki_config[$set][$vali])) {
                 $ewiki_config[$set][$vali] = $valv;
              }
           }
        }
        $ewiki_config_DEFAULTS_tmp = $valv = $vali = $val = NULL;
        
        #-- special pre-sets
        $ewiki_config["ua"] = "ewiki/".EWIKI_VERSION
           . " (".PHP_OS."; PHP/".PHP_VERSION.")" . @$ewiki_config["ua"];


    #-- text  (never remove the "C" or "en" sections!)
        #
    $ewiki_t["C"] = (array)@$ewiki_t["C"] + array(
           "DATE" => "%a, %d %b %G %T %Z",
       "EDIT_TEXTAREA_RESIZE_JS" => '<a href="javascript:ewiki_enlarge()" style="text-decoration:none">+</a><script type="text/javascript"><!--'."\n".'function ewiki_enlarge() {var ta=document.getElementById("ewiki_content");ta.style.width=((ta.cols*=1.1)*10).toString()+"px";ta.style.height=((ta.rows*=1.1)*30).toString()+"px";}'."\n".'//--></script>',
        );
        #
    $ewiki_t["en"] = (array)@$ewiki_t["en"] + array(
       "EDITTHISPAGE" => $words->getFormatted("WikiEditThisPage"),
       "APPENDTOPAGE" => $words->getFormatted("WikiAddTo"),
       "BACKLINKS" => $words->getFormatted("WikiBackLinks"),
       "EDITCOMPLETE" => $words->getFormatted('WikiEditSaved', '<a href="$url">', '</a>'), // 'Your edit has been saved click <a href="$url">here</a> to see the edited page.',
       "PAGESLINKINGTO" => $words->getFormatted('WikiPagesLinkingTo', '"$title"'), //"Pages linking to \$title",
       "PAGEHISTORY" => $words->getFormatted("WikiPageInfo"),
       "INFOABOUTPAGE" => $words->getFormatted("WikiInfoAboutPage"), //"Information about page",
       "LIKEPAGES" => $words->getFormatted("WikiLikePages"), //"Pages like this",
       "NEWESTPAGES" => $words->getFormatted("WikiNewestPages"), //"Newest Pages",
       "LASTCHANGED" => $words->getFormatted("WikiLastChanged", "%c"), //"last changed on %c",
       "DOESNOTEXIST" => $words->getFormatted("WikiDoesNotExist"), //"This page does not yet exist, please click on EditThisPage if you'd like to create it.",
       "DISABLEDPAGE" => $words->getFormatted("WikiDisabledPage"), //"This page is currently not available.",
       "ERRVERSIONSAVE" => $words->getFormatted("WikiErrVersionSave"), /*"Sorry, while you edited this page someone else
        did already save a changed version. Please go back to the
        previous screen and copy your changes to your computers
        clipboard to insert it again after you reload the edit
        screen.", */
       "ERRORSAVING" => $words->getFormatted("WikiErrorSaving"), //"An error occoured while saving your changes. Please try again.",
       "THANKSFORCONTRIBUTION" => $words->getFormatted("WikiThanksForContribution"), //"Thank you for your contribution!",
       "CANNOTCHANGEPAGE" => $words->getFormatted("WikiCannotChangePage"), //"This page cannot be changed.",
       "OLDVERCOMEBACK" => $words->getFormatted("WikiOldVersionComeBack"), //"Make this old version come back to replace the current one",
       "PREVIEW" => $words->getFormatted("Preview"), //"Preview",
       "SAVE" => $words->getFormatted("Save"), //"Save",
       "CANCEL_EDIT" => $words->getFormatted("WikiCancelEdit"), //"CancelEditing",
       "UPLOAD_PICTURE_BUTTON" => $words->getFormatted("WikiUploadPictureButton"), //"upload picture &gt;&gt;&gt;",
       "EDIT_FORM_1" => $words->getFormatted("WikiEditForm1"), /*"It is <a href=\"".EWIKI_SCRIPT."GoodStyle\">GoodStyle</a>
                to just start writing. With <a href=\"".EWIKI_SCRIPT."WikiMarkup\">WikiMarkup</a>
        you can style your text later.<br />", */
       "EDIT_FORM_2" => $words->getFormatted("WikiEditForm2"), /*"<br />Please do not write things, which may make other
        people angry. And please keep in mind that you are not all that
        anonymous in the internet (find out more about your computers
        '<a href=\"http://google.com/search?q=my+computers+IP+address\">IP address</a>' at Google).", */
       "BIN_IMGTOOLARGE" => $words->getFormatted("WikiImageTooLarge"), //"Image file is too large!",
       "BIN_NOIMG" => $words->getFormatted("WikiNoImg"), //"This is no image file (inacceptable file format)!",
       "FORBIDDEN" => $words->getFormatted("WikiForbidden"), //"You are not authorized to access this page.",
       "FETCHBACK" => $words->getFormatted("WikiEdit"),
       "BROWSE" => $words->getFormatted("WikiBrowse"),
    );
        #
        $ewiki_t["es"] = (array)@$ewiki_t["es"] + array(
       "EDITTHISPAGE" => $words->getFormatted("WikiEditThisPage"),
           "APPENDTOPAGE" => $words->getFormatted("WikiAddTo"),
       "BACKLINKS" => $words->getFormatted("WikiBackLinks"),
           "EDITCOMPLETE" => $words->getFormatted('WikiEditSaved', '<a href="$url">', '</a>'), // 'Your edit has been saved click <a href="$url">here</a> to see the edited page.',
       "PAGESLINKINGTO" => $words->getFormatted('WikiPagesLinkingTo', '"$title"'), //"Pages linking to \$title",
       "PAGEHISTORY" => $words->getFormatted("WikiPageInfo"),
           "INFOABOUTPAGE" => "InformaciÔøΩ sobre la pÔøΩina",
           "LIKEPAGES" => "PÔøΩinas como esta",
           "NEWESTPAGES" => "PÔøΩinas mÔøΩ nuevas",
           "LASTCHANGED" => "ltima modificaciÔøΩ %d/%m/%Y a las %H:%M",
           "DOESNOTEXIST" => "Esta pÔøΩina an no existe, por favor eliga EditarEstaPÔøΩina si desea crearla.",
           "DISABLEDPAGE" => "Esta pÔøΩina no estÔøΩdisponible en este momento.",
           "ERRVERSIONSAVE" => "Disculpe, mientras editaba esta pÔøΩina alguiÔøΩ mÔøΩ
        salvÔøΩuna versiÔøΩ modificada. Por favor regrese a
        a la pantalla anterior y copie sus cambios a su computador
        para insertalos nuevamente despuÔøΩ de que cargue
        la pantalla de ediciÔøΩ.",
           "ERRORSAVING" => "OcurriÔøΩun error mientras se salvavan sus cambios. Por favor intente de nuevo.",
           "THANKSFORCONTRIBUTION" => "Gracias por su contribuciÔøΩ!",
           "CANNOTCHANGEPAGE" => "Esta pÔøΩina no puede ser modificada.",
           "OLDVERCOMEBACK" => "Hacer que esta versiÔøΩ antigua regrese a remplazar la actual",
           "PREVIEW" => "Previsualizar",
           "SAVE" => "Salvar",
           "CANCEL_EDIT" => "CancelarEdiciÔøΩ",
           "UPLOAD_PICTURE_BUTTON" => "subir grÔøΩica &gt;&gt;&gt;",
           "EDIT_FORM_1" => "<a href=\"".EWIKI_SCRIPT."BuenEstilo\">BuenEstilo</a> es
        escribir lo que viene a su mente. No se preocupe mucho
        por la apariencia. TambiÔøΩ puede agregar <a href=\"".EWIKI_SCRIPT."ReglasDeMarcadoWiki\">ReglasDeMarcadoWiki</a>
        mÔøΩ adelante si piensa que es necesario.<br />",
           "EDIT_FORM_2" => "<br />Por favor no escriba cosas, que puedan
        enfadar a otras personas. Y por favor tenga en mente que
        usted no es del todo anÔøΩimo en Internet 
        (encuentre mÔøΩ sobre 
        '<a href=\"http://google.com/search?q=my+computers+IP+address\">IP address</a>' de su computador con Google).",
           "BIN_IMGTOOLARGE" => "La grÔøΩica es demasiado grande!",
           "BIN_NOIMG" => "No es un archivo con una grÔøΩica (formato de archivo inaceptable)!",
           "FORBIDDEN" => "No estÔøΩautorizado para acceder a esta pÔøΩina.",
        );
        #

    $ewiki_t["nl"] = (array)@$ewiki_t["nl"] + array(
       "EDITTHISPAGE" => $words->getFormatted("WikiEditThisPage"),
           "APPENDTOPAGE" => $words->getFormatted("WikiAddTo"),
       "BACKLINKS" => $words->getFormatted("WikiBackLinks"),
           "EDITCOMPLETE" => $words->getFormatted('WikiEditSaved', '<a href="$url">', '</a>'), // 'Your edit has been saved click <a href="$url">here</a> to see the edited page.',
       "PAGESLINKINGTO" => $words->getFormatted('WikiPagesLinkingTo', '"$title"'), //"Pages linking to \$title",
       "PAGEHISTORY" => $words->getFormatted("WikiPageInfo"),
        );

    #-- InterWiki:Links
    $ewiki_config["interwiki"] = (array)@$ewiki_config["interwiki"] +
    array(
           "javascript" => "",  # this actually protects from javascript: links
           "url" => "",
           "jump" => "",        # fallback; if jump plugin isn't loaded
#          "self" => "this",
           "this" => defined("EWIKI_SCRIPT_URL")?EWIKI_SCRIPT_URL:EWIKI_SCRIPT,
           // real entries:
       "ErfurtWiki" => "http://erfurtwiki.sourceforge.net/",
       "InterWiki" => "MetaWiki",
       "MetaWiki" => "http://sunir.org/apps/meta.pl?",
       "Wiki" => "WardsWiki",
       "WardsWiki" => "http://www.c2.com/cgi/wiki?",
       "WikiFind" => "http://c2.com/cgi/wiki?FindPage&value=",
       "WikiPedia" => "http://www.wikipedia.com/wiki.cgi?",
       "MeatBall" => "MeatballWiki",
       "MeatballWiki" => "http://www.usemod.com/cgi-bin/mb.pl?",
       "UseMod"       => "http://www.usemod.com/cgi-bin/wiki.pl?",
       "CommunityWiki" => "http://www.emacswiki.org/cgi-bin/community/",
       "WikiFeatures" => "http://wikifeatures.wiki.taoriver.net/moin.cgi/",
       "PhpWiki" => "http://phpwiki.sourceforge.net/phpwiki/index.php3?",
       "LinuxWiki" => "http://linuxwiki.de/",
       "OpenWiki" => "http://openwiki.com/?",
       "Tavi" => "http://andstuff.org/tavi/",
       "TWiki" => "http://twiki.sourceforge.net/cgi-bin/view/",
       "MoinMoin" => "http://www.purl.net/wiki/moin/",
       "Google" => "http://google.com/search?q=",
       "ISBN" => "http://www.amazon.com/exec/obidos/ISBN=",
       "icq" => "http://www.icq.com/",
    );
    
// end of config




#-------------------------------------------------------------------- init ---


#-- bring up database backend
if (!isset($ewiki_db) && ($pf = $ewiki_plugins["database"][0])) {
   if (class_exists($pf)) {
      $ewiki_db = new $pf;
   }
   elseif (function_exists($pf)) {
      include("plugins/db/oldapi.php"); // eeeyk! temporary workaround!
   }
}

#-- init stuff, autostarted parts (done a 2nd time inside ewiki_page)
if ($pf_a = $ewiki_plugins["init"]) {
   ksort($pf_a);
   foreach ($pf_a as $pf) {
      $pf($GLOBALS);
   }
   unset($ewiki_plugins["init"]);
}



#-------------------------------------------------------------------- main ---

/*  This is the main function, which you should preferrably call to
    integrate the ewiki into your web site; it chains to most other
    parts and plugins (including the edit box).
    If you do not supply the requested pages "$id" we will fetch it
    from the pre-defined possible URL parameters.
*/
function ewiki_page($id=false) {

   global $ewiki_links, $ewiki_plugins, $ewiki_ring, $ewiki_t,
      $ewiki_errmsg, $ewiki_data, $ewiki_title, $ewiki_id,
      $ewiki_action, $ewiki_config;

   #-- output str
   $o = "";

   #-- selected page
   if (!strlen($id)) {
      $id = ewiki_id();
   }

   #-- page action
   $action = EWIKI_DEFAULT_ACTION;
   if ($delim = strpos($id, EWIKI_ACTION_SEP_CHAR)) {
      $a = substr($id, 0, $delim);
      if (EWIKI_ACTION_TAKE_ASIS || in_array($a, $ewiki_plugins["action"]) || in_array($a, $ewiki_plugins["action_always"])) {
         $action = rawurlencode($a);
         $id = substr($id, $delim + 1);
      }
   }
   if (EWIKI_USE_ACTION_PARAM && isset($_REQUEST["action"])) {
      $action = rawurlencode($_REQUEST["action"]);
   }
   $ewiki_data = array();
   $ewiki_id = $id;
   $ewiki_title = ewiki_split_title($id);
   $ewiki_action = $action;

   #-- more initialization
   if ($pf_a = @$ewiki_plugins["init"]) {
      ksort($pf_a);
      foreach ($pf_a as $pf) {
         $o .= $pf();
      }
      unset($ewiki_plugins["init"]);
   }
   #-- micro-gettext stub (for upcoming/current transition off of ewiki_t)
   if (!function_exists("_")) {
      function _($text) { return($text); }
      function gettext($text) { return($text); }
   }

   #-- fetch from db
   $version = false;
   if (!isset($_REQUEST["content"]) && ($version = 0 + @$_REQUEST["version"])) {
      $ewiki_config["forced_version"] = $version;
   }
   $ewiki_data = ewiki_db::GET($id, $version);
   $data = &$ewiki_data;

   #-- pre-check if actions exist
   $pf_page = ewiki_array($ewiki_plugins["page"], $id);
   
   #-- edit <form> or info/ page for non-existent and empty pages
   if (($action==EWIKI_DEFAULT_ACTION) && empty($data["content"]) && empty($pf_page)) {
      if ($data["version"] >= 2) {
         $action = "info";
      }
      elseif (EWIKI_AUTO_EDIT) {
         $action = "edit";
      }
      else {
         $data["content"] = ewiki_t("DOESNOTEXIST");
      }
   }

   #-- internal "create" action / used for authentication requests
   if (($action == "edit")&&(($data["version"]==0) && !isset($pf_page))) {
      $ewiki_config["create"] = $id;
   }

   #-- require auth
   if (EWIKI_PROTECTED_MODE) {
      if (!ewiki_auth($id, $data, $action, $ring=false, $force=EWIKI_AUTO_LOGIN)) {
         return($o.=$ewiki_errmsg);
      }
   }

   #-- handlers
   $handler_o = "";
   if ($pf_a = @$ewiki_plugins["handler"]) {
      ksort($pf_a);
      foreach ($pf_a as $pf_i=>$pf) {
         if ($handler_o = $pf($id, $data, $action, $pf_i)) { break; }
   }  }

   #-- stop here if page is not marked as _TEXT,
   #   perform authentication then, and let only administrators proceed
   if (!$handler_o) {
      if (!empty($data["flags"]) && (($data["flags"] & EWIKI_DB_F_TYPE) != EWIKI_DB_F_TEXT)) {
         if (($data["flags"] & EWIKI_DB_F_BINARY) && ($pf = $ewiki_plugins["handler_binary"][0])) {
            return($pf($id, $data, $action)); //_BINARY entries handled separately
         }
      elseif ((!EWIKI_PROTECTED_MODE || !ewiki_auth($id, $data, $action, 0, 1)) && ($ewiki_ring!=0)) {
            return(ewiki_t("DISABLEDPAGE"));
         }
      }
   }

   #-- finished by handler
   if ($handler_o) {
      $o .= $handler_o;
   }
   #-- actions that also work for static and internal pages
   elseif (($pf = @$ewiki_plugins["action_always"][$action]) && function_exists($pf)) {
      $o .= $pf($id, $data, $action);
   }
   #-- internal pages
   elseif ($pf_page && function_exists($pf_page)) {
      $o .= $pf_page($id, $data, $action);
   }
   #-- page actions
   else {
      $pf = @$ewiki_plugins["action"][$action];

      #-- fallback to "view" action
      if (empty($pf) || !function_exists($pf)) {

         $pf = "ewiki_page_view";
         $action = "view";     // we could also allow different (this is a
         // catch-all) view variants, but this would lead to some problems
      }

      $o .= $pf($id, $data, $action);
   }

   #-- error instead of page?
   if (empty($o) && $ewiki_errmsg) {
      $o = $ewiki_errmsg;
   }

   #-- html post processing
   if ($pf_a = $ewiki_plugins["page_final"]) {
      ksort($pf_a);
      foreach ($pf_a as $pf) {
         $pf($o, $id, $data, $action);
      }
   }

   if (EWIKI_ESCAPE_AT && !isset($ewiki_config["@"])) {
      $o = str_replace("@", "&#x40;", $o);
   }

   $ewiki_data = &$data;
   unset($ewiki_data["content"]);
   return($o);
}



#-- HTTP meta headers
function ewiki_http_headers(&$o, $id, &$data, $action, $saveasfilename=1) {
   global $ewiki_t, $ewiki_config;
   if (EWIKI_HTTP_HEADERS && !headers_sent()) {
      if (!empty($data)) {
         if (($uu = @$data["id"]) && $saveasfilename) @header('Content-Disposition: inline; filename="' . urlencode($uu) . '.html"');
         if ($uu = @$data["version"]) @header('Content-Version: ' . $uu);
         if ($uu = @$data["lastmodified"]) @header('Last-Modified: ' . gmstrftime($ewiki_t["C"]["DATE"], $uu));
      }
      if (EWIKI_NO_CACHE) {
         header('Expires: ' . gmstrftime($ewiki_t["C"]["DATE"], UNIX_MILLENNIUM));
         header('Pragma: no-cache');
         header('Cache-Control: no-cache, must-revalidate' . (($ewiki_author||EWIKI_PROTECTED_MODE)?", private":"") );
         # ", private" flag only for authentified users / _PROT_MODE
      }
      #-- ETag
      if ($data["version"] && ($etag=ewiki_etag($data)) || ($etag=md5($o))) {
         $weak = "W/" . urlencode($id) . "." . $data["version"];
         header("ETag: \"$etag\"");     ###, \"$weak\"");
         header("X-Server: $ewiki_config[ua]");
      }
   }
}
function ewiki_etag(&$data) {
   return(  urlencode($data["id"]) . ":" . dechex($data["version"]) . ":ewiki:" .
            dechex(crc32($data["content"]) & 0x7FFFBFFF)  );
}



#-- encloses whole page output with a descriptive <div>
function ewiki_page_css_container(&$o, &$id, &$data, &$action) {
   $sterilized_id = trim(preg_replace('/[^\w\d]+/', "-", $id), "-");
   $sterilized_id = preg_replace('/^(\d)/', 'page$1', $sterilized_id);
   $o = "<div class=\"wiki $action $sterilized_id\">\n" . $o . "\n</div>\n";
}



function ewiki_split_title ($id='', $split=-1, $entities=1) {
   if ($split==-1) {
      $split = $GLOBALS["ewiki_config"]["split_title"];
   }
   strlen($id) or ($id = $GLOBALS["ewiki_id"]);
   if ($split) {
      $id = preg_replace("/([".EWIKI_CHARS_L."])([".EWIKI_CHARS_U."]+)/", "$1 $2", $id);
   }
   return($entities ? htmlentities($id) : $id);
}



function ewiki_add_title(&$html, $id, &$data, $action, $go_action="links") {
   if (EWIKI_PRINT_TITLE)
      $html = "<div class=\"text-head\">\n"
         . ewiki_make_title($id, '', 1, $action, $go_action)
         . "\n</div>\n" . $html;
}


function ewiki_make_title($id='', $title='', $class=3, $action="view", $go_action="links", $may_split=1) {

   global $ewiki_config, $ewiki_plugins, $ewiki_title, $ewiki_id;

   #-- advanced handler
   if ($pf = @$ewiki_plugins["make_title"][0]) {
      return($pf($title, $class, $action, $go_action, $may_split));
   }
   #-- disabled
   elseif (!$ewiki_config["print_title"]) {
      return("");
   }

   #-- get id
   if (empty($id)) {
      $id = $ewiki_id;
   }

   #-- get title
   if (!strlen($title)) {
      $title = $ewiki_title;  // already in &html; format
   }
   elseif ($ewiki_config["split_title"] && $may_split) {
      $title = ewiki_split_title($title, $ewiki_config["split_title"], 0&($title!=$ewiki_title));
   }
   else {
//      $title = htmlentities($title);
   }

   #-- title mangling
   if ($pf_a = @$ewiki_plugins["title_transform"]) {
      foreach ($pf_a as $pf) { $pf($id, $title, $go_action); }
   }

   #-- simple headline
   $o = rawurldecode($title);

   // h2.page.title is obsolete; h2.text-title recommended
   return('<h1 class="text-title page title">' . $o . '</h1>');
}




function ewiki_page_view($id, &$data, $action, $all=1) {

   global $ewiki_plugins, $ewiki_config;
   $o = "";

    // undo html-entities in update function -dh
    $data["content"] = html_entity_decode($data["content"], ENT_NOQUOTES, 'UTF-8');

   #-- render requested wiki page  <-- goal !!!
   $render_args = array(
      "scan_links" => 1,
      "html" => (EWIKI_ALLOW_HTML||(@$data["flags"]&EWIKI_DB_F_HTML)),
   );
   $o .= '<div class="text-body text">' . "\n"
      //. $ewiki_plugins["render"][0] ($data["content"], $render_args)
      . ewiki_format($data["content"], $render_args) // -dh
      . "</div>\n";
   if (!$all) {
      return($o);
   }

   #-- control line + other per-page info stuff
   if ($pf_a = $ewiki_plugins["view_append"]) {
      ksort($pf_a);
      $o .= "<div class=\"wiki-plugins\">\n";
      foreach ($pf_a as $n => $pf) { $o .= $pf($id, $data, $action); }
      $o .= "</div>\n";
   }
   if ($pf_a = $ewiki_plugins["view_final"]) {
      ksort($pf_a);
      foreach ($pf_a as $n => $pf) { $pf($o, $id, $data, $action); }
   }

   if (!empty($_REQUEST["thankyou"]) && $ewiki_config["edit_thank_you"]) {
      $o = '<div class="text-prefix system-message">'
         . ewiki_t("THANKSFORCONTRIBUTION") . "</div>\n" . $o;
   }

   if (EWIKI_HIT_COUNTING) {
      ewiki_db::HIT($id);
   }

   return($o);
}




#-------------------------------------------------------------------- util ---


/*  retrieves "$id/$action" string from URL / QueryString / PathInfo,
    change this in conjunction with ewiki_script() to customize your URLs
    further whenever desired
*/
function ewiki_id() {
   ($id = @$_REQUEST["id"]) or
   ($id = @$_REQUEST["name"]) or
   ($id = @$_REQUEST["page"]) or
   ($id = @$_REQUEST["file"]) or
   (EWIKI_USE_PATH_INFO)
      and isset($_SERVER["PATH_INFO"])
      and ($_SERVER["PATH_INFO"] != $_SERVER["SCRIPT_NAME"])  // Apache+PHP workaround
      and ($id = ltrim($_SERVER["PATH_INFO"], "/")) or
   (!isset($_REQUEST["id"])) and ($id = trim(strtok($_SERVER["QUERY_STRING"], "&?;")));
   if (!strlen($id) || ($id=="id=")) {
      $id = EWIKI_PAGE_INDEX;
   }
   (EWIKI_URLDECODE) && ($id = urldecode($id));
   return($id);
}




/*  replaces EWIKI_SCRIPT, works more sophisticated, and
    bypasses various design flaws
    - if only the first parameter is used (old style), it can contain
      a complete "action/WikiPage" - but this is ambigutious
    - else $asid is the action, and $id contains the WikiPageName
    - $ewiki_config["script"] will now be used in favour of the constant
    - needs more work on _BINARY, should be a separate function
*/
function ewiki_script($asid, $id=false, $params="", $bin=0, $html=1, $script=NULL) {

   global $ewiki_config, $ewiki_plugins;

   #-- get base script url from config vars
   if (empty($script)) {
      $script = &$ewiki_config[!$bin?"script":"script_binary"];
   }
   $alt_style = (EWIKI_USE_ACTION_PARAM >= 2);
   $ins_prefix = (EWIKI_ACTION_TAKE_ASIS);

   #-- separate $action and $id for old style requests
   if ($id === false) {
      if (strpos($asid, EWIKI_ACTION_SEP_CHAR) !== false) {
         $asid = strtok($asid, EWIKI_ACTION_SEP_CHAR);
         $id = strtok("\000");
      }
      else {
         $id = $asid;
         $asid = "";
      }
   }

   #-- prepare params
   if (is_array($params)) {
      $uu = $params;
      $params = "";
      if ($uu) foreach ($uu as $k=>$v) {
         $params .= (strlen($params)? "&" : "") . rawurlencode($k) . "=" . rawurlencode($v);
      }
   }
   #-- use action= parameter instead of prefix/
   if ($alt_style) {
      $params = "action=$asid" . (strlen($params)? "&": "") . $params;
      if (!$ins_prefix) {
         $asid = "";
      }
   }

   #-- workaround slashes in $id
   if (empty($asid) && (strpos($id, EWIKI_ACTION_SEP_CHAR) !== false) && !$bin && $ins_prefix) {
      $asid = EWIKI_DEFAULT_ACTION;
   }
   /*paranoia*/ $asid = trim($asid, EWIKI_ACTION_SEP_CHAR);

   #-- make url
   if (EWIKI_URLENCODE) {
      $id = urlencode($id);
      $asid = urlencode($asid);
   }
   else {
      // only urlencode &, %, ? for example
   }
   $url = $script;
   if ($asid) {
      $id = $asid . EWIKI_ACTION_SEP_CHAR . $id;  #= "action/PageName"
   }
   if (strpos($url, "%s") !== false) {
      $url = str_replace("%s", $id, $url);
   }
   else {
      $url .= $id;
   }

   #-- add url params
   if (strlen($params)) {
      $url .= (strpos($url,"?")!==false ? "&":"?") . $params;
   }

   #-- fin
   if ($html) {
      $url = str_replace("&", "&amp;", $url);
   }
   return($url);
}


/*  this ewiki_script() wrapper is used to generate URLs to binary
    content in the ewiki database
*/
function ewiki_script_binary($asid, $id=false, $params=array(), $upload=0) {

   $upload |= is_string($params) && strlen($params) || count($params);

   #-- generate URL directly to the plainly saved data file,
   #   see also plugins/db/binary_store
   if (defined("EWIKI_DB_STORE_URL") && !$upload) {
      $url = EWIKI_DB_STORE_URL . urlencode(rawurlencode(strtok($id, "?")));
   }

   #-- else get standard URL (thru ewiki.php) from ewiki_script()
   else {
      $url = ewiki_script($asid, $id, $params, "_BINARY=1");
   }

   return($url);
}


/*  this function returns the absolute ewiki_script url, if EWIKI_SCRIPT_URL
    is set, else it guesses the value
*/
function ewiki_script_url($asid="", $id="", $params="") {

   global $ewiki_action, $ewiki_id, $ewiki_config;

   if ($asid||$id) {
      return ewiki_script($asid, $id, $params, false, true, ewiki_script_url());
   }
   if ($url = $ewiki_config["script_url"]) {
      return($url);
   }

   $scr_template = $ewiki_config["script"];
   $scr_current = ewiki_script($ewiki_action, $ewiki_id);
   $req_uri = $_SERVER["REQUEST_URI"];
   $qs = $_SERVER["QUERY_STRING"]?1:0;
   $sn = $_SERVER["SCRIPT_NAME"];

   if (($p = strpos($req_uri, $scr_current)) !== false) {
      $url = substr($req_uri, 0, $p) . $scr_template;
   }
   elseif (($qs) && (strpos($scr_template, "?") !== false)) {
      $url = substr($req_uri, 0, strpos($req_uri, "?"))
           . substr($scr_template, strpos($scr_template, "?"));
   }
   elseif (($p = strrpos($sn, "/")) && (strncmp($req_uri, $sn, $p) == 0)) {
      $url = $sn . "?id=";
   }
   else {
      return(NULL);   #-- could not guess it
   }
 
   $url = (@$_SERVER["HTTPS"] ? "https" : "http") . "://"
        . EWIKI_SERVER . $url; 

   return($ewiki_config["script_url"] = $url);
}




#------------------------------------------------------------ page plugins ---


#-- links/ action
function ewiki_page_links($id, &$data, $action) {
   $o = ewiki_make_title($id, ewiki_t("PAGESLINKINGTO", array("title"=>$id)), 1, $action, "", "_MAY_SPLIT=1");
   if ($pages = ewiki_get_backlinks($id)) {
      $o .= ewiki_list_pages($pages);
   } else {
      $o .= ewiki_t("This page isn't linked from anywhere else.");
   }
   return($o);
}

#-- get all pages, that are linking to $id
function ewiki_get_backlinks($id) {
   $result = ewiki_db::SEARCH("refs", $id);
   $pages = array();
   $id_i = EWIKI_CASE_INSENSITIVE ? strtolower($id) : $id;
   while ($row = $result->get(0, 0x0077)) {
      if (strpos(EWIKI_CASE_INSENSITIVE ?strtolower($row["refs"]) :$row["refs"], "\n$id_i\n") !== false) {
         $pages[] = $row["id"];
      }
   }
   return($pages);
}

#-- get all existing pages (as array of pagenames), that are linked from $id
function ewiki_get_links($id) {
   if ($data = ewiki_db::GET($id)) {
      $refs = explode("\n", trim($data["refs"]));
      $r = array();
      foreach (ewiki_db::FIND($refs) as $id=>$exists) {
         if ($exists) {
            $r[] = $id;
         }
      }
      return($r);
   }
}



#-- outputs listing from page name array
function ewiki_list_pages($pages=array(), $limit=NULL,
                          $value_as_title=0, $pf_list=false)
{
   global $ewiki_plugins;
   $o = "";

   if (!isset($limit)) {
      ($limit = 0 + $_REQUEST[EWIKI_UP_LISTLIM])
      or ($limit = EWIKI_LIST_LIMIT);
   }
   $is_num = !empty($pages[0]);
   $lines = array();
   $n = 0;

   if ($pages) foreach ($pages as $id=>$add_text) {

      $title = $id;
      $params = "";

      if (is_array($add_text)) {
         list($id, $params, $title, $add_text) = $add_text;
         if (!$title) { $title = $id; }
      }
      elseif ($is_num) {
         $id = $title = $add_text;
         $add_text = "";
      }
      elseif ($value_as_title) {
         $title = $add_text;
         $add_text = "";
      }

      $lines[] = '<a href="' . ewiki_script("", $id, $params) . '">' . ewiki_split_title($title) . '</a> ' . $add_text;

      if (($limit > 0)  &&  ($n++ >= $limit)) {
         break;
      }
   }

   if ($pf_a = @$ewiki_plugins["list_transform"]) {
      foreach ($pf_a as $pf_transform) {
         $pf_transform($lines);
      }
   }

   if (($pf_list) || ($pf_list = @$ewiki_plugins["list_pages"][0])) {
      $o = $pf_list($lines);
   }
   elseif($lines) {
      $o = "&middot; " . implode("<br />\n&middot; ", $lines) . "<br />\n";
   }

   return($o);
}


#---------------------------------------------------------- page plugins ---


#-- list of all existing pages (without hidden + protected)
function ewiki_page_index($id=0, $data=0, $action=0, $args=array()) {

   global $ewiki_plugins;

   $o = ewiki_make_title($id, $id, 2);

   $exclude = $args ? ("\n" . implode("\n", preg_split("/\s*[,;:\|]\s*/", $args["exclude"])) . "\n") : "";
   $sorted = array();
   $sorted = array_keys($ewiki_plugins["page"]);

   $result = ewiki_db::GETALL(array("flags"), EWIKI_DB_F_TYPE, EWIKI_DB_F_TEXT);
   while ($row = $result->get(0, 0x0037, EWIKI_DB_F_TEXT)) {
      if (!stristr($exclude, "\n".$row["id"]."\n")) {
         $sorted[] = $row["id"];
      }
   }
   natcasesort($sorted);

   $o .= ewiki_list_pages($sorted, 0, 0, $ewiki_plugins["list_dict"][0]);
   return($o);
}



#-- scans database for extremes (by given page meta data information),
#   generates page listing then from list
//@TODO: split $asc parameter into $asc and $firstver
function ewiki_page_ordered_list($orderby="created", $asc=0, $print="%n", $title="", $bad_flags=0) {

   $o = ewiki_make_title("", $title, 2, ".list", "links", 0);

   $sorted = array();
   $result = ewiki_db::GETALL(array($orderby));

   while ($row = $result->get(0, 0x0037, EWIKI_DB_F_TEXT)) {
      if ($asc >= 0) {
         // version 1 is most accurate for {hits}
         $row = ewiki_db::GET($row["id"], 1);
      }
      if (! ($bad_flags & $row["flags"])) {
         $sorted[$row["id"]] = $row[$orderby];
      }
   }

   if ($asc != 0) { arsort($sorted); }
   else { asort($sorted); }

   if ($sorted) foreach ($sorted as $name => $value) { 
      if (empty($value)) { $value = "0"; }
      $sorted[$name] = strftime(str_replace('%n', $value, $print), $value);
   }
   $o .= ewiki_list_pages($sorted);
   
   return($o);
}



function ewiki_page_newest($id, $data, $action) {
   return( ewiki_page_ordered_list("created", -1, ewiki_t("LASTCHANGED"), ewiki_t("NEWESTPAGES")) );
}

function ewiki_page_updates($id, $data, $action) {
   return ewiki_page_ordered_list("lastmodified", -1, ewiki_t("LASTCHANGED"), EWIKI_PAGE_UPDATES, EWIKI_DB_F_MINOR);
}

function ewiki_page_hits($id, $data, $action) {
   return( ewiki_page_ordered_list("hits", 1, "%n hits", EWIKI_PAGE_HITS) );
}

function ewiki_page_versions($id, $data, $action) {
   return( ewiki_page_ordered_list("version", -1, "%n changes", EWIKI_PAGE_VERSIONS) );
}







function ewiki_page_search($id, &$data, $action) {

   $o = ewiki_make_title($id, $id, 2, $action);

   if (! ($q = @$_REQUEST["q"])) {

      $o .= '<form action="' . ewiki_script("", $id) . '" method="POST">';
      $o .= ewiki_form("q::30", "") . '<br /><br />';
      $o .= ewiki_form(":submit", $id);
      $o .= '</form>';
   }
   else {
      $found = array();

      $q = preg_replace('/\s*[^\041-\175\200-\377]\s*/', ' ', $q);
      if ($q) foreach (explode(" ", $q) as $search) {

         if (empty($search)) { continue; }

         $result = ewiki_db::SEARCH("content", $search);

         while ($row = $result->get()) {

            #-- show this entry in page listings?
            if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $row, "view")) {
               continue;
            }

            $found[] = $row["id"];
         }
      }

      $o .= ewiki_list_pages($found);
   }
 
   return($o);
}








function ewiki_page_info($id, &$data, $action) {

   global $ewiki_plugins, $ewiki_config, $ewiki_links;

   $o = ewiki_make_title($id, ewiki_t("INFOABOUTPAGE")." '{$id}'", 2, $action,"", "_MAY_SPLIT=1"); 

   $flagnames = array(
      "TEXT", "BIN", "DISABLED", "HTML", "READONLY", "WRITEABLE",
      "APPENDONLY", "SYSTEM", "PART", 10=>"HIDDEN", 17=>"EXEC",
   );
   $show = array(
      "version", "author",
      "lastmodified",  "created", "hits", "refs",
      "flags", "content"
   );
   $no_refs = (boolean)$ewiki_config["info_refs_once"];

   #-- versions to show
   $v_start = $data["version"];
   if ( ($uu=0+@$_REQUEST[EWIKI_UP_PAGENUM]) && ($uu<=$v_start) ) {
      $v_start = $uu;
   }
   $v_end = $v_start - $ewiki_config["list_limit"];
   if ( ($uu=0+@$_REQUEST[EWIKI_UP_PAGEEND]) && ($uu<=$v_start) ) {
      $v_end = $uu;
   }
   $v_end = max($v_end, 1);

   #-- go
   # the very ($first) entry is rendered more verbosely than the others
   for ($v=$v_start,$first=1; ($v>=$v_end); $v--,$first=0) {

      $current = ewiki_db::GET($id, $v);

      if (!strlen(trim($current["id"])) || !$current["version"]) {
         continue;
      }

      $o .= '<table class="version-info"  cellpadding="2" cellspacing="1">' . "\n";

      #-- additional info-actions
      $o .= '<tr><td></td><td class="action-links">';
      $o .= ewiki_control_links_list($id, $data, $ewiki_config["action_links"]["info"], $current["version"]);
      $o .= "</td></tr>\n";

      #-- print page database entry
      foreach($show as $i) {

         $value = @$current[$i];

         #-- show database {fields} differently
         if ($i == "meta") {
            $str = "";
            if ($value) foreach ($value as $n2=>$d2) {
               foreach ((array)$d2 as $n=>$d) {
                  if (is_int($n)) { $n = $n2; } else { $n = "_$n"; }
                  $str .= htmlentities("$n: $d") . "<br />\n";
               }
            }
            $value = $str;
         }
         elseif (($i =="lastmodified")||($i =="created")) {    #-- {lastmodified}, {created}
            $value = strftime("%c", $value);
         }
         elseif ($i == "content") {
            $value = strlen(trim($value)) . " bytes";
            $i = "content size";
         }
         elseif ($first && ($i == "refs") && !(EWIKI_PROTECTED_MODE && (EWIKI_PROTECTED_MODE_HIDING>=2))) {
            $a = explode("\n", trim($value));
            $ewiki_links = ewiki_db::FIND($a);
            ewiki_merge_links($ewiki_links);
            foreach ($a as $n=>$link) {
               $a[$n] = ewiki_link_regex_callback(array("$link"), "force_noimg");
            }
            $value = implode(", ", $a);
         }
         elseif (strpos($value, "\n") !== false) {       #-- also for {refs}
            if ($no_refs && ($i == "refs")) { continue; }
            $value = str_replace("\n", ", ", trim($value));
         }
         elseif ($i == "version") {
            $value = '<a href="' .
               ewiki_script("", $id, array("version"=>$value)) . '">' .
               $value . '</a>';
         }
         elseif ($i == "flags") {
            $fstr = "";
            for ($n = 0; $n < 32; $n++) {
              if ($value & (1 << $n)) {
                 if (! ($s=$flagnames[$n])) { $s = "UU$n"; }
                 $fstr .= $s . " ";
              }
            }
            $value = $fstr;
         }
         elseif ($i == "author") {
            $value = ewiki_author_html($value);
         }

         $o .= '<tr class="page-' . $i . '"><td valign="top"><b>' . $i . '</b></td>' .
               '<td>' . $value . "</td></tr>\n";

      }

      $o .= "</table><br />\n";
   }

   #-- page result split
   if ($v >= 1) {
      $o .= "<br /><div class=\"chunk-list\">\n" . ewiki_chunked_page($action, $id, -1, $v+1, 1) . "\n</div><br />";
   }
   #-- ext info actions
   $o .= '<div class="summary control-links">' . ewiki_control_links_list($id, $data, $ewiki_config["action_links"]["summary"]) . "</div>\n";

   return($o);
}




function ewiki_chunked_page($action, $id, $dir=-1, $start=10, $end=1, $limit=0, $overlap=0.25, $collapse_last=0.67) {

   global $ewiki_config;

   if (empty($limit)) {
      $limit = $ewiki_config["list_limit"];
   }
   if ($overlap < 1) {
      $overlap = (int) ($limit * $overlap);
   }

   $p = "";
   $n = $start;

   while ($n) {

      $n -= $dir * $overlap;

      $e = $n + $dir * ($limit + $overlap);

      if ($dir<0) {
         $e = max(1, $e);
         if ($e <= $collapse_last * $limit) {
            $e = 1;
         }
      }
      else {
         $e = min($end, $e);
         if ($e >= $collapse_last * $limit) {
            $e = $end;
         }
      }

      $o .= ($o?" &middot; ":"")
         . '<a href="'.ewiki_script($action, $id, array(EWIKI_UP_PAGENUM=>$n, EWIKI_UP_PAGEEND=>$e))
         . '">'. "$n-$e" . '</a>';

      if (($n=$e) <= $end) {
         $n = false;
      }
   }

   return('<div class="chunked-result">'. $o .'</div>');
}






function ewiki_page_edit($id, $data, $action) {

   global $ewiki_links, $ewiki_author, $ewiki_plugins, $ewiki_ring,
      $ewiki_errmsg, $ewiki_config;

   $hidden_postdata = array();
   
   #-- previous version come back
   if ($ewiki_config["forced_version"]) {

      $current = ewiki_db::GET($id);
      $data["version"] = $current["version"];
      unset($current);

      unset($_REQUEST["content"]);
      unset($_REQUEST["version"]);
   }

   #-- edit interception
   if ($pf_a = @$ewiki_plugins["edit_hook"]) foreach ($pf_a as $pf) {
      if ($output = $pf($id, $data, $hidden_postdata)) {
         return($output);
      }
   }

   #-- permission checks   //@TODO: move into above hook, split out flag checks
   if (isset($ewiki_ring)) {
      $ring = $ewiki_ring;
   } else { 
      $ring = 3;
   }
   $flags = @$data["flags"];
   if (!($flags & EWIKI_DB_F_WRITEABLE)) {

      #-- perform auth
      $edit_ring = (EWIKI_PROTECTED_MODE>=2) ? (2) : (NULL);
      if (EWIKI_PROTECTED_MODE && !ewiki_auth($id, $data, $action, $edit_ring, "FORCE")) {
         return($ewiki_errmsg);
      }

      #-- flag checking
      if (($flags & EWIKI_DB_F_READONLY) and ($ring >= 2)) {
         return(ewiki_t("CANNOTCHANGEPAGE"));
      }
      if (($flags) and (($flags & EWIKI_DB_F_TYPE) != EWIKI_DB_F_TEXT) and ($ring >= 1)) {
         return(ewiki_t("CANNOTCHANGEPAGE"));
      }
   }

   #-- "Edit Me"
   $o = ewiki_make_title($id, ewiki_t("EDITTHISPAGE").(" '{$id}'"), 2, $action, "", "_MAY_SPLIT=1");

    #-- normalize to UNIX newlines
   $_REQUEST["content"] = str_replace("\015\012", "\012", $_REQUEST["content"]);
   $_REQUEST["content"] = str_replace("\015", "\012", $_REQUEST["content"]);

    // encode entities -dh
    $_REQUEST["content"] = htmlentities($_REQUEST["content"], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');

    #-- preview
   if (isset($_REQUEST["preview"])) {
      $o .= $ewiki_plugins["edit_preview"][0]($data);
   }

   #-- save
   if (isset($_REQUEST["save"])) {

         #-- check for concurrent version saving
         $error = 0;
         if ((@$data["version"] >= 1) && (($data["version"] != @$_REQUEST["version"]) || (@$_REQUEST["version"] < 1))) {

            $pf = $ewiki_plugins["edit_patch"][0];

            if (!$pf || !$pf($id, $data)) {
               $error = 1;
               $o .= ewiki_t("ERRVERSIONSAVE") . "<br /><br />";
            }

         }
         if (!$error) {

            #-- new pages` flags
            $set_flags = (@$data["flags"] & EWIKI_DB_F_COPYMASK);
            if (($set_flags & EWIKI_DB_F_TYPE) == 0) {
               $set_flags = EWIKI_DB_F_TEXT;
            }
            if (EWIKI_ALLOW_HTML) {
               $set_flags |= EWIKI_DB_F_HTML;
            }

            #-- mk db entry
            $save = array(
               "id" => $id,
               "version" => @$data["version"] + 1,
               "flags" => $set_flags,
               "content" => $_REQUEST["content"],
               "created" => ($uu=@$data["created"]) ? $uu : time(),
               "meta" => ($uu=@$data["meta"]) ? $uu : "",
               "hits" => ($uu=@$data["hits"]) ? $uu : "0",
            );
            ewiki_data_update($save);

            #-- edit storage hooks
            if ($pf_a = @$ewiki_plugins["edit_save"]) {
               foreach ($pf_a as $pf) {
                  $pf($save, $data);
               }
            }

            #-- save
            if (!$save || !ewiki_db::WRITE($save)) {

               $o .= $ewiki_errmsg ? $ewiki_errmsg : ewiki_t("ERRORSAVING");

            }
            else {
               #-- prevent double saving, when ewiki_page() is re-called
               $_REQUEST = $_GET = $_POST = array();

               $o = ewiki_t("THANKSFORCONTRIBUTION") . "<br /><br />";

               if (EWIKI_EDIT_REDIRECT) {
                  $url = ewiki_script("", $id, "thankyou=1", 0, 0, ewiki_script_url());
                  $o .= ewiki_t("EDITCOMPLETE", array("url"=>htmlentities($url)));

                  if (EWIKI_HTTP_HEADERS && !headers_sent()) {
                     header("Status: 303 Redirect for GET");
                     $sid = defined("SID") ? EWIKI_ADDPARAMDELIM.SID : "";
                     header("Location: $url$sid");
                     #header("URI: $url");
                     #header("Refresh: 0; URL=$url");
                  }
                  else {
                     $o .= '<meta http-equiv="Location" content="'.htmlentities($url).'">';
                  }
               }
               else {
                  $o .= ewiki_page($id);
               }

            }

         }

         //@REWORK
         // header("Reload-Location: " . ewiki_script("", $id, "", 0, 0, ewiki_script_url()) );

   }
   else {
      #-- Edit <form>
      $o .= ewiki_page_edit_form($id, $data, $hidden_postdata);

      #-- additional forms
      if ($pf_a = $ewiki_plugins["edit_form_final"]) foreach ($pf_a as $pf) {
         $pf($o, $id, $data, $action);
      }
   }

   return($o);
}


function ewiki_data_update(&$data, $author="") {
   ewiki_db::UPDATE($data, $author);
}


function ewiki_new_data($id, $flags=EWIKI_DB_F_TEXT, $author="") {
   return(ewiki_db::CREATE($id, $flags, $author));
}



#-- edit <textarea>
function ewiki_page_edit_form(&$id, &$data, &$hidden_postdata) {

   global $ewiki_plugins, $ewiki_config, $callbackId;

   #-- previously edited, or db fetched content
   if (@$_REQUEST["content"] || @$_REQUEST["version"]) {
      $data = array(
         "version" => &$_REQUEST["version"],
         "content" => &$_REQUEST["content"]
      );
   }
   else {
      if (empty($data["version"])) {
         $data["version"] = 1;
      }
      @$data["content"] .= "";
   }

   #-- normalize to DOS newlines
   $data["content"] = str_replace("\015\012", "\012", $data["content"]);
   $data["content"] = str_replace("\015", "\012", $data["content"]);
   $data["content"] = str_replace("\012", "\015\012", $data["content"]);
   
   // Undo HTMLEntities -dh
   $data["content"] = html_entity_decode($data["content"], ENT_QUOTES);

   $hidden_postdata["version"] = &$data["version"];

   #-- edit textarea/form
   $o .= ewiki_t("EDIT_FORM_1")
       . '<form method="POST" enctype="multipart/form-data" action="'
       . ewiki_script("edit", $id) . '" name="ewiki"'
       . ' accept-charset="'.EWIKI_CHARSET.'">' . "\n";

   #-- additional POST vars
   if ($hidden_postdata) foreach ($hidden_postdata as $name => $value) {
       $o .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />' ."\n";
   }
   
   // Callback-ID -dh
   $o .= '<input type="hidden" name="'.$callbackId.'" value="1"/>';
   // Callback-tag
   // $o .= $callback_tag;

   if (EWIKI_CHARSET=="UTF-8") {
      $data["content"] = utf8_encode($data["content"]);
   }
   ($cols = strtok($ewiki_config["edit_box_size"], "x*/,;:")) && ($rows = strtok("x, ")) || ($cols=70) && ($rows=15);
   $o .= '<textarea wrap="soft" id="ewiki_content" name="content" rows="'.$rows . '" cols="' .$cols. '">'
      . htmlentities($data["content"]) . "</textarea>"
      . $GLOBALS["ewiki_t"]["C"]["EDIT_TEXTAREA_RESIZE_JS"];

   #-- more <input> elements before the submit button
   if ($pf_a = $ewiki_plugins["edit_form_insert"]) foreach ($pf_a as $pf) {
      $o .= $pf($id, $data, $action);
   }

   $o .= "\n<br />\n"
      . ewiki_form("save:submit", " &nbsp; ".ewiki_t("SAVE")." &nbsp; ")
      . " &nbsp; "
/*      . ewiki_form("preview:submit", " &nbsp; ".ewiki_t("PREVIEW")." &nbsp; ")*/ // TODO: Doesnt work yet
      . ' &nbsp; <a class="cancel" href="'. ewiki_script("", $id) . '">' . ewiki_t("CANCEL_EDIT") . '</a><br />';

   #-- additional form elements
   if ($pf_a = $ewiki_plugins["edit_form_append"]) foreach ($pf_a as $pf) {
      $o .= $pf($id, $data, $action);
   }

   $o .= "\n</form>\n"
      . ewiki_t("EDIT_FORM_1");

   return('<div class="edit-box">'. $o .'</div>');
}



#-- pic upload form
function ewiki_page_edit_form_final_imgupload(&$o, &$id, &$data, &$action) {
   if (EWIKI_SCRIPT_BINARY && EWIKI_UP_UPLOAD && EWIKI_IMAGE_MAXSIZE) {
      $o .= "\n<br />\n". '<div class="image-upload">'
      . '<form action='
      . '"'. ewiki_script_binary("", EWIKI_IDF_INTERNAL, "", "_UPLOAD=1") .'"'
      . ' method="POST" enctype="multipart/form-data" target="_upload">'
      . '<input type="file" name="'.EWIKI_UP_UPLOAD.'"'
      . (defined("EWIKI_IMAGE_ACCEPT") ? ' accept="'.EWIKI_IMAGE_ACCEPT.'" />' : "")
      . '<input type="hidden" name="'.EWIKI_UP_BINARY.'" value="'.EWIKI_IDF_INTERNAL.'">'
      . '<input type="hidden" name="'.EWIKI_UP_PARENTID.'" value="'.htmlentities($id).'">'
      . '&nbsp;&nbsp;&nbsp;'
      . '<input type="submit" value="'.ewiki_t("UPLOAD_PICTURE_BUTTON").'">'
      . '</form></div>'. "\n";
  }
}


function ewiki_page_edit_preview(&$data) {
   return( '<div class="preview">'
           . '<hr noshade="noshade" />'
           . "<div align=\"right\">" . ewiki_t("PREVIEW") . "</div><hr noshade=\"noshade\" /><br />\n"
           . $GLOBALS["ewiki_plugins"]["render"][0]($_REQUEST["content"], 1, EWIKI_ALLOW_HTML || (@$data["flags"]&EWIKI_DB_F_HTML))
           . '<hr noshade="noshade" /><br />'
           . "</div>"
   );
}







function ewiki_control_links($id, &$data, $action, $hide_hr=0, $hide_mtime=0) {

   global $ewiki_plugins, $ewiki_ring, $ewiki_config, $callbackId;
   $action_links = & $ewiki_config["action_links"][$action];

   #-- disabled
   if (!$ewiki_config["control_line"]) {
      return("");
   }

   $o = "\n"
      . '<div align="right" class="action-links control-links">';
   if (!$hide_hr && !@$ewiki_config["control_line.no_deco"]) {
      $o .=  "\n<br />\n" . '<hr noshade="noshade" />' . "\n";
   }

   if (@$ewiki_config["forced_version"] && ewiki_auth($id, $data, "edit")) {

      $o .= '<form action="' . ewiki_script("edit", $id) . '" method="POST">' .
             
             '<input type="hidden" name="'.$callbackId.'" value="1"/>'. // -dh

            '<input type="hidden" name="edit" value="old">' .
            '<input type="hidden" name="version" value="'.$ewiki_config["forced_version"].'">' .
            '<input type="submit" value="' . ewiki_t("OLDVERCOMEBACK") . '"></form> ';
   }
   else {
      $o .= ewiki_control_links_list($id, $data, $action_links);
   }

   if (!$hide_mtime && ($data["lastmodified"] >= UNIX_MILLENNIUM)) { 
      $o .= '<small>' . strftime(ewiki_t("LASTCHANGED"), @$data["lastmodified"]) . '</small>';
   }

   $o .= "</div>\n";
   return($o);
}


#-- the core of ewiki_control_links, separated for use in info and plugins
function ewiki_control_links_list($id, &$data, $action_links, $version=0) {
   global $ewiki_plugins, $ewiki_config;
   $o = "";
   ($ins = @$ewiki_config["control_links_enclose"]) or ($ins = "    ");

   if ($action_links) foreach ($action_links as $action => $title)
   if (!empty($ewiki_plugins["action"][$action]) || !empty($ewiki_plugins["action_always"][$action]) || strpos($action, ":/"))
   {
      if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($id, $data, $action)) {
         continue;
      }
      $o .= $ins[1] . '<a class="button" href="' .
         ( strpos($action, "://")
            ? $action   # an injected "action" URL
            : ewiki_script($action, $id, $version?array("version"=>$version):NULL)
         ) . '">' . ewiki_t($title) . '</a> ' . $ins[2];
#++ &nbsp;
   }
   
   $o = $ins[0] . $o . $ins[3];
   return($o);
}




# ============================================================= rendering ===





########  ###   ###  #########  ###  ###   ###  #######
########  ####  ###  #########  ###  ####  ###  #######
###       ##### ###  ###             ##### ###  ###
######    #########  ###  ####  ###  #########  ######
######    #########  ###  ####  ###  #########  ######
###       ### #####  ###   ###  ###  ### #####  ###
########  ###  ####  #########  ###  ###  ####  #######
########  ###   ###  #########  ###  ###   ###  #######


/*
   The _format() function transforms $wiki_source pages into <html> strings,
   also calls various markup and helper plugins during the transformation
   process. The $params array can activate various features and extensions.
   only accepts UNIX newlines!
*/
function ewiki_format (
            $wiki_source,
            $params = array()
         )
{
   global $ewiki_links, $ewiki_plugins, $ewiki_config;

   #-- state vars
   $params = (array)$ewiki_config["format_params"] + (array)$params;
   $s = array(
      "in" => 0,         # current input $iii[] block array index
      "para" => "",
      "line" => "",
      "post" => "",      # string to append after current line/paragraph
      "line_i" => 0,
      "lines" => array(),
      "list" => "",      # lists
      "tbl" => 0,        # open table?
      "indent" => 0,     # indentation
      "close" => array(),
   );
   #-- aliases
   $in = &$s["in"]; 
   $line = &$s["line"];
   $lines = &$s["lines"];
   $para = &$s["para"];
   $post = &$s["post"];
   $list = &$s["list"];

   #-- input and output arrays
   if ($wiki_source[0] == "<") {            # also prepend an empty line 
      $wiki_source = "\n" . $wiki_source;    # for faster strpos() searchs
   }
   $core_flags = 0x137F;            # (0x0001=WikiMarkup, 0x0002=WikiLinks, 0x1000=MoreBlockPlugins)
   $iii = array(
      0 => array(
         0 => $wiki_source."\n",    # body + empty line
         1 => $core_flags,          # rendering / behaviour options
         2 => "core",               # block plugin name
      )
   );
   $ooo = array(
   );
   unset($wiki_source);

   #-- plugins
   $pf_tbl = @$ewiki_plugins["format_table"][0];
   $pf_line = @$ewiki_plugins["format_line"];

   #-- wikimarkup (wm)
   $htmlentities = $ewiki_config["htmlentities"];
   $wm_indent = &$ewiki_config["wm_indent"];
   $s["wm_indent_close"] = "</" . strtok($wm_indent, "< />"). ">";
   $wm_table_defaults = &$ewiki_config["wm_table_defaults"];
   $wm_source = &$ewiki_config["wm_source"];
   $wm_list = &$ewiki_config["wm_list"];
   $wm_list_chars = implode("", array_keys($wm_list));
   $wm_style = &$ewiki_config["wm_style"];
   $wm_start_end = &$ewiki_config["wm_start_end"];
   $wm_max_header = &$ewiki_config["wm_max_header"];
   $wm_publishing_headers = &$ewiki_config["wm_publishing_headers"];
   $wm_whole_line = &$ewiki_config["wm_whole_line"];

   #-- eleminate html
   $iii[0][0] = strtr($iii[0][0], $htmlentities);
   unset($htmlentities["&"]);

   #-- pre-processing plugins (working on wiki source)
   if ($pf_source = $ewiki_plugins["format_source"]) {
      foreach ($pf_source as $pf) $pf($iii[0][0]);
   }

   #-- simple markup
   $iii[0][0] = strtr($iii[0][0], $wm_source);


   #-- separate input into blocks ------------------------------------------
   if ($ewiki_config["format_block"])
   foreach ($ewiki_config["format_block"] as $btype=>$binfo) {
      #-- disabled block plugin?
      if ($binfo[2] && !$params[$binfo[2]])  {
         continue;
      }
      $i = 0;

      #-- traverse $iii[]
      $in = -1;
      while ((++$in) < count($iii)) {
         #-- search fragment delimeters
         if ($iii[$in][1] & 0x0100)
         while (
            ($c = & $iii[$in][0]) &&
            (($l = strpos($c, $binfo[0])) !== false) &&
            ($r = strpos($c, $binfo[1], $l))   )
         {
            $l_len = strlen($binfo[0]);
            $r_len = strlen($binfo[1]);

            $repl = array();
            // pre-text
            if (($l > 0) && trim($text = substr($c, 0, $l))) {
               $repl[] = array($text, $core_flags, "core");
            }
            // the extracted part
            if (trim($text = substr($c, $l+$l_len, $r-$l-$l_len))) {
               $repl[] = array($text, $binfo[3], "$btype");
            }
            // rest
            if (($r+$r_len < strlen($c)) && trim($text = substr($c, $r+$r_len))) {
               $repl[] = array($text, $core_flags, "core");
            }
            array_splice($iii, $in, 1, $repl);

            $in += 1;
         }
      }
   }

   #-- run format_block plugins
   $in = -1;
   while ((++$in) < count($iii)) {
      if (($btype = $iii[$in][2]) && ($pf_a = @$ewiki_plugins["format_block"][$btype])) {
         $c = &$iii[$in][0];
         if ($iii[$in][1] & 0x0400) {
            $c = strtr($c, array_flip($htmlentities));
         }
         foreach ($pf_a as $pf) {   
            # current buffer $c and pointer $in into $iii[] and state $s
            $pf($c, $in, $iii, $s, $btype);
         }
      }
   }

   #-- wiki markup ------------------------------------------------------
   $para = "";
   $in = -1;   
   while ((++$in) < count($iii)) {
      #-- wikimarkup
      if ($iii[$in][1] & 0x0001) {

         #-- input $lines buffer, and output buffer $ooo array
         $lines = explode("\n", $iii[$in][0]);
         $ooo[$in] = array(
            0 => "",
            1 => $iii[$in][1]
         );
         $out = &$ooo[$in][0];
         $s["bmarkup"] = ($iii[$in][1] & 0x0008);   # lists/tables/paras
         $s["nopara"] = !($s["bmarkup"]);   # disables indentation & paragraphs
# should this disable lists and tables and ...
# shouldn't it rather be a bit flag?

         #-- walk through wiki source lines
         $line_max = count($lines);
         if ($lines) foreach ($lines as $s["line_i"]=>$line) {
 #echo "line={$s[line_i]}:$line\n";

            #-- empty lines separate paragraphs
            if (!ltrim($line)) {
               ewiki_format_close_para($ooo, $s);
               ewiki_format_close_tags($ooo, $s);
               if (!$s["nopara"]) {
                  $out .= "\n";
               }
               $line = '';
            }
    
    
            #-- list/table/headline "BlockMarkup" ---------------------------
            if ($s["bmarkup"]) {
        
                #-- horiz bar
                if (!$list && !strncmp($line, "----", 4)) {
                   $s["para"] .= "<hr noshade=\"noshade\" />\n";
                   continue;
                }
                #-- html comment
                if (!strncmp($line, "&lt;!--", 7)) {
                   $out .= "<!-- " . htmlentities(str_replace("--", "__", substr($line, 7))) . " -->\n";
                   continue;
                }

                strlen($line) && ($c0 = $line[0])
                or ($c0 = "\000");

                #-- tables ------------------------
                if (($c0 == "|") && ($s["tbl"] || ($line[strlen($line)-1] == "|"))) {
                   if (!$s["tbl"]) {
                      ewiki_format_close_para($ooo, $s);
                      ewiki_format_close_tags($ooo, $s);
                      $s["list"] = "";
                   }
                   $line = substr($line, 1);
                   if ($line[strlen($line)-1] == "|") {
                      $line = substr($line, 0, -1);
                   }
                   if ($pf_tbl) { 
                      $pf_tbl($line, $ooo, $s);
                   }
                   else {
                      if (!$s["tbl"]) {  
                         $out .= "<table " . $wm_table_defaults . ">\n";
                         $s["close"][] = "\n</table>"; 
                      }
                      $line = "<tr>\n<td>" . str_replace("|", "</td>\n<td>", $line) . "</td>\n</tr>";
                   }
                   $s["tbl"] = 1;
                   $para = false;
                }
                elseif ($s["tbl"]) {
                   $s["tbl"] = 0;
                }


                #-- headlines
                if (($c0 == "!") && ($excl = strspn($line, "!"))) {
                
                   if ($excl > $wm_max_header) { 
                      $excl = $wm_max_header;
                   }
                   $line = substr($line, $excl);
                   //publishing headers go from h2 smaller "like word"
                   $excl = $wm_publishing_headers? (1+$excl) :5 - $excl;
                   $line = "<h$excl>" . $line . "</h$excl>";
                   if ($para) {
                      ewiki_format_close_para($ooo, $s);
                   }
                   ewiki_format_close_tags($ooo, $s);
                   $para = false;
                }


                #-- whole-line wikimarkup
                foreach ($wm_whole_line as $find=>$replace) {
                  if (substr($line, 0, strlen($find)) == $find) {
                     $line = "<$replace>" . ltrim(substr($line,strlen($find))) . "</".strtok($replace," ").">";
                  }
                }

                #-- indentation (space/tab markup)
                $n_indent = 0;
                if (!$list && (!$s["nopara"]) && ($n_indent = strspn($line, " "))) {
                   $n_indent = (int) ($n_indent / 2.65);
                   while ($n_indent > $s["indent"]) { 
                      $s["para"] .= $wm_indent;
                      $s["indent"]++;
                   }
                }
                while ($n_indent < $s["indent"]) { 
                   $s["para"] .= $s["wm_indent_close"] . "\n";
                   $s["indent"]--;
                }


                #-- list markup -------------------
                if (isset($wm_list[$c0])) {
                   if (!$list) {
                      ewiki_format_close_para($ooo, $s);
                      ewiki_format_close_tags($ooo, $s);
                   }
                   $new_len = strspn($line, $wm_list_chars);
                   $new_list = substr($line, 0, $new_len);
                   $old_len = strlen($list);
                   $lchar = $new_list[$new_len-1];
                   list($lopen, $ltag1, $ltag2) = $wm_list[$lchar];

                   #-- exception: "--" is treated as literal
                   if (($old_len===0) && (($new_len>=2) && ($new_list=="--"))) {
                      $list = '';         # change this ^^ to an OR (||)
                                          # to filter bad list markup
                   }
                   else {
                      #-- cut line
                      $line = substr($line, $new_len);
                      $lspace = "";
                      $linsert = "";
                      if ($ltag1) {
                         $linsert = "<$ltag1>" . strtok($line, $lchar) . "</$ltag1> ";
                         $line = strtok("\000");
                      }

                      #-- enum list types
                      if (($lchar == "#") && ($line[1] == " ") && ($ltype = $line[0])) {
                         if (($ltype >= "0") || ($ltype <= "z")) {
                            $line = substr($line, 2);
                         } else {
                            $ltype = "";
                         }
                      }

                      #-- add another <li>st entry
                      if ($new_len == $old_len) {
                         $lspace = str_repeat("  ", $new_len);
                         $out .=  "</$ltag2>\n" . $lspace . $linsert . "<$ltag2>";
                      }
                      #-- add list
                      elseif ($new_len > $old_len) {
                         while ($new_len > ($old_len=strlen($list))) {
                            $lchar = $new_list[$old_len];
                            $list .= $lchar;
                            list($lopen, $ltag1, $ltag2) = $wm_list[$lchar];
                            $lclose = strtok($lopen, " ");
                            $lspace = str_repeat("  ", $new_len);

                            if (isset($ltype) && $ltype) {
                               $ltype = ($ltype<"A"?"1": ($ltype=="I"?"I": ($ltype=="i"?"i": ($ltype<"a"?"A": "a"))));
                               $lopen .= " type=\"$rltype\"";
                               if ($rltype!=$ltype) { $lopen .= " start=\"$ltype\""; }
                            }
                            
                            $out .= "\n$lspace<$lopen>\n" . "$lspace". $linsert . "<$ltag2>";
                            $s["close"][] = "$lspace</$lclose>";
                            $s["close"][] = "$lspace</$ltag2>";
                         }
                      }
                      #-- close lists
                      else {
                         while ($new_len < ($old_len=strlen($list))) {
                            $remove = $old_len-$new_len;
                            ewiki_format_close_tags($ooo, $s, 2*$remove);
                            $list = substr($list, 0, -$remove);
                         }
                         if ($new_len) {
                            $lspace = str_repeat("  ", $new_len);
                            $out .= "$lspace</$ltag2>\n" . $lspace . $linsert . "<$ltag2>";
                         }
                      }

                      $list = $new_list;
                      $para = false;
                   }
                }
                elseif ($list) {
                   if ($c0 == " ") {
                      $para = false;
                   }
                   else {
                      ewiki_format_close_tags($ooo, $s);
                      $list = "";
                   }
                }

            }#--if $s["bmarkup"] --------------------------------------------


            #-- text style triggers
            foreach ($wm_style as $find=>$replace) {
               $find_len = strlen($find);
               $loop = 20;
               while(($loop--) && 
               (($l = strpos($line, $find)) !== false) && 
               ($r = strpos($line, $find, $l + $find_len))) {
                  $line = substr($line, 0, $l) . $replace[0] .
                          substr($line, $l + strlen($find), $r - $l - $find_len) .
                          $replace[1] . substr($line, $r + $find_len);
               }
            }

            #-- start-end markup
            foreach ($wm_start_end as $d) {
               $len0 = strlen($d[0]);
               $loop = 20;
               while(($loop--) && (($l = strpos($line, $d[0])) !== false) && ($r = strpos($line, $d[1], $l + $len0))) {
                  $len1 = strlen($d[1]);
                  $line = substr($line, 0, $l) . $d[2] .
                          substr($line, $l + $len0, $r - $l - $len0) .
                          $d[1] . substr($line, $r + $len1);
               }
            }

            #-- call wiki source formatting plugins that work on current line
            if ($pf_line) {
               foreach ($pf_line as $pf) $pf($out, $line, $post);
            }


            #-- add formatted line to page-output
            $line .= $post;
            if ($para === false) {
               $out .= $line;
               $para = "";
            }
            else {
               $para .= $line . "\n";
            }

         }

         #-- last block, or flags dictate a WikiSource blocks/para break?
         if (!isset($iii[$in+1]) || (($iii[$in+1][1] & 0x0010) ^ ($iii[$in][1] & 0x0010)) ) {
            ewiki_format_close_para($ooo, $s);
            ewiki_format_close_tags($ooo, $s);
         }
      }
      #-- copy as is into output buffer
      else {
         $ooo[$in] = $iii[$in];
      }
      $iii[$in] = array();
   }


   #-- wiki linking ------------------------------------------------------
   $scan_src = "";
   for ($in=0; $in<count($ooo); $in++) {
// BUG: does not respect the (absence of) flags of individual blocks
      #-- join together multiple WikiSource blocks
      if ($ooo[$in][1] & 0x0022) {
         while (isset($ooo[$in+1]) && ($ooo[$in][1] & 0x0002) && ($ooo[$in+1][1] & 0x0002)) {
            $ooo[$in] = array(
               0 => $ooo[$in][0] . "\n" . $ooo[$in+1][0],
               1 => $ooo[$in][1] | $ooo[$in+1][1],
            );
            array_splice($ooo, $in+1, 1);
         }
      }
      #-- html character entities
      if (EWIKI_HTML_CHARS || ($ooo[$in][1] & 0x0004)) {
         $ooo[$in][0] = str_replace("&amp;#", "&#", $ooo[$in][0]);
      }
      $scan_src .= $ooo[$in][0];
   }

   #-- pre-scan
   if ($params["scan_links"]) {
      ewiki_scan_wikiwords($scan_src, $ewiki_links);
   }
   if ($pf_linkprep = @$ewiki_plugins["format_prepare_linking"]) {
      foreach ($pf_linkprep as $pf) $pf($scan_src);
   }
   $scan_src = NULL;

   #-- finally the link-creation-regex
   for ($in=0; $in<count($ooo); $in++) {
      if ($ooo[$in][1] & 0x0002) {
         ewiki_render_wiki_links($ooo[$in][0]);
      }
   }


   #-- fin: combine all blocks into html string ----------------------------
   $html = "";
   for ($in=0; $in<count($ooo); $in++) {
      $html .= $ooo[$in][0] . "\n";
      $ooo[$in] = 0;
   }
   #-- call post processing plugins
   if ($pf_final = $ewiki_plugins["format_final"]) {
      foreach ($pf_final as $pf) $pf($html);
   }
   return($html);
}



function ewiki_format_close_para(&$ooo, &$s) {
   $out = &$ooo[$s["in"]][0];
   #-- output text block
   if (trim($s["para"])) {
      #-- indentation
      while ($s["indent"]) {
         $s["para"] .= $s["wm_indent_close"];
         $s["indent"]--;
      }
      #-- enclose in <p> tags
      if (!$s["nopara"]) {
         $s["para"] = "\n<p>" . ltrim($s["para"], "\n") . "</p>\n";
      }
      #-- paragraph formation plugins
      if ($pf_a = @$GLOBALS["ewiki_plugins"]["format_para"]) {
         foreach ($pf_a as $pf) {
            $pf($s["para"], $ooo, $s);
         }
      }
      $out .= $s["para"];
      $s["para"] = "";
   }
}


function ewiki_format_close_tags(&$ooo, &$s, $count=100) {
   $out = &$ooo[$s["in"]][0];
   if (!is_array($s) || !is_array($s["close"])) { 
      die("\$s is garbaged == $s!!");
   }
   while (($count--) && ($add = array_pop($s["close"]))) {
      $out .= $add . "\n";
   }
}


function ewiki_format_pre(&$str, &$in, &$iii, &$s, $btype) {
   $str = "<pre class=\"markup $btype\">" . $str . "</pre>";
}


function ewiki_format_html(&$str, &$in, &$iii, &$s) {
   $he = array_reverse($GLOBALS["ewiki_config"]["htmlentities"]);
   $str = strtr($str, array_flip($he));
   $str = "<span class=\"markup html\">" . $str . "\n</span>\n"; 
}


function ewiki_format_comment(&$str, &$in, &$iii, &$s, $btype) {
   $str = "<!-- "  . str_replace("--", "", $str) . " -->";
}




/* unclean pre-scanning for WikiWords in a page,
   pre-query to the db */
function ewiki_scan_wikiwords(&$wiki_source, &$ewiki_links, $se=0) {

   global $ewiki_config, $ewiki_id;

   #-- find matches
   preg_match_all($ewiki_config["wiki_pre_scan_regex"], $wiki_source, $uu);
   $uu = array_merge((array)$uu[1], (array)$uu[2], (array)@$uu[3], (array)$uu[4]);

   #-- clean up list, trim() spaces (allows more unclean regex) - page id unification
   foreach ($uu as $i=>$id) {
      $uu[$i] = trim($id);
      $uu[$i] = str_replace(' ','_',$uu[$i]); // Edit 'Nov09: replace spaces with underscores
   }
   unset($uu[""]);
   $uu = array_unique($uu);

   #-- unfold SubPage names
   if (EWIKI_SUBPAGE_START) {
      foreach ($uu as $i=>$id) {
         if ($id && (strpos(EWIKI_SUBPAGE_START, $id[0]) !== false)) {
            if ($id[1] == "/") { $id = substr($id, 1); }
            $uu[$i] = $ewiki_id . $id;
         }
   }  }

   #-- query db
   $ewiki_links = ewiki_db::FIND($uu);

   #-- strip email adresses
   if ($se) {
      foreach ($ewiki_links as $c=>$uu) {
         if (strpos($c, "@") && (strpos($c, ".") || strpos($c, ":"))) {
            unset($ewiki_links[$c]);
         }
   }  }
}



/* regex on page content,
   handled by callback (see below)
*/
function ewiki_render_wiki_links(&$o) {
   global $ewiki_links, $ewiki_config, $ewiki_plugins;

   #-- merge with dynamic pages list
   ewiki_merge_links($ewiki_links);

   #-- replace WikiWords
   $link_regex = &$ewiki_config["wiki_link_regex"];
   $o = preg_replace_callback($link_regex, "ewiki_link_regex_callback", $o);

   #-- cleanup
///////////   unset($ewiki_links);
}


/* combines with page plugin list,
   and makes all case-insensitive (=lowercased)
   in accord with EWIKI_CASE_INSENSITIVE 
        (handled within ewiki_array)
*/
function ewiki_merge_links(&$ewiki_links) {
   global $ewiki_plugins;
   if ($ewiki_links !== true) {
      foreach ($ewiki_plugins["page"] as $page=>$uu) {
         $ewiki_links[$page] = 1;
      }
      $ewiki_links = ewiki_array($ewiki_links);
   }
}



/* link rendering (p)regex callback
   (ooutch, this is a complicated one)
*/
function ewiki_link_regex_callback($ii, $force_noimg=0) {

   global $ewiki_links, $ewiki_plugins, $ewiki_config, $ewiki_id;

   $str = trim($ii[0]);
   $type = array();
   $states = array();

   #-- link bracket '[' escaped with '!' or '~'
   if (($str[0] == "!") || ($str[0] == "~") || ($str[0] == "\\")) {
      return(substr($str, 1));
   }
   if ($str[0] == "#") {
      $states["define"] = 1;
      $str = substr($str, 1);
   }
   if ($str[0] == "[") {
      $states["brackets"] = 1;
      $str = substr($str, 1, -1);
      if (!strlen($str)) { return("[]"); }  //better: $ii[0]
   }

   #-- explicit title given via [ title | WikiLink ]
   $href = $title = strtok($str, "|");
   if ($uu = strtok("|")) {
      $title = $uu;
      $states["titled"] = 1;
   }
   #-- title and href swapped: swap back
   if (strpos("://", $title) || strpos($title, ":") && !strpos($href, ":")) {
      $uu = $title; $title = $href; $href = $uu;
   }
   #-- new entitling scheme [ url "title" ]
   if ((($l=strpos($str, '"')) < ($r=strrpos($str, '"'))) && ($l!==false) ) {
      $title = substr($str, $l + 1, $r - $l - 1);
      $href = substr($str, 0, $l) . substr($str, $r + 1);
      $states["titled"] = 1;
      if (!$href) { return($ii[0]); }
   }

   #-- strip spaces
   $spaces_l = ($href[0]==" ") ?1:0;
   $spaces_r = ($href[strlen($href)-1]==" ") ?1:0;
   $title = ltrim(trim($title), "^");
   $href = ltrim(trim($href), "^");

   #-- strip_htmlentities()
   if (1&&    (strpos($href, "&")!==false) && strpos($href, ";")) {
      ewiki_stripentities($href);
   }
 
   #-- anchors
   $href2 = "";
   if (($p = strrpos($href, "#")) && ($p) && ($href[$p-1] != "&")) {
      $href2 = trim(substr($href, $p));
      $href = trim(substr($href, 0, $p));
   }
   elseif ($p === 0) {
      $states["define"] = 1;
   }
   if ($href == ".") {
      $href = $ewiki_id;
   }
   


   #-- SubPages
   $c0 = $href[0];
   if ($c0 && (strpos(EWIKI_SUBPAGE_START, $c0) !== false)) {
      $_set = EWIKI_SUBPAGE_LONGTITLE && ($href==$title);
      if (($href[1] == "/")) {   ##($c0 == ".") && 
         $href = substr($href, 1);
      }
      $href = $ewiki_id . $href;
      if ($_set) {
         $title = $href;
      }
   }

   #-- for case-insensitivines
   $href_i = EWIKI_CASE_INSENSITIVE ? strtolower($href) : ($href);
   $href_i = str_replace(' ','_',$href_i); // Edit 'Nov09: replace spaces with underscores

   #-- injected URLs
   if (isset($ewiki_links[$href_i]) && !is_array($ewiki_links[$href_i]) && strpos($inj_url = $ewiki_links[$href_i], "://")) {
      if ($href==$title) { $href = $inj_url; }
   }
   $states["title"] = &$title;

   #-- interwiki links
   if (strpos($href, ":") && ($uu = ewiki_interwiki($href, $type, $states))) {
      $href = $uu;
      $str = "<a href=\"$href$href2\">$title</a>";
   }
   #-- action:WikiLinks
   elseif (isset($ewiki_plugins["action"][$a=strtolower(strtok($href, ":"))])) {
      $type = array($a, "action", "wikipage");
      $str = '<a href="' . ewiki_script($a, strtok("\000")) . '">' . $title . '</a>';
   }
   #-- page anchor definitions, if ($href[0]=="#")
   elseif (@$states["define"]) {
      $type = array("anchor");
      if ($title==$href) { 
         $title = "";   // was "&nbsp;" before, but that's not required
      }
      $str = '<a name="' . htmlentities(ltrim($href, "#")) . '">' . ltrim($title, "#") . '</a>';
   }
   #-- inner page anchor jumps
   elseif (strlen($href2) && ($href==$ewiki_id) || ($href[0]=="#") && ($href2=&$href)) {
      $type = array("jump");
      $str = '<a href="' . htmlentities($href2) . '">' . $title . '</a>';
   }
   #-- ordinary internal WikiLinks
   elseif (($ewiki_links === true) || @$ewiki_links[$href_i]) {
      if (!$states["brackets"]) return $ii[0]; // BW Rox hack by lupochen: This prevents CamelCaseLinks without brackets from working
      $href = str_replace(' ','_',$href); // Edit 'Nov09: replace spaces with underscores
      $type = array("wikipage");
      $str = '<a href="' . ewiki_script("", $href) . htmlentities($href2)
           . '">' . $title . '</a>';
   }
   #-- guess for mail@addresses, convert to URI if
   elseif (strpos($href, "@") && !strpos($href, ":")) {
      $type = array("email");
      $href = "mailto:" . $href;
   }
   #-- not found fallback
   else {
      $str = "";
      if (!$states["brackets"]) return $ii[0]; // BW Rox hack by lupochen: This prevents CamelCaseLinks without brackets from working
      #-- a plugin may take care
      if ($pf_a = @$ewiki_plugins["link_notfound"]) {
         foreach ($pf_a as $pf) {
            if ($str = $pf($title, $href, $href2, $type)) {
               break;
         }  }
      }
      #-- (QuestionMarkLink to edit/ action)
      if (!$str) {
         $type = array("notfound");
         $t = $ewiki_config["qmark_links"];
         $str = ewiki_script(isset($t[4]) ? "edit" : "", $href);
         if (strlen($t) >= 3) {
            $str = ($t[0] ? "<a href=\"$str\">$t[0]</a>" :'')
                 . ($t[1] ? "<$t[1]>$title</$t[1]>" : $title)
                 . ($t[2] ? "<a href=\"$str\">$t[2]</a>" :'');
         } else {
            $str = "<a href=\"$str\">" . $title . "</a>";
            if ($t<0) { $str .= "?"; }
         }
         $str = '<span class="NotFound">' . $title . '</span>';
      }
   }

   #-- convert standard and internal:// URLs
   $is_url = eregi('^('.implode('|', $ewiki_config["idf"]["url"]).')', $href);
   $is_internal = 0;
   //
   if (!$is_url && ($ewiki_links[$href_i]["flags"] & EWIKI_DB_F_BINARY)) {
      $is_url = 1;
      $is_internal = 1;
   }
   if ($is_url) {
      $type[-2] = "url";
      $type[-1] = strtok($href, ":");

      #-- [http://url titles]
      if (strpos($href, " ") && ($title == $href)) {
         $href = strtok($href, " ");
         $title = strtok("\377");
      }

      #-- URL plugins
      if ($pf_a = $ewiki_plugins["link_url"]) foreach ($pf_a as $pf) {
         if ($str = $pf($href, $title, $status)) { break 2; }
      }
      $meta = @$ewiki_links[$href];

      #-- check for image files
      $ext = substr($href, strrpos($href,"."));
      $nocache = strpos($ext, "no");
      $ext = strtok($ext, "?&#");
      $obj = in_array($ext, $ewiki_config["idf"]["obj"]);
      $img = (strncmp(strtolower($href), "data:image/", 11) == 0) && ($nocache=1)
             || $obj || in_array($ext, $ewiki_config["idf"]["img"]);

      #-- internal:// references (binary files)
      $id = $href; 
      if (EWIKI_SCRIPT_BINARY && ((strpos($href, EWIKI_IDF_INTERNAL)===0)  ||
          EWIKI_IMAGE_MAXSIZE && EWIKI_CACHE_IMAGES && $img && !$nocache) ||
          $is_internal )
      {
         $type = array("binary");
         $href = ewiki_script_binary("", $href);
      }

      #-- output html reference
      if (!$img || $force_noimg || !$states["brackets"] || (strpos($href, EWIKI_IDF_INTERNAL) === 0)) {
//@FIX: #add1   || $href2  (breaks #.jpeg hack, but had a purpose?)
         $str = '<a href="' . $href /*#($href2)*/ . '">' . $title . '</a>';
      }
      #-- img tag
      else {
         $type = array("image");
         if (is_string($meta)) {
            $meta = unserialize($meta);
         }
         $str = ewiki_link_img($href, $id, $title, $meta, $spaces_l+2*$spaces_r, $obj, $states);
      }
   }

   #-- icon/transform plugins
   ksort($type);
   if ($pf_a = @$ewiki_plugins["link_final"]) {
      foreach ($pf_a as $pf) { $pf($str, $type, $href, $title, $states); }
   }
   if (isset($states["xhtml"]) && $states["xhtml"]) {
      foreach ($states["xhtml"] as $attr=>$val) {
         $str = str_replace("<a ", "<a $attr=\"$val\" ", $str);
      }
   }

   return($str);
}


/*
   assembles an <img> tag
*/
function ewiki_link_img($href, $id, $title, $meta, $spaces, $obj, $states) {

   #-- size of cached image
   $x = $meta["width"];
   $y = $meta["height"];

   #-- width/height given in url
   if ($p = strpos($id, '?')) {
      $id = str_replace("&amp;", "&", substr($id, $p+1));
      parse_str($id, $meta);
      if ($uu = $meta["x"].$meta["width"]) {
         $x = $uu;
      }
      if ($uu = $meta["y"].$meta["height"]) {
         $y = $uu;
      }
      if ($scale = $meta["r"] . $meta["scale"]) {
         if ($p = strpos($scale, "%")) {
            $scale = strpos($scale, 0, $p) / 100;
         }
         $x *= $scale;
         $y *= $scale;
      }
   }

   #-- alignment
   $align = array('', ' align="right"', ' align="left"', ' align="center"');
   $align = $align[$spaces];
   $size = ($x && $y ? " width=\"$x\" height=\"$y\"" : "");
   
   #-- remove annoyances
   if ($href==$title) {
      $title = "";
   }

   #-- do
   return
     ($obj ? '<embed' : '<img')
     . ' src="' . $href . '"'
     . ' alt="' . htmlentities($title) . '"'
     . (@$states["titled"] ? ' title="' . htmlentities($title) . '"' : '')
     . $size . $align
     . ($obj ? "></embed>" : " />");
   # htmlentities($title)
}


function ewiki_stripentities(&$str) {
   static $un = array("&lt;"=>"<", "&gt;"=>">", "&amp;"=>"&");
   $str = strtr($str, $un);
}


/*
   Returns URL if it encounters an InterWiki:Link or workalike.
*/
function ewiki_interwiki(&$href, &$type, &$s) {
   global $ewiki_config, $ewiki_plugins;

   $l = strpos($href, ":");
   if ($l and (strpos($href,"//") != $l+1) and ($p1 = strtok($href, ":"))) {
      $page = strtok("\000");

      if (($p2 = ewiki_array($ewiki_config["interwiki"], $p1)) !== NULL) {
         $p1 = $p2;
         $type = array("interwiki", $uu);
         while ($p1_alias = $ewiki_config["interwiki"][$p1]) {
             $type[] = $p1;
             $p1 = $p1_alias;
         }
         if (!strpos($p1, "%s")) {
             $p1 .= "%s";
         }
         $href = str_replace("%s", $page, $p1);
         return($href);
      }
      elseif ($pf = $ewiki_plugins["intermap"][$p1]) {
         return($pf($p1, $page));
      }
      elseif ($pf_a = $ewiki_plugins["interxhtml"]) {
         foreach($pf_a as $pf) {
            $pf($p1, $page, $s);
         }
         $href = $page;
      }
   }
}


/* 
   implements FeatureWiki:InterMapWalking
*/
function ewiki_intermap_walking($id, &$data, $action) {
   if (empty($data["version"]) && ($href = ewiki_interwiki($id, $uu, $uu))) {
      header("Location: $href$sid");
      return("<a href=\"$href\">$href</a>");
   }
}



function ewiki_link($pagename, $title="") {
   if (!($url = ewiki_interwiki($pagename, $uu, $uu))) {
      $url = ewiki_script("", $pagename);
   }
   if (!$title) { $title = $pagename; }
   return("<a href=\"$url\">".htmlentities($title)."</a>");
}



# =========================================================================



#####    ##  ##   ##    ##    #####   ##  ##
######   ##  ###  ##   ####   ######  ##  ##
##  ##   ##  ###  ##  ######  ##  ##  ##  ##
#####    ##  #### ##  ##  ##  ######  ######
#####    ##  #######  ######  ####     ####
##  ###  ##  ## ####  ######  #####     ##
##  ###  ##  ##  ###  ##  ##  ## ###    ##
######   ##  ##  ###  ##  ##  ##  ##    ##
######   ##  ##   ##  ##  ##  ##  ##    ##




/*  fetch & store
*/
function ewiki_binary($break=0) {

   global $ewiki_plugins;

   #-- reject calls
   if (!strlen($id = @$_REQUEST[EWIKI_UP_BINARY]) || !EWIKI_IDF_INTERNAL) {
      return(false);
   }
   if (headers_sent()) die("ewiki-binary configuration error");

   #-- upload requests
   $upload_file = @$_FILES[EWIKI_UP_UPLOAD];
   $add_meta = array();
   if ($orig_name = @$upload_file["name"]) {
      $add_meta["Content-Location"] = urlencode($orig_name);
      $add_meta["Content-Disposition"] = 'inline; filename="'.urlencode(basename("remote://$orig_name")).'"';
   }

   #-- what are we doing here?
   if (($id == EWIKI_IDF_INTERNAL) && ($upload_file)) { 
      $do = "upload";
   }
   else {
      $data = ewiki_db::GET($id);
      $flags = @$data["flags"];
      if (EWIKI_DB_F_BINARY == ($flags & EWIKI_DB_F_TYPE)) { 
         $do = "get";
      }
      elseif (empty($data["version"]) and EWIKI_CACHE_IMAGES) {
         $do = "cache";
      }
      else { 
         $do = "nop";
      }
   }

   #-- auth only happens when enforced with _PROTECTED_MODE_XXL setting
   #   (authentication for inline images in violation of the WWW spirit)
   if ((EWIKI_PROTECTED_MODE>=5) && !ewiki_auth($id, $data, "binary-{$do}")) {
      return($_REQUEST["id"]="view/BinaryPermissionError");
   }

   #-- upload an image
   if ($do == "upload"){

      $id = ewiki_binary_save_image($upload_file["tmp_name"], "", $return=0, $add_meta);
      @unlink($upload_file["tmp_name"]);
      ($title = trim($orig_name, "/")) && ($title = preg_replace("/[^-._\w\d]+/", "_", substr(substr($orig_name, strrpos($title, "/")), 0, 20)))
      && ($title = ' \\"'.$title.'\\"') || ($title="");

      if ($id) {
         echo<<<EOF
<html><head><title>File/Picture Upload</title><script language="JavaScript" type="text/javascript"><!--
 opener.document.forms["ewiki"].elements["content"].value += "\\nUPLOADED PICTURE: [$id$title]\\n";
 window.setTimeout("self.close()", 5000);
//--></script></head><body bgcolor="#440707" text="#FFFFFF">Your uploaded file was saved as<br /><big><b>
[$id]
</b></big>.<br /><br /><noscript>Please copy this &uarr; into the text input box:<br />select/mark it with your mouse, press [Ctrl]+[Insert], go back<br />to the previous screen and paste it into the textbox by pressing<br />[Shift]+[Insert] inside there.</noscript></body></html>
EOF;
      }
   }

   #-- request for contents from the db
   elseif ($do == "get") {

      #-- send http_headers from meta
      if (is_array($data["meta"])) {
         foreach ($data["meta"] as $hdr=>$val) {
            if (($hdr[0] >= "A") && ($hdr[0] <= "Z")) {
               header("$hdr: $val");
            }
         }
      }

      #-- fetch from binary store
      if ($pf_a = $ewiki_plugins["binary_get"]) {
         foreach ($pf_a as $pf) { $pf($id, $data["meta"]); }
      }

      #-- else fpassthru
      echo $data["content"];
   }

   #-- fetch & cache requested URL,
   elseif ($do == "cache") {

      #-- check for standard protocol names, to prevent us from serving
      #   evil requests for '/etc/passwd.jpeg' or '../.htaccess.gif'
      if (preg_match('@^\w?(http|ftp|https|ftps|sftp)\w?://@', $id)) {

         #-- generate local copy
         $filename = tempnam(EWIKI_TMP, "ewiki.local.temp.");
            if(!copy($id, $filename)){
              ewiki_log("ewiki_binary: error copying $id to $filename", 0);
            } else {
            $add_meta = array(
               "Content-Location" => urlencode($id),
               "Content-Disposition" => 'inline; filename="'.urlencode(basename($id)).'"',
               'PageType' => 'CachedImage'
            );

            $result = ewiki_binary_save_image($filename, $id, "RETURN", $add_meta);
         }
      }      

      #-- deliver
      if ($result && !$break) {
         ewiki_binary($break=1);
      }
      #-- mark URL as unavailable
      else {
         $data = array(
            "id" => $id,
            "version" => 1, 
            "flags" => EWIKI_DB_F_DISABLED,
            "lastmodified" => time(),
            "created" => time(),
            "author" => ewiki_author("ewiki_binary_cache"),
            "content" => "",
            "meta" => array("Status"=>"404 Absent"),
         );
         ewiki_db::WRITE($data);
         header("Location: $id");
         ewiki_log("imgcache: did not find '$id', and marked it now in database as DISABLED", 2);
      }
      
   }

   #-- "we don't sell this!"
   else {
      if (strpos($id, EWIKI_IDF_INTERNAL) === false) {
         header("Status: 301 Located SomeWhere Else");
         header("Location: $id");
      }
      else {
         header("Status: 404 Absent");
         header("X-Broken-URI: $id");
      }
   }

   // you should not remove this one, it is really a good idea to use it!
   die();
}






function ewiki_binary_save_image($filename, $id="", $return=0,
$add_meta=array(), $accept_all=EWIKI_ACCEPT_BINARY, $care_for_images=1)
{
   global $ewiki_plugins;

   #-- break on empty files
   if (!filesize($filename)) {
      return(false);
   }

   #-- check for image type and size
   $mime_types = array(
      "application/octet-stream",
      "image/gif",
      "image/jpeg",
      "image/png",
      "application/x-shockwave-flash"
   );
   $ext_types = array(
      "bin", "gif", "jpeg", "png", "swf"
   );
   list($width, $height, $mime_i, $uu) = getimagesize($filename);
   (!$mime_i) && ($mime_i=0) || ($mime = $mime_types[$mime_i]);

   #-- images expected
   if ($care_for_images) {

      #-- mime type
      if (!$mime_i && !$accept_all || !filesize($filename)) {
         ewiki_die(ewiki_t("BIN_NOIMG"), $return);
         return;
      }

      #-- resize image
      if ((strpos($mime,"image/")!==false)
      && (EWIKI_IMAGE_RESIZE)) {   // filesize() check now in individual resize plugins
         if ($pf_a = $ewiki_plugins["image_resize"]) foreach ($pf_a as $pf) {
            $pf($filename, $mime, $return);
            clearstatcache();
         }
      }

      #-- reject image if too large
      if(filesize($filename) > EWIKI_IMAGE_MAXSIZE) {
         ewiki_die(ewiki_t("BIN_IMGTOOLARGE"), $return);
         return;
      }

      #-- again check mime type and image sizes
      list($width, $height, $mime_i, $uu) = getimagesize($filename);
      (!$mime_i) && ($mime_i=0) || ($mime = $mime_types[$mime_i]);

   }
   ($ext = $ext_types[$mime_i]) or ($ext = $ext_types[0]);

   #-- binary files
   if ((!$mime_i) && ($pf = $ewiki_plugins["mime_magic"][0])) {
      if ($tmp = $pf($content)) {
         $mime = $tmp;
      }
   }
   if (!strlen($mime)) {
      $mime = $mime_types[0];
   }

   #-- store size of binary file
   $add_meta["size"] = filesize($filename);
   $content = "";

   #-- handler for (large/) binary content?
   if ($pf_a = $ewiki_plugins["binary_store"]) {
      foreach ($pf_a as $pf) {
         $pf($filename, $id, $add_meta, $ext);
      }
   }

   #-- read file into memory (2MB), to store it into the database
   if ($filename) {
      $f = fopen($filename, "rb");
      $content = fread($f, 1<<21);
      fclose($f);
   }

   #-- generate db file name
   if (empty($id)) {
      $md5sum = md5($content);
      $id = EWIKI_IDF_INTERNAL . $md5sum . ".$ext";
      ewiki_log("generated md5sum '$md5sum' from file content");
   }

   #-- prepare meta data
   $meta = array(
      "class" => $mime_i ? "image" : "file",
      "Content-Type" => $mime,
      "Pragma" => "cache",
   ) + (array)$add_meta;
   if ($mime_i) {
      $meta["width"] = $width;
      $meta["height"] = $height;
   }

   #-- database entry
   $data = array(
      "id" => $id,
      "version" => "1", 
      "author" => ewiki_author(),
      "flags" => EWIKI_DB_F_BINARY | EWIKI_DB_F_READONLY,
      "created" => time(),
      "lastmodified" => time(),
      "meta" => &$meta,
      "content" => &$content,
   );
   
   #-- write if not exist
   $exists = ewiki_db::FIND(array($id));
   if (! $exists[$id] ) {
      $result = ewiki_db::WRITE($data);
      ewiki_log("saving of '$id': " . ($result ? "ok" : "error"));
   }
   else {
      ewiki_log("binary_save_image: '$id' was already in the database", 2);
   }

   return($id);
}




# =========================================================================


####     ####  ####   ########     ########
#####   #####  ####  ##########   ##########
###### ######  ####  ####   ###   ####    ###
#############        ####        ####
#############  ####   ########   ####
#### ### ####  ####    ########  ####
####  #  ####  ####        ####  ####
####     ####  ####  ###   ####  ####    ###
####     ####  ####  #########    ##########
####     ####  ####   #######      ########



/* yes! it is not necessary to annoy people with country flags, if
   HTTP already provides means to determine the prefered language!
*/
function ewiki_localization() {

   global $ewiki_t;

   $deflangs = ','.@$_ENV["LANGUAGE"] . ','.@$_ENV["LANG"]
             . ",".EWIKI_DEFAULT_LANG . ",en,C";

   foreach (explode(",", @$_SERVER["HTTP_ACCEPT_LANGUAGE"].$deflangs) as $l) {

      $l = strtok($l, ";");
      $l = strtok($l, "-"); $l = strtok($l, "_"); $l = strtok($l, ".");

      if ($l = trim($l)) {
         $ewiki_t["languages"][] = strtolower($l);
      }
   }
   
   $ewiki_t["languages"] = array_unique($ewiki_t["languages"]);
}




/* poor mans gettext, $repl is an array of string replacements to get
   applied to the fetched text chunk,
   "$const" is either an entry from $ewiki_t[] or a larger text block
   containing _{text} replacement braces of the form "_{...}"
*/
function ewiki_t($const, $repl=array(), $pref_langs=array()) {

   global $ewiki_t;

   #-- use default language wishes
   if (empty($pref_langs)) {
      $pref_langs = $ewiki_t["languages"];
   }

   #-- large text snippet replacing
   if (strpos($const, "_{") !== false) {
      while ( (($l=strpos($const,"_{")) || ($l===0)) && ($r=strpos($const,"}",$l)) ) {
         $const = substr($const, 0, $l)
                . ewiki_t(substr($const, $l+2, $r-$l-2))
                . substr($const,$r+1);
      }
   }

   #-- just one string
   else foreach ($pref_langs as $l) {

      if (is_string($r = @$ewiki_t[$l][$const]) || ($r = @$ewiki_t[$l][strtoupper($const)])) {

         foreach ($repl as $key=>$value) {
            if ($key[0] != '$') {
               $key = '$'.$key;
            }
            $r = str_replace($key, $value, $r);
         }
         return($r);

      }
   }

   return($const);
}




/* takes all ISO-8859-1 characters into account
   but won't work with all databases
*/
function ewiki_lowercase($s) {
   $len = strlen($s);
   for ($i=0; $i<$len; $i++) {
      if (ord($s[$i]) >= 192) {
         $s[$i] = chr(ord($s[$i]) | 0x20);
      }
   }
   return(strtolower($s));
}




function ewiki_log($msg, $error_type=3) {

   if ((EWIKI_LOGLEVEL >= 0) && ($error_type <= EWIKI_LOGLEVEL)) {

      $msg = time() . " - " .
             $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"] . " - " .
             $_SERVER["REQUEST_METHOD"] . " " . $_SERVER["REQUEST_URI"] . " - " .
             strtr($msg, "\n\r\000\377\t\f", "\r\r\r\r\t\f") . "\n";
      error_log($msg, 3, EWIKI_LOGFILE);
   }
}




function ewiki_die($msg, $return=0) {
   ewiki_log($msg, 1);
   if ($return) {
      return($GLOBALS["ewiki_error"] = $msg);
   }
   else {
      die($msg);
   }
}



function ewiki_array_hash(&$a) {
   return(count($a) . ":" . implode(":", array_keys(array_slice($a, 0, 3))));
}



/* provides an case-insensitive in_array replacement to search a page name
   in a list of others;
   the supplied $array WILL be lowercased afterwards, unless $dn was set
*/
function ewiki_in_array($value, &$array, $dn=0, $ci=EWIKI_CASE_INSENSITIVE) {

   static $as = array();

   #-- work around pass-by-reference
   if ($dn && $ci) {   $dest = array();   }
              else {   $dest = &$array;   }

   #-- make everything lowercase
   if ($ci) {
      $value = strtolower($value);
      if (empty($as[ewiki_array_hash($array)])) {  // prevent working on the
         foreach ($array as $i=>$v) {              // same array multiple times
            $dest[$i] = strtolower($v);
         }
         $as[ewiki_array_hash($dest)] = 1;
      }
   }

   #-- search in values
   return(in_array($value, $dest));
}



/* case-insensitively retrieves an entry from an $array,
   or returns the given $array lowercased if $key was obmitted
*/
function ewiki_array($array, $key=false, $am=1, $ci=EWIKI_CASE_INSENSITIVE) {

   #-- make everything lowercase
   if ($ci) {
      $key = strtolower($key);

      $r = array();
      foreach ($array as $i=>$v) {
         $i = strtolower($i);
         if (!$am || empty($r[$i])) {
            $r[$i] = $v;
         }
         else {
            $r[$i] .= $v;    //RET: doubling for images`meta won't happen
         }            // but should be "+" here for integers
      }
      $array = &$r;
   }

   #-- search in values
   if ($key) {
      return(@$array[$key]);
   }
   else {
      return($array);
   }
}





/*
   generates {author} string field for page database entry updates
*/
function ewiki_author($defstr="") {

   $author = @$GLOBALS["ewiki_author"];
   ($ip = &$_SERVER["REMOTE_ADDR"]) or ($ip = "127.0.0.0");
   ($port = $_SERVER["REMOTE_PORT"]) or ($port = "null");

   #-- this call may be very slow (~20 sec)
   if (EWIKI_RESOLVE_DNS) {
      $hostname = gethostbyaddr($ip);
   }
   $remote = (($ip != $hostname) ? $hostname . " " : "")
           . $ip . ":" . $port;

   (empty($author)) && (
      ($author = $defstr) ||
      ($author = $_SERVER["HTTP_FROM"]) ||    // RFC2068 sect 14.22
      ($author = $_SERVER["PHP_AUTH_USER"])
   );

   (empty($author))
      && ($author = $remote)
      || ($author = addslashes($author) . " (" . $remote . ")" );

   return($author);
}

/*
   decodes {author} field for display in pages
*/
function ewiki_author_html($orig, $tail=0) {
   $str = strtok($orig, " (|,;/[{<+");
   $tail = $tail ? " " . strtok("\000") : "";
   #-- only IP
   if (strpos($str, ":")) {
      return('<a href="'. strtok($str, ":") . "\">$orig</a>");
   }
   #-- mail address
   elseif (strpos($str, "@")) {
      // email_protect_*() now takes care of plugin pages
      return("<a href=\"mailto:$str\">$str</a>$tail");
   }
   #-- host name
   elseif (strpos($str, ".") < strrpos($str, ".")) {
      return("<a href=\"http://$str/\">$str</a>$tail");
   }
   #-- eventually an AuthorName
   else {
      return('<a href="'.PVars::getObj("env")->baseuri.'members/' . $str . '">' . $str . '</a>' . $tail);
   }
   return($orig);
}









/*  Returns a value of (true) if the currently logged in user (this must
    be handled by one of the plugin backends) is authenticated to do the
    current $action, or to view the current $id page.
  - alternatively just checks current authentication $ring permission level
  - errors are returned via the global $ewiki_errmsg
*/
function ewiki_auth($id, &$data, $action, $ring=false, $request_auth=0) {

   global $ewiki_plugins, $ewiki_ring, $ewiki_author,
      $ewiki_errmsg, $ewiki_config;
   $ok = true;
   $ewiki_errmsg="";

#echo "_a($id,dat,$action,$ring,$request_auth)<br />\n";

   if (EWIKI_PROTECTED_MODE) {

      #-- set required vars
      if (!isset($ewiki_ring)) {
         $ewiki_ring = (int)EWIKI_AUTH_DEFAULT_RING;
      }
      if ($ring===false) {
         $ring = NULL;
      }
      if ($ewiki_config["create"] && ($action=="edit")) {
         $action = "create";  // used only/primarily in authentication plugins
      }

      #-- plugins to call
      $pf_login = @$ewiki_plugins["auth_query"][0];
      $pf_perm = $ewiki_plugins["auth_perm"][0];

      #-- nobody is currently logged in, so try to fetch username,
      #   the login <form> is not yet enforced
      if ($pf_login && empty($ewiki_auth_user)) {
         $pf_login($data, 0);
      }

      #-- check permission for current request (page/action/ring)
      if ($pf_perm) {

         #-- via _auth handler
         $ok = $pf_perm($id, $data, $action, $ring, $request_auth);

         #-- if it failed, we really depend on the login <form>,
         #   and then recall the _perm plugin
         if ($pf_login && (($request_auth >= 2) || !$ok && $request_auth && (empty($ewiki_auth_user) || EWIKI_AUTO_LOGIN) && empty($ewiki_errmsg))) {
//@FIXME: complicated if()  - strip empty(errmsg) ??
            $pf_login($data, $request_auth);
            $ok = $pf_perm($id, $data, $action, $ring, $request_auth=0);
         }
      }
      else {
         $ok = !isset($ring) || isset($ring) && ($ewiki_ring <= $ring);
      }

      #-- return error string
      if (!$ok && empty($ewiki_errmsg)) {
         ewiki_log("ewiki_auth: Access Denied ($action/$id, $ring/$ewiki_ring, $request_auth)");
         $ewiki_errmsg = ewiki_t("FORBIDDEN");
      }
   }

   return($ok);
}


/*
   Queries all registered ["auth_userdb"] plugins for the given
   username, and compares password to against "db" value, sets
   $ewiki_ring and returns(true) if valid.
*/
function ewiki_auth_user($username, $password) {
  global $ewiki_ring, $ewiki_errmsg, $ewiki_auth_user, $ewiki_plugins, $ewiki_author;

  if (empty($username)) {
     return(false);
  }
  if (($password[0] == "$") || (strlen($password) > 12)) {
     ewiki_log("_auth_userdb: password was transmitted in encoded form, or is just too long (login attemp for user '$username')", 2);
     return(false);
  }

  if ($pf_u = $ewiki_plugins["auth_userdb"])
  foreach ($pf_u as $pf) {

     if (function_exists($pf) && ($entry = $pf($username, $password))) {

        #-- get and compare password
        if ($entry = (array) $entry) {
           $enc_pw = $entry[0];
        }
        $success = false
                || ($enc_pw == substr($password, 0, 12))
                || ($enc_pw == md5($password))
                || ($enc_pw == crypt($password, substr($enc_pw, 0, 2)))
                || function_exists("sha1") && ($enc_pw == sha1($password));
        $success &= $enc_pw != "*";

        #-- return if it matches
        if ($success) {
           if (isset($entry[1])) { 
              $ewiki_ring = (int)($entry[1]);
           } else {
              $ewiki_ring = 2;  //(EWIKI_AUTH_DEFAULT_RING - 1);
           }
           if (empty($ewiki_author)) {
              ($ewiki_author = $entry[2]) or
              ($ewiki_author = $username);
           }
           return($success && ($ewiki_auth_user=$username));
        }
     }
  }

  if ($username || $password) {
     ewiki_log("_auth_userdb: wrong password supplied for user '$username', not verified against any userdb", 3);
     $ewiki_errmsg = "wrong username and/or password";
#     ewiki_auth($uu, $uu, $uu, $uu, 2);
  }
  return(false);
}




/*
   Returns <form> field html strings, looks up previously selected values.
   Don't use it for textareas, $value magically elects the input field type.
*/
function ewiki_form($name, $value, $label="", $_text="| |\n", $inj="") {
   global $_EWIKI, $ewiki_id;
   static $fid = 50;
   static $_sel = ' selected="selected"', $_chk = ' checked="checked"';

   #-- prepare
   $o = "";
   $_text = explode("|", $_text);
   list($name, $type, $width, $height) = explode(":", $name);
   $type = $type ? (strpos($type, "a") ? "a" : (strpos($type, "b") ? "b" : $type[0])) : "t";
   if ($inj) { $inj = " $inj"; }
   $old_value = @$_EWIKI["form"][$ewiki_id][$name];

   #-- select fields
   if ((($type=="s") || strpos($value, "|")) && ($v = explode("|", $value))) {
      $value = array();
      foreach ($v as $opt) {
         $opt = strtok($opt, "="); ($title = strtok("|")) or ($title = $opt);
         $value[$opt] = $title;
      }
   }

   #-- label, surrounding text
   $o .= "$_text[0]";
   if ($fid++ && $label) {
      $o .= "<label for=\"ff$fid\">$label</label>";
   }
   $o .= "$_text[1]";

   #-- submit (as "button")
   if (!$name || ($type=="b")) {
      if ($name) { $name = " name=\"$name\""; }
      $o .= "<input type=\"submit\" id=\"ff$fid\"$name value=\"$value\"$inj />";
   }
   #-- select
   elseif (is_array($value)) {
      $o .= "<select name=\"$name\" id=\"ff$fid\"$inj>";
      $no_val = isset($value[0]);
      foreach ($value as $val=>$title) {
         if ($no_val) { $val = $title; }
         $sel = (!$old_value && strpos($val, "!") || ($old_value==$val)) ? $_sel : "";
         $o .= '<option value="' . trim(rtrim($val, "!")) . '"' . $sel . '>'
            . trim(rtrim($title, "!")) . '</option>';
      }
      $o .= "</select>";
   }
   #-- checkbox
   elseif (($type=="c") || strpos($value, "]")) {
      if (isset($old_value)) { $value = $old_value ? "1x" : ""; }
      $sel = strpos($value, "x") ? $_chk : "";
      $o .= "<input type=\"checkbox\" id=\"ff$fid\" name=\"$name\" value=\"1\"$sel$inj />";
   }
   #-- textarea
   elseif ($type=="a") {
      if ($width && $height) { $inj .= " cols=\"$width\" rows=\"$height\""; }
      $o .= "<textarea id=\"ff$fid\" name=\"$name\"$inj>"
         . htmlentities($value) . "</textarea>";
   }
   #-- input field; text or hidden
   else {
      if ($width) { $inj .= " size=\"$width\""; }
      if (isset($old_value)) { $value = $old_value; }
      $type = ($type == "t") ? "text" : "hidden";
      $o .= "<input type=\"$type\" id=\"ff$fid\" name=\"$name\" value=\""
         . htmlentities($value) . "\"$inj />";
   }

   #-- fin
   $o .= "$_text[2]";
   return($o);
}




/*  reads all files from "./init-pages/" into the database,
    when ewiki is run for the very first time and the FrontPage
    does not yet exist in the database
*/
function ewiki_eventually_initialize(&$id, &$data, &$action) {

   #-- initialize database only if frontpage missing
   if (($id==EWIKI_PAGE_INDEX) && ($action=="edit") && empty($data["version"])) {

      ewiki_db::INIT();
      if ($dh = @opendir($path=EWIKI_INIT_PAGES)) {
         while ($filename = readdir($dh)) {
            if (preg_match('/^(['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+\w*)+/', $filename)) {
               $found = ewiki_db::FIND(array($filename));
               if (! $found[$filename]) {
                  $content = implode("", file("$path/$filename"));
                  ewiki_scan_wikiwords($content, $ewiki_links, "_STRIP_EMAIL=1");
                  $refs = "\n\n" . implode("\n", array_keys($ewiki_links)) . "\n\n";
                  $save = array(
                     "id" => "$filename",
                     "version" => "1",
                     "flags" => EWIKI_DB_F_TEXT,
                     "content" => $content,
                     "author" => ewiki_author("ewiki_initialize"),
                     "refs" => $refs,
                     "lastmodified" => filemtime("$path/$filename"),
                     "created" => filectime("$path/$filename")   // (not exact)
                  );
                  ewiki_db::WRITE($save);
               }
            }
         }
         closedir($dh);
      }
      else {
         echo "<b>ewiki error</b>: could not read from directory ". realpath($path) ."<br />\n";
      }

      #-- try to view/ that newly inserted page
      if ($data = ewiki_db::GET($id)) {
         $action = "view";
      }
   }
}




#---------------------------------------------------------------------------



########     ###    ########    ###    ########     ###     ######  ########
########     ###    ########    ###    ########     ###     ######  ########
##     ##   ## ##      ##      ## ##   ##     ##   ## ##   ##    ## ##
##     ##   ## ##      ##      ## ##   ##     ##   ## ##   ##    ## ##
##     ##  ##   ##     ##     ##   ##  ##     ##  ##   ##  ##       ##
##     ##  ##   ##     ##     ##   ##  ##     ##  ##   ##  ##       ##
##     ## ##     ##    ##    ##     ## ########  ##     ##  ######  ######
##     ## ##     ##    ##    ##     ## ########  ##     ##  ######  ######
##     ## #########    ##    ######### ##     ## #########       ## ##
##     ## #########    ##    ######### ##     ## #########       ## ##
##     ## ##     ##    ##    ##     ## ##     ## ##     ## ##    ## ##
##     ## ##     ##    ##    ##     ## ##     ## ##     ## ##    ## ##
########  ##     ##    ##    ##     ## ########  ##     ##  ######  ########
########  ##     ##    ##    ##     ## ########  ##     ##  ######  ########




#-- database API (static wrapper around backends)
class ewiki_db {


   #-- load page
   # returns database entry as array for the page whose name was given in
   # $id key, usually fetches the latest version of a page, unless a specific
   # $version was requested
   #
   function GET($id, $version=false) {
      global $ewiki_db;
      $r = $ewiki_db->GET($id, 0+$version);
      ewiki_db::expand($r);
      return($r);
   }

   
   #-- save page
   # stores the page $hash into the database, while not overwriting existing
   # entries unless $overwrite was set; returns 0 on failure, 1 if completed
   #
   function WRITE($hash, $overwrite=0) {
      global $ewiki_db;
      if (is_array($hash) && count($hash) && !defined("EWIKI_DB_LOCK")) {
         #-- settype (for flat-file databases)
         $hash["version"] += 0;
         $hash["hits"] += 0;
         $hash["lastmodified"] += 0;
         $hash["created"] += 0;
         ewiki_db::shrink($hash);
         return $ewiki_db->WRITE($hash, $overwrite);
      }
   }

   #-- search
   # returns dbquery_result object of database entries (also arrays), where
   # the one specified column matches the specified content string;
   # it is not guaranteed to only search/return the latest version of a page;
   # $field may be an array, in which case an OR-search is emulated
   #
   function SEARCH($field, $content, $ci=1, $regex=0, $mask=0x0000, $filter=0x0000) {
      global $ewiki_db;
      $ci = ($ci ? "i" : false);
      #-- multisearch (or connected)
      if (is_array($field)) {
         if (isset($field[0])) { // multiple $field names, just one $content string
            $uu = $field; $field = array();
            foreach ($uu as $f) {
               $field[$f] = $content;
            }
         }
         $r = new ewiki_dbquery_result(array($field));
         foreach ($field as $f=>$c) {
            $add = $ewiki_db->SEARCH($f, $c, $ci, $regex, $mask, $filter);
            $r->entries = array_merge($r->entries, $add->entries);
            unset($add);  // dispose, hopefully
         }
      }
      #-- single query
      else {
         $r = $ewiki_db->SEARCH($field, $content, $ci, $regex, $mask, $filter);
         ewiki_db::dbquery_result($r);
      }
      return($r);
   }

   
   #-- full page list
   # returns an dbquery_result object with __all__ pages, where each entry
   # is made up of at least the fields from the database requested with the
   # $fields array, e.g. array("flags","meta","lastmodified");
   #
   function GETALL($fields, $mask=0x0000, $filter=0x0000) {
      global $ewiki_db;
      $fields[] = "flags";
      $fields[] = "version";
      $fields = array_flip($fields);
      unset($fields["id"]);
      $fields = array_flip($fields);
      $r = $ewiki_db->GETALL($fields);
      ewiki_db::dbquery_result($r);
      return($r);
   }


   #-- check page existence
   # searches for all given page names (in $list) in the database and returns
   # an associative array with page names as keys and booleans as values;
   # (int)0 for missing pages, and for existing ones the associated value is
   # the page {flags} value or the {meta} data array (for binary entries)
   #
   function FIND($list) {
      global $ewiki_db;
      if (!count($list)) {
         return($list);
      }
      return $ewiki_db->FIND($list);
   }


   #-- page hits
   # increases the {hit} counter for the page given by $id
   #
   function HIT($id) {
      global $ewiki_db;
      return $ewiki_db->HIT($id);
   }


   #-- admin functions
   function DELETE($id, $version=false) {
      global $ewiki_db;
      if (!defined("EWIKI_DB_LOCK"))
        return $ewiki_db->DELETE($id, $version);
   }
   function INIT() {
      global $ewiki_db;
      return $ewiki_db->INIT();
   }
   
   
   #-- virtual features
   # ::CREATE() creates a new page hash (template)
   # ::UPDATE() renews meta data (except version) to allow ::WRITE()
   # ::APPEND() adds content to an existing text page
   #
   function CREATE($id, $flags=EWIKI_DB_F_TEXT, $author="") {
      $data = array(
         "id"=>$id, "version"=>1, "flags"=>$flags,
         "content"=>"", "meta"=>array(),
         "hits"=>0, "created"=>time(),
         "lastmodified"=>time(),
         "author"=>ewiki_author($author),
      );
      return($data);
   }
   function UPDATE(&$data, $author="") {
      global $ewiki_links;
      #-- regenerate backlinks entry
      ewiki_scan_wikiwords($data["content"], $ewiki_links, "_STRIP_EMAIL=1");
      $data["refs"] = "\n\n".implode("\n", array_keys($ewiki_links))."\n\n";
      #-- update meta info
      $data["lastmodified"] = time();
      $data["author"] = ewiki_author($author);
      $data["meta"]["user-agent"] = trim($_SERVER["HTTP_USER_AGENT"]);
   }
   function APPEND($id, $text, $textonly=1) {
      ($data = ewiki_db::GET($id))
      or ($data = ewiki_db::CREATE($id));
      if (!strlen(trim($text)) or $textonly && (($data["flags"]&EWIKI_DB_F_TYPE) != EWIKI_DB_F_TEXT)) {
         return;
      }
      $data["content"] .= $text;
      ewiki_db::UPDATE($data);
      $data["version"]++;
      return ewiki_db::WRITE($data);
   }


   #-- helper code
   function expand(&$r) {
      if (isset($r["meta"]) && is_string($r["meta"]) && strlen($r["meta"])) {
         $r["meta"] = unserialize($r["meta"]);
      }
   }
   function shrink(&$r) {
      if (isset($r["meta"]) && is_array($r["meta"])) {
         $r["meta"] = serialize($r["meta"]);
      }
   }
   function dbquery_result(&$r) {
      if (is_array($r)) {
         $z = new ewiki_dbquery_result(array_keys($args));
         foreach ($r as $id=>$row) {
            $z->add($row);
         }
         $r = $z;
      }
   }

} // end of class



#-- returned for SEARCH and GETALL queries, as those operations are
#   otherwise too memory exhaustive
class ewiki_dbquery_result {

   var $keys = array();
   var $entries = array();
   var $buffer = EWIKI_DBQUERY_BUFFER;
   var $size = 0;

   function ewiki_dbquery_result($keys) {
      $keys = array_merge((array)$keys, array(-50=>"id", "version", "flags"));
      $this->keys = array_unique($keys);
   }

   function add($row) {
      if (is_array($row)) {
         if ($this->buffer) {
            $this->size += strlen(serialize($row));
            $this->buffer = $this->size <= EWIKI_DBQUERY_BUFFER;
            ewiki_db::expand($row);
         }
         else {
            $row = $row["id"];
         }
      }
      $this->entries[] = $row;
   }

   function get($all=0, $flags=0x0000, $type=0) {
      $row = array();

      $prot_hide = ($flags&0x0020) && EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING;
      $flag_hide = ($flags&0x0003);
      do {
         if (count($this->entries)) {

            #-- fetch very first entry from $entries list
            $r = array_shift($this->entries);

            #-- finish if buffered entry
            if (is_array($r) && !$all) {
               $row = $r;
            }
            #-- else refetch complete entry from database
            else {
               if (is_array($r)) {
                  $r = $r["id"];
               }
               $r = ewiki_db::GET($r);
               if (!$all) {
                  foreach ($this->keys as $key) {
                     $row[$key] = $r[$key];
                  }
               } else { 
                  $row = $r;
               }
            }
            unset($r);
         }
         else { 
            return(NULL);  // no more entries
         }

         #-- expand {meta} field
         if (is_array($row) && is_string(@$row["meta"])) {
            $row["meta"] = unserialize($row["meta"]);
         }

         #-- drop unwanted results
         if ($prot_hide && !ewiki_auth($row["id"], $row, 'view')
         || ($flag_hide && ($row["flags"] & (EWIKI_DB_F_HIDDEN|EWIKI_DB_F_DISABLED)))
         || ($type) && (($row["flags"] & EWIKI_DB_F_TYPE) != $type)) {
            $row = array();
         }
      } while (empty($row) && ($prot_hide || $flag_hide));

      return($row);
   }

   function count() {
      return(count($this->entries));
   }
}



#-- obsolete compatibility wrapper
function ewiki_database($action, $args, $sw1=0, $sw2=0, $pf=false) {
   switch ($action) {
      case "GET":
         return ewiki_db::GET($args["id"], @$args["version"]);
      case "WRITE":
         return ewiki_db::WRITE($args, 0);
      case "OVERWRITE":
         return ewiki_db::WRITE($args, 1);
      case "FIND":
         return ewiki_db::FIND($args);
      case "GETALL":
         return ewiki_db::GETALL($args);
      case "SEARCH":
         return ewiki_db::SEARCH(implode("",array_keys($args)), implode("",$args));
      case "HIT":
         return ewiki_db::HIT($args["id"]);
      case "DELETE":
         return ewiki_db::DELETE($args["id"], $args["version"]);
      case "INIT":
         return ewiki_db::INIT();
   }
   echo "error: unknown database call '$action'<br>\n";
   return false;
}



#-- MySQL database backend (default, but will be teared out soon)
#   Note: this is of course an abuse of the relational database scheme,
#   but necessary for real db independence and abstraction
class ewiki_database_mysql {

   function ewiki_database_mysql() {
      $this->table = EWIKI_DB_TABLE_NAME;
   }


   function GET($id, $version=false) {
      $id = mysql_escape_string($id);
      if ($version) {
         $version = "AND (version=$version)";
      } else  { 
         $version="";
      }
      $result = mysql_query("SELECT *, pagename as id FROM {$this->table}
          WHERE (pagename='$id') $version  ORDER BY version DESC  LIMIT 1"
      );
      echo mysql_error();
      if ($result && ($r = mysql_fetch_array($result, MYSQL_ASSOC))) {
         unset($r["pagename"]);
         return($r);
      }
   }

   
   function HIT($id) {
      $id = mysql_escape_string($id);
      mysql_query("UPDATE {$this->table} SET hits=(hits+1) WHERE pagename='$id'");
   }


   function WRITE($hash, $overwrite=0) {

      $COMMAND = $overwrite ? "REPLACE" : "INSERT";
      $sql1 = $sql2 = "";
      $hash["pagename"] = $hash["id"];
      unset($hash["id"]);
      foreach ($hash as $index=>$value) {
         if (is_int($index)) {
            continue;
         }
         $a = ($sql1 ? ', ' : '');
         $sql1 .= $a . $index;
         // HACK BY LUPOCHEN Sept/2009: without the utf8_encode() we run into problems with special characters
         $sql2 .= $a . "'" . mysql_escape_string(utf8_encode($value)) . "'";
      }

      $result = mysql_query("$COMMAND INTO {$this->table} ($sql1) VALUES ($sql2)");
      $result = ($result && mysql_affected_rows()) ?1:0;
      return($result);
   }


   function FIND($list) {
      $r = array();
      $sql = "";
      foreach (array_values($list) as $id) {
         if (strlen($id)) {
            $r[$id] = 0;
            $sql .= ($sql ? " OR " : "") .
                 "(pagename='" . mysql_escape_string($id) . "')";
         }
      }
      $result = mysql_query("SELECT pagename AS id, meta, flags FROM {$this->table} WHERE $sql");
      if ($result) {
         while ($row = mysql_fetch_array($result)) {
            $id = $row["id"];
            if (strlen($row["meta"])) {
               $r[$id] = unserialize($row["meta"]);
               $r[$id]["flags"] = $row["flags"];
            } else {
               $r[$id] = $row["flags"];
            }
         }
      }
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $fields = implode(", ", $fields);
      $f_sql = $mask ? "WHERE ((flags & $mask) = $filter)" : "";
      $result = mysql_query("SELECT pagename AS id, $fields FROM
          {$this->table} $f_sql GROUP BY id, version DESC"
      );
      $r = new ewiki_dbquery_result($fields);
      $last = "";
      if ($result) while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
         $drop = EWIKI_CASE_INSENSITIVE ? strtolower($row["id"]) : $row["id"];
         if (($last != $drop) && ($last = $drop)) {
            $r->add($row);
         }
      }
      return($r);
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {

      $sql_fields = ", $field";
      if ($field == "id") {
         $field = "pagename";
         $sql_fields = "";
      }
      $content = mysql_escape_string($content);
      if ($mask) {
         $sql_flags = "AND ((flags & $mask) = $filter)";
      }
      if ($regex) {
         $sql_strsearch = "($field REGEXP '$content')";
      }
      elseif ($ci) {
         $sql_strsearch = "LOCATE('".strtolower($content)."', LCASE($field))";
      }
      else {
         $sql_strsearch = "LOCATE('$content', $field)";
      }
      
      $result = mysql_query(
       "SELECT pagename AS id, version, flags  $sql_fields
          FROM {$this->table}
         WHERE $sql_strsearch $sql_flags
         GROUP BY id, version DESC
      ");

      $r = new ewiki_dbquery_result(array("id","version",$field));
      $last = "";
      if ($result) while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
         $drop = EWIKI_CASE_INSENSITIVE ? strtolower($row["id"]) : $row["id"];
         if (($last != $drop) && ($last = $drop)) {
            $r->add($row);
         }
      }
      return($r);
   }


   function DELETE($id, $version) {
      $id = mysql_escape_string($id);
      mysql_query("DELETE FROM {$this->table} WHERE pagename='$id' AND version=$version");
   }


   function INIT() {
      mysql_query("CREATE TABLE {$this->table}
         (pagename VARCHAR(160) NOT NULL,
         version INTEGER UNSIGNED NOT NULL DEFAULT 0,
         flags INTEGER UNSIGNED DEFAULT 0,
         content MEDIUMTEXT,
         author VARCHAR(100) DEFAULT 'ewiki',
         created INTEGER UNSIGNED DEFAULT ".time().",
         lastmodified INTEGER UNSIGNED DEFAULT 0,
         refs MEDIUMTEXT,
         meta MEDIUMTEXT,
         hits INTEGER UNSIGNED DEFAULT 0,
         PRIMARY KEY id (pagename, version) )
      ");
      echo mysql_error();
   }


} // end of class ewiki_database_mysql







?>
