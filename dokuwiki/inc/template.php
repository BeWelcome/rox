<?php
/**
 * DokuWiki template functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * Access a template file
 *
 * Returns the path to the given file inside the current template, uses
 * default template if the custom version doesn't exist.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param string $file
 * @return string
 */
function template($file) {
    global $conf;

    if(@is_readable(DOKU_INC.'lib/tpl/'.$conf['template'].'/'.$file))
        return DOKU_INC.'lib/tpl/'.$conf['template'].'/'.$file;

    return DOKU_INC.'lib/tpl/dokuwiki/'.$file;
}

/**
 * Convenience function to access template dir from local FS
 *
 * This replaces the deprecated DOKU_TPLINC constant
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param string $tpl The template to use, default to current one
 * @return string
 */
function tpl_incdir($tpl='') {
    global $conf;
    if(!$tpl) $tpl = $conf['template'];
    return DOKU_INC.'lib/tpl/'.$tpl.'/';
}

/**
 * Convenience function to access template dir from web
 *
 * This replaces the deprecated DOKU_TPL constant
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param string $tpl The template to use, default to current one
 * @return string
 */
function tpl_basedir($tpl='') {
    global $conf;
    if(!$tpl) $tpl = $conf['template'];
    return DOKU_BASE.'lib/tpl/'.$tpl.'/';
}

/**
 * Print the content
 *
 * This function is used for printing all the usual content
 * (defined by the global $ACT var) by calling the appropriate
 * outputfunction(s) from html.php
 *
 * Everything that doesn't use the main template file isn't
 * handled by this function. ACL stuff is not done here either.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @triggers TPL_ACT_RENDER
 * @triggers TPL_CONTENT_DISPLAY
 * @param bool $prependTOC should the TOC be displayed here?
 * @return bool true if any output
 */
function tpl_content($prependTOC = true) {
    global $ACT;
    global $INFO;
    $INFO['prependTOC'] = $prependTOC;

    ob_start();
    trigger_event('TPL_ACT_RENDER', $ACT, 'tpl_content_core');
    $html_output = ob_get_clean();
    trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');

    return !empty($html_output);
}

/**
 * Default Action of TPL_ACT_RENDER
 *
 * @return bool
 */
function tpl_content_core() {
    global $ACT;
    global $TEXT;
    global $PRE;
    global $SUF;
    global $SUM;
    global $IDX;
    global $INPUT;

    switch($ACT) {
        case 'show':
            html_show();
            break;
        /** @noinspection PhpMissingBreakStatementInspection */
        case 'locked':
            html_locked();
        case 'edit':
        case 'recover':
            html_edit();
            break;
        case 'preview':
            html_edit();
            html_show($TEXT);
            break;
        case 'draft':
            html_draft();
            break;
        case 'search':
            html_search();
            break;
        case 'revisions':
            html_revisions($INPUT->int('first'));
            break;
        case 'diff':
            html_diff();
            break;
        case 'recent':
            $show_changes = $INPUT->str('show_changes');
            if (empty($show_changes)) {
                $show_changes = get_doku_pref('show_changes', $show_changes);
            }
            html_recent($INPUT->extract('first')->int('first'), $show_changes);
            break;
        case 'index':
            html_index($IDX); #FIXME can this be pulled from globals? is it sanitized correctly?
            break;
        case 'backlink':
            html_backlinks();
            break;
        case 'conflict':
            html_conflict(con($PRE, $TEXT, $SUF), $SUM);
            html_diff(con($PRE, $TEXT, $SUF), false);
            break;
        case 'login':
            html_login();
            break;
        case 'register':
            html_register();
            break;
        case 'resendpwd':
            html_resendpwd();
            break;
        case 'denied':
            html_denied();
            break;
        case 'profile' :
            html_updateprofile();
            break;
        case 'admin':
            tpl_admin();
            break;
        case 'subscribe':
            tpl_subscribe();
            break;
        case 'media':
            tpl_media();
            break;
        default:
            $evt = new Doku_Event('TPL_ACT_UNKNOWN', $ACT);
            if($evt->advise_before()) {
                msg("Failed to handle command: ".hsc($ACT), -1);
            }
            $evt->advise_after();
            unset($evt);
            return false;
    }
    return true;
}

/**
 * Places the TOC where the function is called
 *
 * If you use this you most probably want to call tpl_content with
 * a false argument
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param bool $return Should the TOC be returned instead to be printed?
 * @return string
 */
function tpl_toc($return = false) {
    global $TOC;
    global $ACT;
    global $ID;
    global $REV;
    global $INFO;
    global $conf;
    global $INPUT;
    $toc = array();

    if(is_array($TOC)) {
        // if a TOC was prepared in global scope, always use it
        $toc = $TOC;
    } elseif(($ACT == 'show' || substr($ACT, 0, 6) == 'export') && !$REV && $INFO['exists']) {
        // get TOC from metadata, render if neccessary
        $meta = p_get_metadata($ID, '', METADATA_RENDER_USING_CACHE);
        if(isset($meta['internal']['toc'])) {
            $tocok = $meta['internal']['toc'];
        } else {
            $tocok = true;
        }
        $toc = isset($meta['description']['tableofcontents']) ? $meta['description']['tableofcontents'] : null;
        if(!$tocok || !is_array($toc) || !$conf['tocminheads'] || count($toc) < $conf['tocminheads']) {
            $toc = array();
        }
    } elseif($ACT == 'admin') {
        // try to load admin plugin TOC
        /** @var $plugin DokuWiki_Admin_Plugin */
        if ($plugin = plugin_getRequestAdminPlugin()) {
            $toc = $plugin->getTOC();
            $TOC = $toc; // avoid later rebuild
        }
    }

    trigger_event('TPL_TOC_RENDER', $toc, null, false);
    $html = html_TOC($toc);
    if($return) return $html;
    echo $html;
    return '';
}

/**
 * Handle the admin page contents
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @return bool
 */
function tpl_admin() {
    global $INFO;
    global $TOC;
    global $INPUT;

    $plugin = null;
    $class  = $INPUT->str('page');
    if(!empty($class)) {
        $pluginlist = plugin_list('admin');

        if(in_array($class, $pluginlist)) {
            // attempt to load the plugin
            /** @var $plugin DokuWiki_Admin_Plugin */
            $plugin = plugin_load('admin', $class);
        }
    }

    if($plugin !== null) {
        if(!is_array($TOC)) $TOC = $plugin->getTOC(); //if TOC wasn't requested yet
        if($INFO['prependTOC']) tpl_toc();
        $plugin->html();
    } else {
        $admin = new dokuwiki\Ui\Admin();
        $admin->show();
    }
    return true;
}

/**
 * Print the correct HTML meta headers
 *
 * This has to go into the head section of your template.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @triggers TPL_METAHEADER_OUTPUT
 * @param  bool $alt Should feeds and alternative format links be added?
 * @return bool
 */
