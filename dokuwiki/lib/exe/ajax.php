<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
//close session
session_write_close();

header('Content-Type: text/html; charset=utf-8');

//call the requested function
if($INPUT->post->has('call')){
    $call = $INPUT->post->str('call');
}else if($INPUT->get->has('call')){
    $call = $INPUT->get->str('call');
}else{
    exit;
}
$callfn = 'ajax_'.$call;

if(function_exists($callfn)){
    $callfn();
}else{
    $evt = new Doku_Event('AJAX_CALL_UNKNOWN', $call);
    if ($evt->advise_before()) {
        print "AJAX call '".htmlspecialchars($call)."' unknown!\n";
        exit;
    }
    $evt->advise_after();
    unset($evt);
}

/**
 * Searches for matching pagenames
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_qsearch(){
    global $lang;
    global $INPUT;

    $maxnumbersuggestions = 50;

    $query = $INPUT->post->str('q');
    if(empty($query)) $query = $INPUT->get->str('q');
    if(empty($query)) return;

    $query = urldecode($query);

    $data = ft_pageLookup($query, true, useHeading('navigation'));

    if(!count($data)) return;

    print '<strong>'.$lang['quickhits'].'</strong>';
    print '<ul>';
    $counter = 0;
    foreach($data as $id => $title){
        if (useHeading('navigation')) {
            $name = $title;
        } else {
            $ns = getNS($id);
            if($ns){
                $name = noNS($id).' ('.$ns.')';
            }else{
                $name = $id;
            }
        }
        echo '<li>' . html_wikilink(':'.$id,$name) . '</li>';

        $counter ++;
        if($counter > $maxnumbersuggestions) {
            echo '<li>...</li>';
            break;
        }
    }
    print '</ul>';
}

/**
 * Support OpenSearch suggestions
 *
 * @link   http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.0
 * @author Mike Frysinger <vapier@gentoo.org>
 */
function ajax_suggestions() {
    global $INPUT;

    $query = cleanID($INPUT->post->str('q'));
    if(empty($query)) $query = cleanID($INPUT->get->str('q'));
    if(empty($query)) return;

    $data = ft_pageLookup($query);
    if(!count($data)) return;
    $data = array_keys($data);

    // limit results to 15 hits
    $data = array_slice($data, 0, 15);
    $data = array_map('trim',$data);
    $data = array_map('noNS',$data);
    $data = array_unique($data);
    sort($data);

    /* now construct a json */
    $suggestions = array(
                        $query,  // the original query
                        $data,   // some suggestions
                        array(), // no description
                        array()  // no urls
                   );
    $json = new JSON();

    header('Content-Type: application/x-suggestions+json');
    print $json->encode($suggestions);
}

/**
 * Refresh a page lock and save draft
 *
 * Andreas Gohr <andi@splitbrain.org>
 */
function ajax_lock(){
    global $conf;
    global $lang;
    global $ID;
    global $INFO;
    global $INPUT;

    $ID = cleanID($INPUT->post->str('id'));
    if(empty($ID)) return;

    $INFO = pageinfo();

    if (!$INFO['writable']) {
        echo 'Permission denied';
        return;
    }

    if(!checklock($ID)){
        lock($ID);
        echo 1;
    }

    if($conf['usedraft'] && $INPUT->post->str('wikitext')){
        $client = $_SERVER['REMOTE_USER'];
        if(!$client) $client = clientIP(true);

        $draft = array('id'     => $ID,
                'prefix' => substr($INPUT->post->str('prefix'), 0, -1),
                'text'   => $INPUT->post->str('wikitext'),
                'suffix' => $INPUT->post->str('suffix'),
                'date'   => $INPUT->post->int('date'),
                'client' => $client,
                );
        $cname = getCacheName($draft['client'].$ID,'.draft');
        if(io_saveFile($cname,serialize($draft))){
            echo $lang['draftdate'].' '.dformat();
        }
    }

}