function tpl_metaheaders($alt = true) {
    global $ID;
    global $REV;
    global $INFO;
    global $JSINFO;
    global $ACT;
    global $QUERY;
    global $lang;
    global $conf;
    global $updateVersion;
    /** @var Input $INPUT */
    global $INPUT;

    // prepare the head array
    $head = array();

    // prepare seed for js and css
    $tseed   = $updateVersion;
    $depends = getConfigFiles('main');
    $depends[] = DOKU_CONF."tpl/".$conf['template']."/style.ini";
    foreach($depends as $f) $tseed .= @filemtime($f);
    $tseed   = md5($tseed);

    // the usual stuff
    $head['meta'][] = array('name'=> 'generator', 'content'=> 'DokuWiki');
    if(actionOK('search')) {
        $head['link'][] = array(
            'rel' => 'search', 'type'=> 'application/opensearchdescription+xml',
            'href'=> DOKU_BASE.'lib/exe/opensearch.php', 'title'=> $conf['title']
        );
    }

    $head['link'][] = array('rel'=> 'start', 'href'=> DOKU_BASE);
    if(actionOK('index')) {
        $head['link'][] = array(
            'rel'  => 'contents', 'href'=> wl($ID, 'do=index', false, '&'),
            'title'=> $lang['btn_index']
        );
    }

    if($alt) {
        if(actionOK('rss')) {
            $head['link'][] = array(
                'rel'  => 'alternate', 'type'=> 'application/rss+xml',
                'title'=> $lang['btn_recent'], 'href'=> DOKU_BASE.'feed.php'
            );
            $head['link'][] = array(
                'rel'  => 'alternate', 'type'=> 'application/rss+xml',
                'title'=> $lang['currentns'],
                'href' => DOKU_BASE.'feed.php?mode=list&ns='.$INFO['namespace']
            );
        }
        if(($ACT == 'show' || $ACT == 'search') && $INFO['writable']) {
            $head['link'][] = array(
                'rel'  => 'edit',
                'title'=> $lang['btn_edit'],
                'href' => wl($ID, 'do=edit', false, '&')
            );
        }

        if(actionOK('rss') && $ACT == 'search') {
            $head['link'][] = array(
                'rel'  => 'alternate', 'type'=> 'application/rss+xml',
                'title'=> $lang['searchresult'],
                'href' => DOKU_BASE.'feed.php?mode=search&q='.$QUERY
            );
        }

        if(actionOK('export_xhtml')) {
            $head['link'][] = array(
                'rel' => 'alternate', 'type'=> 'text/html', 'title'=> $lang['plainhtml'],
                'href'=> exportlink($ID, 'xhtml', '', false, '&')
            );
        }

        if(actionOK('export_raw')) {
            $head['link'][] = array(
                'rel' => 'alternate', 'type'=> 'text/plain', 'title'=> $lang['wikimarkup'],
                'href'=> exportlink($ID, 'raw', '', false, '&')
            );
        }
    }

    // setup robot tags apropriate for different modes
    if(($ACT == 'show' || $ACT == 'export_xhtml') && !$REV) {
        if($INFO['exists']) {
            //delay indexing:
            if((time() - $INFO['lastmod']) >= $conf['indexdelay']) {
                $head['meta'][] = array('name'=> 'robots', 'content'=> 'index,follow');
            } else {
                $head['meta'][] = array('name'=> 'robots', 'content'=> 'noindex,nofollow');
            }
            $canonicalUrl = wl($ID, '', true, '&');
            if ($ID == $conf['start']) {
                $canonicalUrl = DOKU_URL;
            }
            $head['link'][] = array('rel'=> 'canonical', 'href'=> $canonicalUrl);
        } else {
            $head['meta'][] = array('name'=> 'robots', 'content'=> 'noindex,follow');
        }
    } elseif(defined('DOKU_MEDIADETAIL')) {
        $head['meta'][] = array('name'=> 'robots', 'content'=> 'index,follow');
    } else {
        $head['meta'][] = array('name'=> 'robots', 'content'=> 'noindex,nofollow');
    }

    // set metadata
    if($ACT == 'show' || $ACT == 'export_xhtml') {
        // keywords (explicit or implicit)
        if(!empty($INFO['meta']['subject'])) {
            $head['meta'][] = array('name'=> 'keywords', 'content'=> join(',', $INFO['meta']['subject']));
        } else {
            $head['meta'][] = array('name'=> 'keywords', 'content'=> str_replace(':', ',', $ID));
        }
    }

    // load stylesheets
    $head['link'][] = array(
        'rel' => 'stylesheet', 'type'=> 'text/css',
        'href'=> DOKU_BASE.'lib/exe/css.php?t='.rawurlencode($conf['template']).'&tseed='.$tseed
    );

    // make $INFO and other vars available to JavaScripts
    $json   = new JSON();
    $script = "var NS='".$INFO['namespace']."';";
    if($conf['useacl'] && $INPUT->server->str('REMOTE_USER')) {
        $script .= "var SIG='".toolbar_signature()."';";
    }
    $script .= 'var JSINFO = '.$json->encode($JSINFO).';';
    $head['script'][] = array('type'=> 'text/javascript', '_data'=> $script);

    // load jquery
    $jquery = getCdnUrls();
    foreach($jquery as $src) {
        $head['script'][] = array(
            'type' => 'text/javascript', 'charset' => 'utf-8', '_data' => '', 'src' => $src
        );
    }

    // load our javascript dispatcher
    $head['script'][] = array(
        'type'=> 'text/javascript', 'charset'=> 'utf-8', '_data'=> '',
        'src' => DOKU_BASE.'lib/exe/js.php'.'?t='.rawurlencode($conf['template']).'&tseed='.$tseed
    );

    // trigger event here
    trigger_event('TPL_METAHEADER_OUTPUT', $head, '_tpl_metaheaders_action', true);
    return true;
}

/**
 * prints the array build by tpl_metaheaders
 *
 * $data is an array of different header tags. Each tag can have multiple
 * instances. Attributes are given as key value pairs. Values will be HTML
 * encoded automatically so they should be provided as is in the $data array.
 *
 * For tags having a body attribute specify the body data in the special
 * attribute '_data'. This field will NOT BE ESCAPED automatically.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param array $data
 */
function _tpl_metaheaders_action($data) {
    foreach($data as $tag => $inst) {
        if($tag == 'script') {
            echo "<!--[if gte IE 9]><!-->\n"; // no scripts for old IE
        }
        foreach($inst as $attr) {
            echo '<', $tag, ' ', buildAttributes($attr);
            if(isset($attr['_data']) || $tag == 'script') {
                if($tag == 'script' && $attr['_data'])
                    $attr['_data'] = "/*<![CDATA[*/".
                        $attr['_data'].
                        "\n/*!]]>*/";

                echo '>', $attr['_data'], '</', $tag, '>';
            } else {
                echo '/>';
            }
            echo "\n";
        }
        if($tag == 'script') {
            echo "<!--<![endif]-->\n";
        }
    }
}

/**
 * Print a link
 *
 * Just builds a link.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $url
 * @param string $name
 * @param string $more
 * @param bool $return if true return the link html, otherwise print
 * @return bool|string html of the link, or true if printed
 */
function tpl_link($url, $name, $more = '', $return = false) {
    $out = '<a href="'.$url.'" ';
    if($more) $out .= ' '.$more;
    $out .= ">$name</a>";
    if($return) return $out;
    print $out;
    return true;
}

/**
 * Prints a link to a WikiPage
 *
 * Wrapper around html_wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string      $id   page id
 * @param string|null $name the name of the link
 * @return bool true
 */
function tpl_pagelink($id, $name = null) {
    print '<bdi>'.html_wikilink($id, $name).'</bdi>';
    return true;
}

/**
 * get the parent page
 *
 * Tries to find out which page is parent.
 * returns false if none is available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $id page id
 * @return false|string
 */
function tpl_getparent($id) {
    $parent = getNS($id).':';
    resolve_pageid('', $parent, $exists);
    if($parent == $id) {
        $pos    = strrpos(getNS($id), ':');
        $parent = substr($parent, 0, $pos).':';
        resolve_pageid('', $parent, $exists);
        if($parent == $id) return false;
    }
    return $parent;
}

/**
 * Print one of the buttons
 *
 * @author Adrian Lang <mail@adrianlang.de>
 * @see    tpl_get_action
 *
 * @param string $type
 * @param bool $return
 * @return bool|string html, or false if no data, true if printed
 */
function tpl_button($type, $return = false) {
    $data = tpl_get_action($type);
    if($data === false) {
        return false;
    } elseif(!is_array($data)) {
        $out = sprintf($data, 'button');
    } else {
        /**
         * @var string $accesskey
         * @var string $id
         * @var string $method
         * @var array  $params
         */
        extract($data);
        if($id === '#dokuwiki__top') {
            $out = html_topbtn();
        } else {
            $out = html_btn($type, $id, $accesskey, $params, $method);
        }
    }
    if($return) return $out;
    echo $out;
    return true;
}

/**
 * Like the action buttons but links
 *
 * @author Adrian Lang <mail@adrianlang.de>
 * @see    tpl_get_action
 *
 * @param string $type    action command
 * @param string $pre     prefix of link
 * @param string $suf     suffix of link
 * @param string $inner   innerHML of link
 * @param bool   $return  if true it returns html, otherwise prints
 * @return bool|string html or false if no data, true if printed
 */
function tpl_actionlink($type, $pre = '', $suf = '', $inner = '', $return = false) {
    global $lang;
    $data = tpl_get_action($type);
    if($data === false) {
        return false;
    } elseif(!is_array($data)) {
        $out = sprintf($data, 'link');
    } else {
        /**
         * @var string $accesskey
         * @var string $id
         * @var string $method
         * @var bool   $nofollow
         * @var array  $params
         * @var string $replacement
         */
        extract($data);
        if(strpos($id, '#') === 0) {
            $linktarget = $id;
        } else {
            $linktarget = wl($id, $params);
        }
        $caption = $lang['btn_'.$type];
        if(strpos($caption, '%s')){
            $caption = sprintf($caption, $replacement);
        }
        $akey    = $addTitle = '';
        if($accesskey) {
            $akey     = 'accesskey="'.$accesskey.'" ';
            $addTitle = ' ['.strtoupper($accesskey).']';
        }
        $rel = $nofollow ? 'rel="nofollow" ' : '';
        $out = tpl_link(
            $linktarget, $pre.(($inner) ? $inner : $caption).$suf,
            'class="action '.$type.'" '.
                $akey.$rel.
                'title="'.hsc($caption).$addTitle.'"', true
        );
    }
    if($return) return $out;
    echo $out;
    return true;
}

/**
 * Check the actions and get data for buttons and links
 *
 * Available actions are
 *
 *  edit        - edit/create/show/draft
 *  history     - old revisions
 *  recent      - recent changes
 *  login       - login/logout - if ACL enabled
 *  profile     - user profile (if logged in)
 *  index       - The index
 *  admin       - admin page - if enough rights
 *  top         - back to top
 *  back        - back to parent - if available
 *  backlink    - links to the list of backlinks
 *  subscribe/subscription- subscribe/unsubscribe
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @author Adrian Lang <mail@adrianlang.de>
 *
 * @param string $type
 * @return array|bool|string
 */
function tpl_get_action($type) {
    global $ID;
    global $INFO;
    global $REV;
    global $ACT;
    global $conf;
    /** @var Input $INPUT */
    global $INPUT;

    // check disabled actions and fix the badly named ones
    if($type == 'history') $type = 'revisions';
    if ($type == 'subscription') $type = 'subscribe';
    if(!actionOK($type)) return false;

    $accesskey   = null;
    $id          = $ID;
    $method      = 'get';
    $params      = array('do' => $type);
    $nofollow    = true;
    $replacement = '';

    $unknown = false;
    switch($type) {
        case 'edit':
            // most complicated type - we need to decide on current action
            if($ACT == 'show' || $ACT == 'search') {
                $method = 'post';
                if($INFO['writable']) {
                    $accesskey = 'e';
                    if(!empty($INFO['draft'])) {
                        $type         = 'draft';
                        $params['do'] = 'draft';
                    } else {
                        $params['rev'] = $REV;
                        if(!$INFO['exists']) {
                            $type = 'create';
                        }
                    }
                } else {
                    if(!actionOK('source')) return false; //pseudo action
                    $params['rev'] = $REV;
                    $type          = 'source';
                    $accesskey     = 'v';
                }
            } else {
                $params    = array('do' => '');
                $type      = 'show';
                $accesskey = 'v';
            }
            break;
        case 'revisions':
            $type      = 'revs';
            $accesskey = 'o';
            break;
        case 'recent':
            $accesskey = 'r';
            break;
        case 'index':
            $accesskey = 'x';
            // allow searchbots to get to the sitemap from the homepage (when dokuwiki isn't providing a sitemap.xml)
            if ($conf['start'] == $ID && !$conf['sitemap']) {
                $nofollow = false;
            }
            break;
        case 'top':
            $accesskey = 't';
            $params    = array('do' => '');
            $id        = '#dokuwiki__top';
            break;
        case 'back':
            $parent = tpl_getparent($ID);
            if(!$parent) {
                return false;
            }
            $id        = $parent;
            $params    = array('do' => '');
            $accesskey = 'b';
            break;
        case 'img_backto':
            $params = array();
            $accesskey = 'b';
            $replacement = $ID;
            break;
        case 'login':
            $params['sectok'] = getSecurityToken();
            if($INPUT->server->has('REMOTE_USER')) {
                if(!actionOK('logout')) {
                    return false;
                }
                $params['do'] = 'logout';
                $type         = 'logout';
            }
            break;
        case 'register':
            if($INPUT->server->str('REMOTE_USER')) {
                return false;
            }
            break;
        case 'resendpwd':
            if($INPUT->server->str('REMOTE_USER')) {
                return false;
            }
            break;
        case 'admin':
            if(!$INFO['ismanager']) {
                return false;
            }
            break;
        case 'revert':
            if(!$INFO['ismanager'] || !$REV || !$INFO['writable']) {
                return false;
            }
            $params['rev']    = $REV;
            $params['sectok'] = getSecurityToken();
            break;
        case 'subscribe':
            if(!$INPUT->server->str('REMOTE_USER')) {
                return false;
            }
            break;
        case 'backlink':
            break;
        case 'profile':
            if(!$INPUT->server->has('REMOTE_USER')) {
                return false;
            }
            break;
        case 'media':
            $params['ns'] = getNS($ID);
            break;
        case 'mediaManager':
            // View image in media manager
            global $IMG;
            $imgNS = getNS($IMG);
            $authNS = auth_quickaclcheck("$imgNS:*");
            if ($authNS < AUTH_UPLOAD) {
                return false;
            }
            $params = array(
                'ns' => $imgNS,
                'image' => $IMG,
                'do' => 'media'
            );
            //$type = 'media';
            break;
        default:
            //unknown type
            $unknown = true;
    }

    $data = compact('accesskey', 'type', 'id', 'method', 'params', 'nofollow', 'replacement');

    $evt = new Doku_Event('TPL_ACTION_GET', $data);
    if($evt->advise_before()) {
        //handle unknown types
        if($unknown) {
            $data = '[unknown %s type]';
        }
    }
    $evt->advise_after();
    unset($evt);

    return $data;
}