/**
 * Delete a draft
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_draftdel(){
    global $INPUT;
    $id = cleanID($INPUT->str('id'));
    if(empty($id)) return;

    $client = $_SERVER['REMOTE_USER'];
    if(!$client) $client = clientIP(true);

    $cname = getCacheName($client.$id,'.draft');
    @unlink($cname);
}

/**
 * Return subnamespaces for the Mediamanager
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_medians(){
    global $conf;
    global $INPUT;

    // wanted namespace
    $ns  = cleanID($INPUT->post->str('ns'));
    $dir  = utf8_encodeFN(str_replace(':','/',$ns));

    $lvl = count(explode(':',$ns));

    $data = array();
    search($data,$conf['mediadir'],'search_index',array('nofiles' => true),$dir);
    foreach(array_keys($data) as $item){
        $data[$item]['level'] = $lvl+1;
    }
    echo html_buildlist($data, 'idx', 'media_nstree_item', 'media_nstree_li');
}

/**
 * Return list of files for the Mediamanager
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_medialist(){
    global $NS;
    global $INPUT;

    $NS = cleanID($INPUT->post->str('ns'));
    $sort = $INPUT->post->bool('recent') ? 'date' : 'natural';
    if ($INPUT->post->str('do') == 'media') {
        tpl_mediaFileList();
    } else {
        tpl_mediaContent(true, $sort);
    }
}

/**
 * Return the content of the right column
 * (image details) for the Mediamanager
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function ajax_mediadetails(){
    global $IMG, $JUMPTO, $REV, $fullscreen, $INPUT;
    $fullscreen = true;
    require_once(DOKU_INC.'lib/exe/mediamanager.php');

    $image = '';
    if ($INPUT->has('image')) $image = cleanID($INPUT->str('image'));
    if (isset($IMG)) $image = $IMG;
    if (isset($JUMPTO)) $image = $JUMPTO;
    $rev = false;
    if (isset($REV) && !$JUMPTO) $rev = $REV;

    html_msgarea();
    tpl_mediaFileDetails($image, $rev);
}

/**
 * Returns image diff representation for mediamanager
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function ajax_mediadiff(){
    global $NS;
    global $INPUT;

    $image = '';
    if ($INPUT->has('image')) $image = cleanID($INPUT->str('image'));
    $NS = getNS($image);
    $auth = auth_quickaclcheck("$NS:*");
    media_diff($image, $NS, $auth, true);
}

function ajax_mediaupload(){
    global $NS, $MSG, $INPUT;

    $id = '';
    if ($_FILES['qqfile']['tmp_name']) {
        $id = $INPUT->post->str('mediaid', $_FILES['qqfile']['name']);
    } elseif ($INPUT->get->has('qqfile')) {
        $id = $INPUT->get->str('qqfile');
    }

    $id = cleanID($id);

    $NS = $INPUT->str('ns');
    $ns = $NS.':'.getNS($id);

    $AUTH = auth_quickaclcheck("$ns:*");
    if($AUTH >= AUTH_UPLOAD) { io_createNamespace("$ns:xxx", 'media'); }

    if ($_FILES['qqfile']['error']) unset($_FILES['qqfile']);

    $res = false;
    if ($_FILES['qqfile']['tmp_name']) $res = media_upload($NS, $AUTH, $_FILES['qqfile']);
    if ($INPUT->get->has('qqfile')) $res = media_upload_xhr($NS, $AUTH);

    if($res) {
        $result = array(
            'success' => true,
            'link' => media_managerURL(array('ns' => $ns, 'image' => $NS . ':' . $id), '&'),
            'id' => $NS . ':' . $id,
            'ns' => $NS
        );
    } else {
        $error = '';
        if(isset($MSG)) {
            foreach($MSG as $msg) {
                $error .= $msg['msg'];
            }
        }
        $result = array(
            'error' => $error,
            'ns' => $NS
        );
    }
    $json = new JSON;
    header('Content-Type: application/json');
    echo $json->encode($result);
}

/**
 * Return sub index for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_index(){
    global $conf;
    global $INPUT;

    // wanted namespace
    $ns  = cleanID($INPUT->post->str('idx'));
    $dir  = utf8_encodeFN(str_replace(':','/',$ns));

    $lvl = count(explode(':',$ns));

    $data = array();
    search($data,$conf['datadir'],'search_index',array('ns' => $ns),$dir);
    foreach(array_keys($data) as $item){
        $data[$item]['level'] = $lvl+1;
    }
    echo html_buildlist($data, 'idx', 'html_list_index', 'html_li_index');
}

/**
 * List matching namespaces and pages for the link wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function ajax_linkwiz(){
    global $conf;
    global $lang;
    global $INPUT;

    $q  = ltrim(trim($INPUT->post->str('q')),':');
    $id = noNS($q);
    $ns = getNS($q);

    $ns = cleanID($ns);
    $id = cleanID($id);

    $nsd  = utf8_encodeFN(str_replace(':','/',$ns));

    $data = array();
    if($q && !$ns){

        // use index to lookup matching pages
        $pages = ft_pageLookup($id,true);

        // result contains matches in pages and namespaces
        // we now extract the matching namespaces to show
        // them seperately
        $dirs  = array();

        foreach($pages as $pid => $title){
            if(strpos(noNS($pid),$id) === false){
                // match was in the namespace
                $dirs[getNS($pid)] = 1; // assoc array avoids dupes
            }else{
                // it is a matching page, add it to the result
                $data[] = array(
                        'id'    => $pid,
                        'title' => $title,
                        'type'  => 'f',
                        );
            }
            unset($pages[$pid]);
        }
        foreach($dirs as $dir => $junk){
            $data[] = array(
                    'id'   => $dir,
                    'type' => 'd',
                    );
        }

    }else{

        $opts = array(
                'depth' => 1,
                'listfiles' => true,
                'listdirs'  => true,
                'pagesonly' => true,
                'firsthead' => true,
                'sneakyacl' => $conf['sneaky_index'],
                );
        if($id) $opts['filematch'] = '^.*\/'.$id;
        if($id) $opts['dirmatch']  = '^.*\/'.$id;
        search($data,$conf['datadir'],'search_universal',$opts,$nsd);

        // add back to upper
        if($ns){
            array_unshift($data,array(
                        'id'   => getNS($ns),
                        'type' => 'u',
                        ));
        }
    }

    // fixme sort results in a useful way ?

    if(!count($data)){
        echo $lang['nothingfound'];
        exit;
    }

    // output the found data
    $even = 1;
    foreach($data as $item){
        $even *= -1; //zebra

        if(($item['type'] == 'd' || $item['type'] == 'u') && $item['id']) $item['id'] .= ':';
        $link = wl($item['id']);

        echo '<div class="'.(($even > 0)?'even':'odd').' type_'.$item['type'].'">';

        if($item['type'] == 'u'){
            $name = $lang['upperns'];
        }else{
            $name = htmlspecialchars($item['id']);
        }

        echo '<a href="'.$link.'" title="'.htmlspecialchars($item['id']).'" class="wikilink1">'.$name.'</a>';

        if(!blank($item['title'])){
            echo '<span>'.htmlspecialchars($item['title']).'</span>';
        }
        echo '</div>';
    }

}

//Setup VIM: ex: et ts=2 :