/**
 * Wrapper around tpl_button() and tpl_actionlink()
 *
 * @author Anika Henke <anika@selfthinker.org>
 *
 * @param string        $type action command
 * @param bool          $link link or form button?
 * @param string|bool   $wrapper HTML element wrapper
 * @param bool          $return return or print
 * @param string        $pre prefix for links
 * @param string        $suf suffix for links
 * @param string        $inner inner HTML for links
 * @return bool|string
 */
function tpl_action($type, $link = false, $wrapper = false, $return = false, $pre = '', $suf = '', $inner = '') {
    $out = '';
    if($link) {
        $out .= tpl_actionlink($type, $pre, $suf, $inner, true);
    } else {
        $out .= tpl_button($type, true);
    }
    if($out && $wrapper) $out = "<$wrapper>$out</$wrapper>";

    if($return) return $out;
    print $out;
    return $out ? true : false;
}

/**
 * Print the search form
 *
 * If the first parameter is given a div with the ID 'qsearch_out' will
 * be added which instructs the ajax pagequicksearch to kick in and place
 * its output into this div. The second parameter controls the propritary
 * attribute autocomplete. If set to false this attribute will be set with an
 * value of "off" to instruct the browser to disable it's own built in
 * autocompletion feature (MSIE and Firefox)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param bool $ajax
 * @param bool $autocomplete
 * @return bool
 */
function tpl_searchform($ajax = true, $autocomplete = true) {
    global $lang;
    global $ACT;
    global $QUERY;

    // don't print the search form if search action has been disabled
    if(!actionOK('search')) return false;

    print '<form action="'.wl().'" accept-charset="utf-8" class="search" id="dw__search" method="get" role="search"><div class="no">';
    print '<input type="hidden" name="do" value="search" />';
    print '<input type="text" ';
    if($ACT == 'search') print 'value="'.htmlspecialchars($QUERY).'" ';
    print 'placeholder="'.$lang['btn_search'].'" ';
    if(!$autocomplete) print 'autocomplete="off" ';
    print 'id="qsearch__in" accesskey="f" name="id" class="edit" title="[F]" />';
    print '<button type="submit" title="'.$lang['btn_search'].'">'.$lang['btn_search'].'</button>';
    if($ajax) print '<div id="qsearch__out" class="ajax_qsearch JSpopup"></div>';
    print '</div></form>';
    return true;
}

/**
 * Print the breadcrumbs trace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $sep Separator between entries
 * @return bool
 */
function tpl_breadcrumbs($sep = '•') {
    global $lang;
    global $conf;

    //check if enabled
    if(!$conf['breadcrumbs']) return false;

    $crumbs = breadcrumbs(); //setup crumb trace

    $crumbs_sep = ' <span class="bcsep">'.$sep.'</span> ';

    //render crumbs, highlight the last one
    print '<span class="bchead">'.$lang['breadcrumb'].'</span>';
    $last = count($crumbs);
    $i    = 0;
    foreach($crumbs as $id => $name) {
        $i++;
        echo $crumbs_sep;
        if($i == $last) print '<span class="curid">';
        print '<bdi>';
        tpl_link(wl($id), hsc($name), 'class="breadcrumbs" title="'.$id.'"');
        print '</bdi>';
        if($i == $last) print '</span>';
    }
    return true;
}

/**
 * Hierarchical breadcrumbs
 *
 * This code was suggested as replacement for the usual breadcrumbs.
 * It only makes sense with a deep site structure.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Nigel McNie <oracle.shinoda@gmail.com>
 * @author Sean Coates <sean@caedmon.net>
 * @author <fredrik@averpil.com>
 * @todo   May behave strangely in RTL languages
 *
 * @param string $sep Separator between entries
 * @return bool
 */
function tpl_youarehere($sep = ' » ') {
    global $conf;
    global $ID;
    global $lang;

    // check if enabled
    if(!$conf['youarehere']) return false;

    $parts = explode(':', $ID);
    $count = count($parts);

    echo '<span class="bchead">'.$lang['youarehere'].' </span>';

    // always print the startpage
    echo '<span class="home">';
    tpl_pagelink(':'.$conf['start']);
    echo '</span>';

    // print intermediate namespace links
    $part = '';
    for($i = 0; $i < $count - 1; $i++) {
        $part .= $parts[$i].':';
        $page = $part;
        if($page == $conf['start']) continue; // Skip startpage

        // output
        echo $sep;
        tpl_pagelink($page);
    }

    // print current page, skipping start page, skipping for namespace index
    resolve_pageid('', $page, $exists);
    if(isset($page) && $page == $part.$parts[$i]) return true;
    $page = $part.$parts[$i];
    if($page == $conf['start']) return true;
    echo $sep;
    tpl_pagelink($page);
    return true;
}

/**
 * Print info if the user is logged in
 * and show full name in that case
 *
 * Could be enhanced with a profile link in future?
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @return bool
 */
function tpl_userinfo() {
    global $lang;
    /** @var Input $INPUT */
    global $INPUT;

    if($INPUT->server->str('REMOTE_USER')) {
        print $lang['loggedinas'].' '.userlink();
        return true;
    }
    return false;
}

/**
 * Print some info about the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param bool $ret return content instead of printing it
 * @return bool|string
 */
function tpl_pageinfo($ret = false) {
    global $conf;
    global $lang;
    global $INFO;
    global $ID;

    // return if we are not allowed to view the page
    if(!auth_quickaclcheck($ID)) {
        return false;
    }

    // prepare date and path
    $fn = $INFO['filepath'];
    if(!$conf['fullpath']) {
        if($INFO['rev']) {
            $fn = str_replace($conf['olddir'].'/', '', $fn);
        } else {
            $fn = str_replace($conf['datadir'].'/', '', $fn);
        }
    }
    $fn   = utf8_decodeFN($fn);
    $date = dformat($INFO['lastmod']);

    // print it
    if($INFO['exists']) {
        $out = '';
        $out .= '<bdi>'.$fn.'</bdi>';
        $out .= ' · ';
        $out .= $lang['lastmod'];
        $out .= ' ';
        $out .= $date;
        if($INFO['editor']) {
            $out .= ' '.$lang['by'].' ';
            $out .= '<bdi>'.editorinfo($INFO['editor']).'</bdi>';
        } else {
            $out .= ' ('.$lang['external_edit'].')';
        }
        if($INFO['locked']) {
            $out .= ' · ';
            $out .= $lang['lockedby'];
            $out .= ' ';
            $out .= '<bdi>'.editorinfo($INFO['locked']).'</bdi>';
        }
        if($ret) {
            return $out;
        } else {
            echo $out;
            return true;
        }
    }
    return false;
}

/**
 * Prints or returns the name of the given page (current one if none given).
 *
 * If useheading is enabled this will use the first headline else
 * the given ID is used.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $id page id
 * @param bool   $ret return content instead of printing
 * @return bool|string
 */
function tpl_pagetitle($id = null, $ret = false) {
    global $ACT, $INPUT, $conf, $lang;

    if(is_null($id)) {
        global $ID;
        $id = $ID;
    }

    $name = $id;
    if(useHeading('navigation')) {
        $first_heading = p_get_first_heading($id);
        if($first_heading) $name = $first_heading;
    }

    // default page title is the page name, modify with the current action
    switch ($ACT) {
        // admin functions
        case 'admin' :
            $page_title = $lang['btn_admin'];
            // try to get the plugin name
            /** @var $plugin DokuWiki_Admin_Plugin */
            if ($plugin = plugin_getRequestAdminPlugin()){
                $plugin_title = $plugin->getMenuText($conf['lang']);
                $page_title = $plugin_title ? $plugin_title : $plugin->getPluginName();
            }
            break;

        // user functions
        case 'login' :
        case 'profile' :
        case 'register' :
        case 'resendpwd' :
            $page_title = $lang['btn_'.$ACT];
            break;

         // wiki functions
        case 'search' :
        case 'index' :
            $page_title = $lang['btn_'.$ACT];
            break;

        // page functions
        case 'edit' :
            $page_title = "✎ ".$name;
            break;

        case 'revisions' :
            $page_title = $name . ' - ' . $lang['btn_revs'];
            break;

        case 'backlink' :
        case 'recent' :
        case 'subscribe' :
            $page_title = $name . ' - ' . $lang['btn_'.$ACT];
            break;

        default : // SHOW and anything else not included
            $page_title = $name;
    }

    if($ret) {
        return hsc($page_title);
    } else {
        print hsc($page_title);
        return true;
    }
}

/**
 * Returns the requested EXIF/IPTC tag from the current image
 *
 * If $tags is an array all given tags are tried until a
 * value is found. If no value is found $alt is returned.
 *
 * Which texts are known is defined in the functions _exifTagNames
 * and _iptcTagNames() in inc/jpeg.php (You need to prepend IPTC
 * to the names of the latter one)
 *
 * Only allowed in: detail.php
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param array|string $tags tag or array of tags to try
 * @param string       $alt  alternative output if no data was found
 * @param null|string  $src  the image src, uses global $SRC if not given
 * @return string
 */
function tpl_img_getTag($tags, $alt = '', $src = null) {
    // Init Exif Reader
    global $SRC;

    if(is_null($src)) $src = $SRC;

    static $meta = null;
    if(is_null($meta)) $meta = new JpegMeta($src);
    if($meta === false) return $alt;
    $info = cleanText($meta->getField($tags));
    if($info == false) return $alt;
    return $info;
}

/**
 * Returns a description list of the metatags of the current image
 *
 * @return string html of description list
 */
function tpl_img_meta() {
    global $lang;

    $tags = tpl_get_img_meta();

    echo '<dl>';
    foreach($tags as $tag) {
        $label = $lang[$tag['langkey']];
        if(!$label) $label = $tag['langkey'] . ':';

        echo '<dt>'.$label.'</dt><dd>';
        if ($tag['type'] == 'date') {
            echo dformat($tag['value']);
        } else {
            echo hsc($tag['value']);
        }
        echo '</dd>';
    }
    echo '</dl>';
}

/**
 * Returns metadata as configured in mediameta config file, ready for creating html
 *
 * @return array with arrays containing the entries:
 *   - string langkey  key to lookup in the $lang var, if not found printed as is
 *   - string type     type of value
 *   - string value    tag value (unescaped)
 */
function tpl_get_img_meta() {

    $config_files = getConfigFiles('mediameta');
    foreach ($config_files as $config_file) {
        if(file_exists($config_file)) {
            include($config_file);
        }
    }
    /** @var array $fields the included array with metadata */

    $tags = array();
    foreach($fields as $tag){
        $t = array();
        if (!empty($tag[0])) {
            $t = array($tag[0]);
        }
        if(is_array($tag[3])) {
            $t = array_merge($t,$tag[3]);
        }
        $value = tpl_img_getTag($t);
        if ($value) {
            $tags[] = array('langkey' => $tag[1], 'type' => $tag[2], 'value' => $value);
        }
    }
    return $tags;
}

/**
 * Prints the image with a link to the full sized version
 *
 * Only allowed in: detail.php
 *
 * @triggers TPL_IMG_DISPLAY
 * @param $maxwidth  int - maximal width of the image
 * @param $maxheight int - maximal height of the image
 * @param $link bool     - link to the orginal size?
 * @param $params array  - additional image attributes
 * @return bool Result of TPL_IMG_DISPLAY
 */
function tpl_img($maxwidth = 0, $maxheight = 0, $link = true, $params = null) {
    global $IMG;
    /** @var Input $INPUT */
    global $INPUT;
    global $REV;
    $w = (int) tpl_img_getTag('File.Width');
    $h = (int) tpl_img_getTag('File.Height');

    //resize to given max values
    $ratio = 1;
    if($w >= $h) {
        if($maxwidth && $w >= $maxwidth) {
            $ratio = $maxwidth / $w;
        } elseif($maxheight && $h > $maxheight) {
            $ratio = $maxheight / $h;
        }
    } else {
        if($maxheight && $h >= $maxheight) {
            $ratio = $maxheight / $h;
        } elseif($maxwidth && $w > $maxwidth) {
            $ratio = $maxwidth / $w;
        }
    }
    if($ratio) {
        $w = floor($ratio * $w);
        $h = floor($ratio * $h);
    }

    //prepare URLs
    $url = ml($IMG, array('cache'=> $INPUT->str('cache'),'rev'=>$REV), true, '&');
    $src = ml($IMG, array('cache'=> $INPUT->str('cache'),'rev'=>$REV, 'w'=> $w, 'h'=> $h), true, '&');

    //prepare attributes
    $alt = tpl_img_getTag('Simple.Title');
    if(is_null($params)) {
        $p = array();
    } else {
        $p = $params;
    }
    if($w) $p['width'] = $w;
    if($h) $p['height'] = $h;
    $p['class'] = 'img_detail';
    if($alt) {
        $p['alt']   = $alt;
        $p['title'] = $alt;
    } else {
        $p['alt'] = '';
    }
    $p['src'] = $src;

    $data = array('url'=> ($link ? $url : null), 'params'=> $p);
    return trigger_event('TPL_IMG_DISPLAY', $data, '_tpl_img_action', true);
}

/**
 * Default action for TPL_IMG_DISPLAY
 *
 * @param array $data
 * @return bool
 */
function _tpl_img_action($data) {
    global $lang;
    $p = buildAttributes($data['params']);

    if($data['url']) print '<a href="'.hsc($data['url']).'" title="'.$lang['mediaview'].'">';
    print '<img '.$p.'/>';
    if($data['url']) print '</a>';
    return true;
}

/**
 * This function inserts a small gif which in reality is the indexer function.
 *
 * Should be called somewhere at the very end of the main.php
 * template
 *
 * @return bool
 */
function tpl_indexerWebBug() {
    global $ID;

    $p           = array();
    $p['src']    = DOKU_BASE.'lib/exe/indexer.php?id='.rawurlencode($ID).
        '&'.time();
    $p['width']  = 2; //no more 1x1 px image because we live in times of ad blockers...
    $p['height'] = 1;
    $p['alt']    = '';
    $att         = buildAttributes($p);
    print "<img $att />";
    return true;
}

/**
 * tpl_getConf($id)
 *
 * use this function to access template configuration variables
 *
 * @param string $id      name of the value to access
 * @param mixed  $notset  what to return if the setting is not available
 * @return mixed
 */
function tpl_getConf($id, $notset=false) {
    global $conf;
    static $tpl_configloaded = false;

    $tpl = $conf['template'];

    if(!$tpl_configloaded) {
        $tconf = tpl_loadConfig();
        if($tconf !== false) {
            foreach($tconf as $key => $value) {
                if(isset($conf['tpl'][$tpl][$key])) continue;
                $conf['tpl'][$tpl][$key] = $value;
            }
            $tpl_configloaded = true;
        }
    }

    if(isset($conf['tpl'][$tpl][$id])){
        return $conf['tpl'][$tpl][$id];
    }

    return $notset;
}

/**
 * tpl_loadConfig()
 *
 * reads all template configuration variables
 * this function is automatically called by tpl_getConf()
 *
 * @return array
 */
function tpl_loadConfig() {

    $file = tpl_incdir().'/conf/default.php';
    $conf = array();

    if(!file_exists($file)) return false;

    // load default config file
    include($file);

    return $conf;
}

// language methods
/**
 * tpl_getLang($id)
 *
 * use this function to access template language variables
 *
 * @param string $id key of language string
 * @return string
 */
function tpl_getLang($id) {
    static $lang = array();

    if(count($lang) === 0) {
        global $conf, $config_cascade; // definitely don't invoke "global $lang"

        $path = tpl_incdir() . 'lang/';

        $lang = array();

        // don't include once
        @include($path . 'en/lang.php');
        foreach($config_cascade['lang']['template'] as $config_file) {
            if(file_exists($config_file . $conf['template'] . '/en/lang.php')) {
                include($config_file . $conf['template'] . '/en/lang.php');
            }
        }

        if($conf['lang'] != 'en') {
            @include($path . $conf['lang'] . '/lang.php');
            foreach($config_cascade['lang']['template'] as $config_file) {
                if(file_exists($config_file . $conf['template'] . '/' . $conf['lang'] . '/lang.php')) {
                    include($config_file . $conf['template'] . '/' . $conf['lang'] . '/lang.php');
                }
            }
        }
    }
    return $lang[$id];
}

/**
 * Retrieve a language dependent file and pass to xhtml renderer for display
 * template equivalent of p_locale_xhtml()
 *
 * @param   string $id id of language dependent wiki page
 * @return  string     parsed contents of the wiki page in xhtml format
 */
function tpl_locale_xhtml($id) {
    return p_cached_output(tpl_localeFN($id));
}

/**
 * Prepends appropriate path for a language dependent filename
 *
 * @param string $id id of localized text
 * @return string wiki text
 */
function tpl_localeFN($id) {
    $path = tpl_incdir().'lang/';
    global $conf;
    $file = DOKU_CONF.'template_lang/'.$conf['template'].'/'.$conf['lang'].'/'.$id.'.txt';
    if (!file_exists($file)){
        $file = $path.$conf['lang'].'/'.$id.'.txt';
        if(!file_exists($file)){
            //fall back to english
            $file = $path.'en/'.$id.'.txt';
        }
    }
    return $file;
}

/**
 * prints the "main content" in the mediamanager popup
 *
 * Depending on the user's actions this may be a list of
 * files in a namespace, the meta editing dialog or
 * a message of referencing pages
 *
 * Only allowed in mediamanager.php
 *
 * @triggers MEDIAMANAGER_CONTENT_OUTPUT
 * @param bool $fromajax - set true when calling this function via ajax
 * @param string $sort
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_mediaContent($fromajax = false, $sort='natural') {
    global $IMG;
    global $AUTH;
    global $INUSE;
    global $NS;
    global $JUMPTO;
    /** @var Input $INPUT */
    global $INPUT;

    $do = $INPUT->extract('do')->str('do');
    if(in_array($do, array('save', 'cancel'))) $do = '';

    if(!$do) {
        if($INPUT->bool('edit')) {
            $do = 'metaform';
        } elseif(is_array($INUSE)) {
            $do = 'filesinuse';
        } else {
            $do = 'filelist';
        }
    }

    // output the content pane, wrapped in an event.
    if(!$fromajax) ptln('<div id="media__content">');
    $data = array('do' => $do);
    $evt  = new Doku_Event('MEDIAMANAGER_CONTENT_OUTPUT', $data);
    if($evt->advise_before()) {
        $do = $data['do'];
        if($do == 'filesinuse') {
            media_filesinuse($INUSE, $IMG);
        } elseif($do == 'filelist') {
            media_filelist($NS, $AUTH, $JUMPTO,false,$sort);
        } elseif($do == 'searchlist') {
            media_searchlist($INPUT->str('q'), $NS, $AUTH);
        } else {
            msg('Unknown action '.hsc($do), -1);
        }
    }
    $evt->advise_after();
    unset($evt);
    if(!$fromajax) ptln('</div>');

}

/**
 * Prints the central column in full-screen media manager
 * Depending on the opened tab this may be a list of
 * files in a namespace, upload form or search form
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function tpl_mediaFileList() {
    global $AUTH;
    global $NS;
    global $JUMPTO;
    global $lang;
    /** @var Input $INPUT */
    global $INPUT;

    $opened_tab = $INPUT->str('tab_files');
    if(!$opened_tab || !in_array($opened_tab, array('files', 'upload', 'search'))) $opened_tab = 'files';
    if($INPUT->str('mediado') == 'update') $opened_tab = 'upload';

    echo '<h2 class="a11y">'.$lang['mediaselect'].'</h2>'.NL;

    media_tabs_files($opened_tab);

    echo '<div class="panelHeader">'.NL;
    echo '<h3>';
    $tabTitle = ($NS) ? $NS : '['.$lang['mediaroot'].']';
    printf($lang['media_'.$opened_tab], '<strong>'.hsc($tabTitle).'</strong>');
    echo '</h3>'.NL;
    if($opened_tab === 'search' || $opened_tab === 'files') {
        media_tab_files_options();
    }
    echo '</div>'.NL;

    echo '<div class="panelContent">'.NL;
    if($opened_tab == 'files') {
        media_tab_files($NS, $AUTH, $JUMPTO);
    } elseif($opened_tab == 'upload') {
        media_tab_upload($NS, $AUTH, $JUMPTO);
    } elseif($opened_tab == 'search') {
        media_tab_search($NS, $AUTH);
    }
    echo '</div>'.NL;
}

/**
 * Prints the third column in full-screen media manager
 * Depending on the opened tab this may be details of the
 * selected file, the meta editing dialog or
 * list of file revisions
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function tpl_mediaFileDetails($image, $rev) {
    global $conf, $DEL, $lang;
    /** @var Input $INPUT */
    global $INPUT;

    $removed = (!file_exists(mediaFN($image)) && file_exists(mediaMetaFN($image, '.changes')) && $conf['mediarevisions']);
    if(!$image || (!file_exists(mediaFN($image)) && !$removed) || $DEL) return;
    if($rev && !file_exists(mediaFN($image, $rev))) $rev = false;
    $ns = getNS($image);
    $do = $INPUT->str('mediado');

    $opened_tab = $INPUT->str('tab_details');

    $tab_array = array('view');
    list(, $mime) = mimetype($image);
    if($mime == 'image/jpeg') {
        $tab_array[] = 'edit';
    }
    if($conf['mediarevisions']) {
        $tab_array[] = 'history';
    }

    if(!$opened_tab || !in_array($opened_tab, $tab_array)) $opened_tab = 'view';
    if($INPUT->bool('edit')) $opened_tab = 'edit';
    if($do == 'restore') $opened_tab = 'view';

    media_tabs_details($image, $opened_tab);

    echo '<div class="panelHeader"><h3>';
    list($ext) = mimetype($image, false);
    $class    = preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
    $class    = 'select mediafile mf_'.$class;
    $tabTitle = '<strong><a href="'.ml($image).'" class="'.$class.'" title="'.$lang['mediaview'].'">'.$image.'</a>'.'</strong>';
    if($opened_tab === 'view' && $rev) {
        printf($lang['media_viewold'], $tabTitle, dformat($rev));
    } else {
        printf($lang['media_'.$opened_tab], $tabTitle);
    }

    echo '</h3></div>'.NL;

    echo '<div class="panelContent">'.NL;

    if($opened_tab == 'view') {
        media_tab_view($image, $ns, null, $rev);

    } elseif($opened_tab == 'edit' && !$removed) {
        media_tab_edit($image, $ns);

    } elseif($opened_tab == 'history' && $conf['mediarevisions']) {
        media_tab_history($image, $ns);
    }

    echo '</div>'.NL;
}

/**
 * prints the namespace tree in the mediamanager popup
 *
 * Only allowed in mediamanager.php
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_mediaTree() {
    global $NS;
    ptln('<div id="media__tree">');
    media_nstree($NS);
    ptln('</div>');
}

/**
 * Print a dropdown menu with all DokuWiki actions
 *
 * Note: this will not use any pretty URLs
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $empty empty option label
 * @param string $button submit button label
 */
function tpl_actiondropdown($empty = '', $button = '&gt;') {
    global $ID;
    global $REV;
    global $lang;
    /** @var Input $INPUT */
    global $INPUT;

    $action_structure = array(
        'page_tools' => array('edit', 'revert', 'revisions', 'backlink', 'subscribe'),
        'site_tools' => array('recent', 'media', 'index'),
        'user_tools' => array('login', 'register', 'profile', 'admin'),
    );

    echo '<form action="'.script().'" method="get" accept-charset="utf-8">';
    echo '<div class="no">';
    echo '<input type="hidden" name="id" value="'.$ID.'" />';
    if($REV) echo '<input type="hidden" name="rev" value="'.$REV.'" />';
    if ($INPUT->server->str('REMOTE_USER')) {
        echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />';
    }

    echo '<select name="do" class="edit quickselect" title="'.$lang['tools'].'">';
    echo '<option value="">'.$empty.'</option>';

    foreach($action_structure as $tools => $actions) {
        echo '<optgroup label="'.$lang[$tools].'">';
        foreach($actions as $action) {
            $act = tpl_get_action($action);
            if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';
        }
        echo '</optgroup>';
    }

    echo '</select>';
    echo '<button type="submit">'.$button.'</button>';
    echo '</div>';
    echo '</form>';
}

/**
 * Print a informational line about the used license
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $img     print image? (|button|badge)
 * @param  bool   $imgonly skip the textual description?
 * @param  bool   $return  when true don't print, but return HTML
 * @param  bool   $wrap    wrap in div with class="license"?
 * @return string
 */
function tpl_license($img = 'badge', $imgonly = false, $return = false, $wrap = true) {
    global $license;
    global $conf;
    global $lang;
    if(!$conf['license']) return '';
    if(!is_array($license[$conf['license']])) return '';
    $lic    = $license[$conf['license']];
    $target = ($conf['target']['extern']) ? ' target="'.$conf['target']['extern'].'"' : '';

    $out = '';
    if($wrap) $out .= '<div class="license">';
    if($img) {
        $src = license_img($img);
        if($src) {
            $out .= '<a href="'.$lic['url'].'" rel="license"'.$target;
            $out .= '><img src="'.DOKU_BASE.$src.'" alt="'.$lic['name'].'" /></a>';
            if(!$imgonly) $out .= ' ';
        }
    }
    if(!$imgonly) {
        $out .= $lang['license'].' ';
        $out .= '<bdi><a href="'.$lic['url'].'" rel="license" class="urlextern"'.$target;
        $out .= '>'.$lic['name'].'</a></bdi>';
    }
    if($wrap) $out .= '</div>';

    if($return) return $out;
    echo $out;
    return '';
}

/**
 * Includes the rendered HTML of a given page
 *
 * This function is useful to populate sidebars or similar features in a
 * template
 *
 * @param string $pageid The page name you want to include
 * @param bool $print Should the content be printed or returned only
 * @param bool $propagate Search higher namespaces, too?
 * @param bool $useacl Include the page only if the ACLs check out?
 * @return bool|null|string
 */
function tpl_include_page($pageid, $print = true, $propagate = false, $useacl = true) {
    if($propagate) {
        $pageid = page_findnearest($pageid, $useacl);
    } elseif($useacl && auth_quickaclcheck($pageid) == AUTH_NONE) {
        return false;
    }
    if(!$pageid) return false;

    global $TOC;
    $oldtoc = $TOC;
    $html   = p_wiki_xhtml($pageid, '', false);
    $TOC    = $oldtoc;

    if($print) echo $html;
    return $html;
}

/**
 * Display the subscribe form
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function tpl_subscribe() {
    global $INFO;
    global $ID;
    global $lang;
    global $conf;
    $stime_days = $conf['subscribe_time'] / 60 / 60 / 24;

    echo p_locale_xhtml('subscr_form');
    echo '<h2>'.$lang['subscr_m_current_header'].'</h2>';
    echo '<div class="level2">';
    if($INFO['subscribed'] === false) {
        echo '<p>'.$lang['subscr_m_not_subscribed'].'</p>';
    } else {
        echo '<ul>';
        foreach($INFO['subscribed'] as $sub) {
            echo '<li><div class="li">';
            if($sub['target'] !== $ID) {
                echo '<code class="ns">'.hsc(prettyprint_id($sub['target'])).'</code>';
            } else {
                echo '<code class="page">'.hsc(prettyprint_id($sub['target'])).'</code>';
            }
            $sstl = sprintf($lang['subscr_style_'.$sub['style']], $stime_days);
            if(!$sstl) $sstl = hsc($sub['style']);
            echo ' ('.$sstl.') ';

            echo '<a href="'.wl(
                $ID,
                array(
                     'do'        => 'subscribe',
                     'sub_target'=> $sub['target'],
                     'sub_style' => $sub['style'],
                     'sub_action'=> 'unsubscribe',
                     'sectok'    => getSecurityToken()
                )
            ).
                '" class="unsubscribe">'.$lang['subscr_m_unsubscribe'].
                '</a></div></li>';
        }
        echo '</ul>';
    }
    echo '</div>';

    // Add new subscription form
    echo '<h2>'.$lang['subscr_m_new_header'].'</h2>';
    echo '<div class="level2">';
    $ns      = getNS($ID).':';
    $targets = array(
        $ID => '<code class="page">'.prettyprint_id($ID).'</code>',
        $ns => '<code class="ns">'.prettyprint_id($ns).'</code>',
    );
    $styles  = array(
        'every'  => $lang['subscr_style_every'],
        'digest' => sprintf($lang['subscr_style_digest'], $stime_days),
        'list'   => sprintf($lang['subscr_style_list'], $stime_days),
    );

    $form = new Doku_Form(array('id' => 'subscribe__form'));
    $form->startFieldset($lang['subscr_m_subscribe']);
    $form->addRadioSet('sub_target', $targets);
    $form->startFieldset($lang['subscr_m_receive']);
    $form->addRadioSet('sub_style', $styles);
    $form->addHidden('sub_action', 'subscribe');
    $form->addHidden('do', 'subscribe');
    $form->addHidden('id', $ID);
    $form->endFieldset();
    $form->addElement(form_makeButton('submit', 'subscribe', $lang['subscr_m_subscribe']));
    html_form('SUBSCRIBE', $form);
    echo '</div>';
}

/**
 * Tries to send already created content right to the browser
 *
 * Wraps around ob_flush() and flush()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_flush() {
    ob_flush();
    flush();
}

/**
 * Tries to find a ressource file in the given locations.
 *
 * If a given location starts with a colon it is assumed to be a media
 * file, otherwise it is assumed to be relative to the current template
 *
 * @param  string[] $search       locations to look at
 * @param  bool     $abs           if to use absolute URL
 * @param  array   &$imginfo   filled with getimagesize()
 * @return string
 *
 * @author Andreas  Gohr <andi@splitbrain.org>
 */
function tpl_getMediaFile($search, $abs = false, &$imginfo = null) {
    $img     = '';
    $file    = '';
    $ismedia = false;
    // loop through candidates until a match was found:
    foreach($search as $img) {
        if(substr($img, 0, 1) == ':') {
            $file    = mediaFN($img);
            $ismedia = true;
        } else {
            $file    = tpl_incdir().$img;
            $ismedia = false;
        }

        if(file_exists($file)) break;
    }

    // fetch image data if requested
    if(!is_null($imginfo)) {
        $imginfo = getimagesize($file);
    }

    // build URL
    if($ismedia) {
        $url = ml($img, '', true, '', $abs);
    } else {
        $url = tpl_basedir().$img;
        if($abs) $url = DOKU_URL.substr($url, strlen(DOKU_REL));
    }

    return $url;
}

/**
 * PHP include a file
 *
 * either from the conf directory if it exists, otherwise use
 * file in the template's root directory.
 *
 * The function honours config cascade settings and looks for the given
 * file next to the ´main´ config files, in the order protected, local,
 * default.
 *
 * Note: no escaping or sanity checking is done here. Never pass user input
 * to this function!
 *
 * @author Anika Henke <anika@selfthinker.org>
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $file
 */
function tpl_includeFile($file) {
    global $config_cascade;
    foreach(array('protected', 'local', 'default') as $config_group) {
        if(empty($config_cascade['main'][$config_group])) continue;
        foreach($config_cascade['main'][$config_group] as $conf_file) {
            $dir = dirname($conf_file);
            if(file_exists("$dir/$file")) {
                include("$dir/$file");
                return;
            }
        }
    }

    // still here? try the template dir
    $file = tpl_incdir().$file;
    if(file_exists($file)) {
        include($file);
    }
}

/**
 * Returns <link> tag for various icon types (favicon|mobile|generic)
 *
 * @author Anika Henke <anika@selfthinker.org>
 *
 * @param  array $types - list of icon types to display (favicon|mobile|generic)
 * @return string
 */
function tpl_favicon($types = array('favicon')) {

    $return = '';

    foreach($types as $type) {
        switch($type) {
            case 'favicon':
                $look = array(':wiki:favicon.ico', ':favicon.ico', 'images/favicon.ico');
                $return .= '<link rel="shortcut icon" href="'.tpl_getMediaFile($look).'" />'.NL;
                break;
            case 'mobile':
                $look = array(':wiki:apple-touch-icon.png', ':apple-touch-icon.png', 'images/apple-touch-icon.png');
                $return .= '<link rel="apple-touch-icon" href="'.tpl_getMediaFile($look).'" />'.NL;
                break;
            case 'generic':
                // ideal world solution, which doesn't work in any browser yet
                $look = array(':wiki:favicon.svg', ':favicon.svg', 'images/favicon.svg');
                $return .= '<link rel="icon" href="'.tpl_getMediaFile($look).'" type="image/svg+xml" />'.NL;
                break;
        }
    }

    return $return;
}

/**
 * Prints full-screen media manager
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function tpl_media() {
    global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT;
    $fullscreen = true;
    require_once DOKU_INC.'lib/exe/mediamanager.php';

    $rev   = '';
    $image = cleanID($INPUT->str('image'));
    if(isset($IMG)) $image = $IMG;
    if(isset($JUMPTO)) $image = $JUMPTO;
    if(isset($REV) && !$JUMPTO) $rev = $REV;

    echo '<div id="mediamanager__page">'.NL;
    echo '<h1>'.$lang['btn_media'].'</h1>'.NL;
    html_msgarea();

    echo '<div class="panel namespaces">'.NL;
    echo '<h2>'.$lang['namespaces'].'</h2>'.NL;
    echo '<div class="panelHeader">';
    echo $lang['media_namespaces'];
    echo '</div>'.NL;

    echo '<div class="panelContent" id="media__tree">'.NL;
    media_nstree($NS);
    echo '</div>'.NL;
    echo '</div>'.NL;

    echo '<div class="panel filelist">'.NL;
    tpl_mediaFileList();
    echo '</div>'.NL;

    echo '<div class="panel file">'.NL;
    echo '<h2 class="a11y">'.$lang['media_file'].'</h2>'.NL;
    tpl_mediaFileDetails($image, $rev);
    echo '</div>'.NL;

    echo '</div>'.NL;
}

/**
 * Return useful layout classes
 *
 * @author Anika Henke <anika@selfthinker.org>
 *
 * @return string
 */
function tpl_classes() {
    global $ACT, $conf, $ID, $INFO;
    /** @var Input $INPUT */
    global $INPUT;

    $classes = array(
        'dokuwiki',
        'mode_'.$ACT,
        'tpl_'.$conf['template'],
        $INPUT->server->bool('REMOTE_USER') ? 'loggedIn' : '',
        $INFO['exists'] ? '' : 'notFound',
        ($ID == $conf['start']) ? 'home' : '',
    );
    return join(' ', $classes);
}

/**
 * Create event for tools menues
 *
 * @author Anika Henke <anika@selfthinker.org>
 * @param string $toolsname name of menu
 * @param array $items
 * @param string $view e.g. 'main', 'detail', ...
 */
function tpl_toolsevent($toolsname, $items, $view = 'main') {
    $data = array(
        'view' => $view,
        'items' => $items
    );

    $hook = 'TEMPLATE_' . strtoupper($toolsname) . '_DISPLAY';
    $evt = new Doku_Event($hook, $data);
    if($evt->advise_before()) {
        foreach($evt->data['items'] as $k => $html) echo $html;
    }
    $evt->advise_after();
}

//Setup VIM: ex: et ts=4 :

