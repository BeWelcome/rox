<?php
/**
 * Utilities for handling pagenames
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @todo       Combine similar functions like {wiki,media,meta}FN()
 */

/**
 * Fetch the an ID from request
 *
 * Uses either standard $_REQUEST variable or extracts it from
 * the full request URI when userewrite is set to 2
 *
 * For $param='id' $conf['start'] is returned if no id was found.
 * If the second parameter is true (default) the ID is cleaned.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $param  the $_REQUEST variable name, default 'id'
 * @param bool   $clean  if true, ID is cleaned
 * @return string
 */
function getID($param='id',$clean=true){
    /** @var Input $INPUT */
    global $INPUT;
    global $conf;
    global $ACT;

    $id = $INPUT->str($param);

    //construct page id from request URI
    if(empty($id) && $conf['userewrite'] == 2){
        $request = $INPUT->server->str('REQUEST_URI');
        $script = '';

        //get the script URL
        if($conf['basedir']){
            $relpath = '';
            if($param != 'id') {
                $relpath = 'lib/exe/';
            }
            $script = $conf['basedir'].$relpath.utf8_basename($INPUT->server->str('SCRIPT_FILENAME'));

        }elseif($INPUT->server->str('PATH_INFO')){
            $request = $INPUT->server->str('PATH_INFO');
        }elseif($INPUT->server->str('SCRIPT_NAME')){
            $script = $INPUT->server->str('SCRIPT_NAME');
        }elseif($INPUT->server->str('DOCUMENT_ROOT') && $INPUT->server->str('SCRIPT_FILENAME')){
            $script = preg_replace ('/^'.preg_quote($INPUT->server->str('DOCUMENT_ROOT'),'/').'/','',
                    $INPUT->server->str('SCRIPT_FILENAME'));
            $script = '/'.$script;
        }

        //clean script and request (fixes a windows problem)
        $script  = preg_replace('/\/\/+/','/',$script);
        $request = preg_replace('/\/\/+/','/',$request);

        //remove script URL and Querystring to gain the id
        if(preg_match('/^'.preg_quote($script,'/').'(.*)/',$request, $match)){
            $id = preg_replace ('/\?.*/','',$match[1]);
        }
        $id = urldecode($id);
        //strip leading slashes
        $id = preg_replace('!^/+!','',$id);
    }

    // Namespace autolinking from URL
    if(substr($id,-1) == ':' || ($conf['useslash'] && substr($id,-1) == '/')){
        if(page_exists($id.$conf['start'])){
            // start page inside namespace
            $id = $id.$conf['start'];
        }elseif(page_exists($id.noNS(cleanID($id)))){
            // page named like the NS inside the NS
            $id = $id.noNS(cleanID($id));
        }elseif(page_exists($id)){
            // page like namespace exists
            $id = substr($id,0,-1);
        }else{
            // fall back to default
            $id = $id.$conf['start'];
        }
        if (isset($ACT) && $ACT === 'show') {
            $urlParameters = $_GET;
            if (isset($urlParameters['id'])) {
                unset($urlParameters['id']);
            }
            send_redirect(wl($id,$urlParameters,true));
        }
    }

    if($clean) $id = cleanID($id);
    if(empty($id) && $param=='id') $id = $conf['start'];

    return $id;
}

/**
 * Remove unwanted chars from ID
 *
 * Cleans a given ID to only use allowed characters. Accented characters are
 * converted to unaccented ones
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param  string  $raw_id    The pageid to clean
 * @param  boolean $ascii     Force ASCII
 * @return string cleaned id
 */
function cleanID($raw_id,$ascii=false){
    global $conf;
    static $sepcharpat = null;

    global $cache_cleanid;
    $cache = & $cache_cleanid;

    // check if it's already in the memory cache
    if (!$ascii && isset($cache[(string)$raw_id])) {
        return $cache[(string)$raw_id];
    }

    $sepchar = $conf['sepchar'];
    if($sepcharpat == null) // build string only once to save clock cycles
        $sepcharpat = '#\\'.$sepchar.'+#';

    $id = trim((string)$raw_id);
    $id = utf8_strtolower($id);

    //alternative namespace seperator
    if($conf['useslash']){
        $id = strtr($id,';/','::');
    }else{
        $id = strtr($id,';/',':'.$sepchar);
    }

    if($conf['deaccent'] == 2 || $ascii) $id = utf8_romanize($id);
    if($conf['deaccent'] || $ascii) $id = utf8_deaccent($id,-1);

    //remove specials
    $id = utf8_stripspecials($id,$sepchar,'\*');

    if($ascii) $id = utf8_strip($id);

    //clean up
    $id = preg_replace($sepcharpat,$sepchar,$id);
    $id = preg_replace('#:+#',':',$id);
    $id = trim($id,':._-');
    $id = preg_replace('#:[:\._\-]+#',':',$id);
    $id = preg_replace('#[:\._\-]+:#',':',$id);

    if (!$ascii) $cache[(string)$raw_id] = $id;
    return($id);
}

/**
 * Return namespacepart of a wiki ID
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $id
 * @return string|false the namespace part or false if the given ID has no namespace (root)
 */
function getNS($id){
    $pos = strrpos((string)$id,':');
    if($pos!==false){
        return substr((string)$id,0,$pos);
    }
    return false;
}

/**
 * Returns the ID without the namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $id
 * @return string
 */
function noNS($id) {
    $pos = strrpos($id, ':');
    if ($pos!==false) {
        return substr($id, $pos+1);
    } else {
        return $id;
    }
}

/**
 * Returns the current namespace
 *
 * @author Nathan Fritz <fritzn@crown.edu>
 *
 * @param string $id
 * @return string
 */
function curNS($id) {
    return noNS(getNS($id));
}

/**
 * Returns the ID without the namespace or current namespace for 'start' pages
 *
 * @author Nathan Fritz <fritzn@crown.edu>
 *
 * @param string $id
 * @return string
 */
function noNSorNS($id) {
    global $conf;

    $p = noNS($id);
    if ($p == $conf['start'] || $p == false) {
        $p = curNS($id);
        if ($p == false) {
            return $conf['start'];
        }
    }
    return $p;
}

/**
 * Creates a XHTML valid linkid from a given headline title
 *
 * @param string  $title   The headline title
 * @param array|bool   $check   Existing IDs (title => number)
 * @return string the title
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function sectionID($title,&$check) {
    $title = str_replace(array(':','.'),'',cleanID($title));
    $new = ltrim($title,'0123456789_-');
    if(empty($new)){
        $title = 'section'.preg_replace('/[^0-9]+/','',$title); //keep numbers from headline
    }else{
        $title = $new;
    }

    if(is_array($check)){
        // make sure tiles are unique
        if (!array_key_exists ($title,$check)) {
            $check[$title] = 0;
        } else {
            $title .= ++ $check[$title];
        }
    }

    return $title;
}

/**
 * Wiki page existence check
 *
 * parameters as for wikiFN
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 *
 * @param string $id page id
 * @param string|int $rev empty or revision timestamp
 * @param bool $clean flag indicating that $id should be cleaned (see wikiFN as well)
 * @param bool $date_at
 * @return bool exists?
 */
function page_exists($id,$rev='',$clean=true, $date_at=false) {
    if($rev !== '' && $date_at) {
        $pagelog = new PageChangeLog($id);
        $pagelog_rev = $pagelog->getLastRevisionAt($rev);
        if($pagelog_rev !== false)
            $rev = $pagelog_rev;
    }
    return file_exists(wikiFN($id,$rev,$clean));
}

/**
 * returns the full path to the datafile specified by ID and optional revision
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @param  $raw_id  string   id of wikipage
 * @param  $rev     int|string   page revision, empty string for current
 * @param  $clean   bool     flag indicating that $raw_id should be cleaned.  Only set to false
 *                           when $id is guaranteed to have been cleaned already.
 * @return string full path
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function wikiFN($raw_id,$rev='',$clean=true){
    global $conf;

    global $cache_wikifn;
    $cache = & $cache_wikifn;

    $id = $raw_id;

    if ($clean) $id = cleanID($id);
    $id = str_replace(':','/',$id);

    if (isset($cache[$id]) && isset($cache[$id][$rev])) {
        return $cache[$id][$rev];
    }

    if(empty($rev)){
        $fn = $conf['datadir'].'/'.utf8_encodeFN($id).'.txt';
    }else{
        $fn = $conf['olddir'].'/'.utf8_encodeFN($id).'.'.$rev.'.txt';
        if($conf['compression']){
            //test for extensions here, we want to read both compressions
            if (file_exists($fn . '.gz')){
                $fn .= '.gz';
            }else if(file_exists($fn . '.bz2')){
                $fn .= '.bz2';
            }else{
                //file doesnt exist yet, so we take the configured extension
                $fn .= '.' . $conf['compression'];
            }
        }
    }

    if (!isset($cache[$id])) { $cache[$id] = array(); }
    $cache[$id][$rev] = $fn;
    return $fn;
}

/**
 * Returns the full path to the file for locking the page while editing.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 *
 * @param string $id page id
 * @return string full path
 */
function wikiLockFN($id) {
    global $conf;
    return $conf['lockdir'].'/'.md5(cleanID($id)).'.lock';
}


/**
 * returns the full path to the meta file specified by ID and extension
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 *
 * @param string $id   page id
 * @param string $ext  file extension
 * @return string full path
 */
function metaFN($id,$ext){
    global $conf;
    $id = cleanID($id);
    $id = str_replace(':','/',$id);
    $fn = $conf['metadir'].'/'.utf8_encodeFN($id).$ext;
    return $fn;
}

/**
 * returns the full path to the media's meta file specified by ID and extension
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 *
 * @param string $id   media id
 * @param string $ext  extension of media
 * @return string
 */
function mediaMetaFN($id,$ext){
    global $conf;
    $id = cleanID($id);
    $id = str_replace(':','/',$id);
    $fn = $conf['mediametadir'].'/'.utf8_encodeFN($id).$ext;
    return $fn;
}

/**
 * returns an array of full paths to all metafiles of a given ID
 *
 * @author Esther Brunner <esther@kaffeehaus.ch>
 * @author Michael Hamann <michael@content-space.de>
 *
 * @param string $id page id
 * @return array
 */
function metaFiles($id){
    $basename = metaFN($id, '');
    $files    = glob($basename.'.*', GLOB_MARK);
    // filter files like foo.bar.meta when $id == 'foo'
    return    $files ? preg_grep('/^'.preg_quote($basename, '/').'\.[^.\/]*$/u', $files) : array();
}

/**
 * returns the full path to the mediafile specified by ID
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kate Arzamastseva <pshns@ukr.net>
 *
 * @param string     $id  media id
 * @param string|int $rev empty string or revision timestamp
 * @return string full path
 */
function mediaFN($id, $rev='', $clean=true){
    global $conf;
    if ($clean) $id = cleanID($id);
    $id = str_replace(':','/',$id);
    if(empty($rev)){
        $fn = $conf['mediadir'].'/'.utf8_encodeFN($id);
    }else{
        $ext = mimetype($id);
        $name = substr($id,0, -1*strlen($ext[0])-1);
        $fn = $conf['mediaolddir'].'/'.utf8_encodeFN($name .'.'.( (int) $rev ).'.'.$ext[0]);
    }
    return $fn;
}

/**
 * Returns the full filepath to a localized file if local
 * version isn't found the english one is returned
 *
 * @param  string $id  The id of the local file
 * @param  string $ext The file extension (usually txt)
 * @return string full filepath to localized file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function localeFN($id,$ext='txt'){
    global $conf;
    $file = DOKU_CONF.'lang/'.$conf['lang'].'/'.$id.'.'.$ext;
    if(!file_exists($file)){
        $file = DOKU_INC.'inc/lang/'.$conf['lang'].'/'.$id.'.'.$ext;
        if(!file_exists($file)){
            //fall back to english
            $file = DOKU_INC.'inc/lang/en/'.$id.'.'.$ext;
        }
    }
    return $file;
}

/**
 * Resolve relative paths in IDs
 *
 * Do not call directly use resolve_mediaid or resolve_pageid
 * instead
 *
 * Partyly based on a cleanPath function found at
 * http://php.net/manual/en/function.realpath.php#57016
 *
 * @author <bart at mediawave dot nl>
 *
 * @param string $ns     namespace which is context of id
 * @param string $id     relative id
 * @param bool   $clean  flag indicating that id should be cleaned
 * @return string
 */
function resolve_id($ns,$id,$clean=true){
    global $conf;

    // some pre cleaning for useslash:
    if($conf['useslash']) $id = str_replace('/',':',$id);

    // if the id starts with a dot we need to handle the
    // relative stuff
    if($id && $id{0} == '.'){
        // normalize initial dots without a colon
        $id = preg_replace('/^(\.+)(?=[^:\.])/','\1:',$id);
        // prepend the current namespace
        $id = $ns.':'.$id;

        // cleanup relatives
        $result = array();
        $pathA  = explode(':', $id);
        if (!$pathA[0]) $result[] = '';
        foreach ($pathA AS $key => $dir) {
            if ($dir == '..') {
                if (end($result) == '..') {
                    $result[] = '..';
                } elseif (!array_pop($result)) {
                    $result[] = '..';
                }
            } elseif ($dir && $dir != '.') {
                $result[] = $dir;
            }
        }
        if (!end($pathA)) $result[] = '';
        $id = implode(':', $result);
    }elseif($ns !== false && strpos($id,':') === false){
        //if link contains no namespace. add current namespace (if any)
        $id = $ns.':'.$id;
    }

    if($clean) $id = cleanID($id);
    return $id;
}

/**
 * Returns a full media id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $ns namespace which is context of id
 * @param string &$page (reference) relative media id, updated to resolved id
 * @param bool &$exists (reference) updated with existance of media
 * @param int|string $rev
 * @param bool $date_at
 */
function resolve_mediaid($ns,&$page,&$exists,$rev='',$date_at=false){
    $page   = resolve_id($ns,$page);
    if($rev !== '' &&  $date_at){
        $medialog = new MediaChangeLog($page);
        $medialog_rev = $medialog->getLastRevisionAt($rev);
        if($medialog_rev !== false) {
            $rev = $medialog_rev;
        }
    }

    $file   = mediaFN($page,$rev);
    $exists = file_exists($file);
}

/**
 * Returns a full page id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $ns namespace which is context of id
 * @param string &$page (reference) relative page id, updated to resolved id
 * @param bool &$exists (reference) updated with existance of media
 * @param string $rev
 * @param bool $date_at
 */
function resolve_pageid($ns,&$page,&$exists,$rev='',$date_at=false ){
    global $conf;
    global $ID;
    $exists = false;

    //empty address should point to current page
    if ($page === "") {
        $page = $ID;
    }

    //keep hashlink if exists then clean both parts
    if (strpos($page,'#')) {
        list($page,$hash) = explode('#',$page,2);
    } else {
        $hash = '';
    }
    $hash = cleanID($hash);
    $page = resolve_id($ns,$page,false); // resolve but don't clean, yet

    // get filename (calls clean itself)
    if($rev !== '' && $date_at) {
        $pagelog = new PageChangeLog($page);
        $pagelog_rev = $pagelog->getLastRevisionAt($rev);
        if($pagelog_rev !== false)//something found
           $rev  = $pagelog_rev;
    }
    $file = wikiFN($page,$rev);

    // if ends with colon or slash we have a namespace link
    if(in_array(substr($page,-1), array(':', ';')) ||
       ($conf['useslash'] && substr($page,-1) == '/')){
        if(page_exists($page.$conf['start'],$rev,true,$date_at)){
            // start page inside namespace
            $page = $page.$conf['start'];
            $exists = true;
        }elseif(page_exists($page.noNS(cleanID($page)),$rev,true,$date_at)){
            // page named like the NS inside the NS
            $page = $page.noNS(cleanID($page));
            $exists = true;
        }elseif(page_exists($page,$rev,true,$date_at)){
            // page like namespace exists
            $page = $page;
            $exists = true;
        }else{
            // fall back to default
            $page = $page.$conf['start'];
        }
    }else{
        //check alternative plural/nonplural form
        if(!file_exists($file)){
            if( $conf['autoplural'] ){
                if(substr($page,-1) == 's'){
                    $try = substr($page,0,-1);
                }else{
                    $try = $page.'s';
                }
                if(page_exists($try,$rev,true,$date_at)){
                    $page   = $try;
                    $exists = true;
                }
            }
        }else{
            $exists = true;
        }
    }

    // now make sure we have a clean page
    $page = cleanID($page);

    //add hash if any
    if(!empty($hash)) $page .= '#'.$hash;
}

/**
 * Returns the name of a cachefile from given data
 *
 * The needed directory is created by this function!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $data  This data is used to create a unique md5 name
 * @param string $ext   This is appended to the filename if given
 * @return string       The filename of the cachefile
 */
function getCacheName($data,$ext=''){
    global $conf;
    $md5  = md5($data);
    $file = $conf['cachedir'].'/'.$md5{0}.'/'.$md5.$ext;
    io_makeFileDir($file);
    return $file;
}

/**
 * Checks a pageid against $conf['hidepages']
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 *
 * @param string $id page id
 * @return bool
 */
function isHiddenPage($id){
    $data = array(
        'id' => $id,
        'hidden' => false
    );
    trigger_event('PAGEUTILS_ID_HIDEPAGE', $data, '_isHiddenPage');
    return $data['hidden'];
}

/**
 * callback checks if page is hidden
 *
 * @param array $data event data    - see isHiddenPage()
 */
function _isHiddenPage(&$data) {
    global $conf;
    global $ACT;

    if ($data['hidden']) return;
    if(empty($conf['hidepages'])) return;
    if($ACT == 'admin') return;

    if(preg_match('/'.$conf['hidepages'].'/ui',':'.$data['id'])){
        $data['hidden'] = true;
    }
}

/**
 * Reverse of isHiddenPage
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 *
 * @param string $id page id
 * @return bool
 */
function isVisiblePage($id){
    return !isHiddenPage($id);
}

/**
 * Format an id for output to a user
 *
 * Namespaces are denoted by a trailing “:*”. The root namespace is
 * “*”. Output is escaped.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 *
 * @param string $id page id
 * @return string
 */
function prettyprint_id($id) {
    if (!$id || $id === ':') {
        return '*';
    }
    if ((substr($id, -1, 1) === ':')) {
        $id .= '*';
    }
    return hsc($id);
}

/**
 * Encode a UTF-8 filename to use on any filesystem
 *
 * Uses the 'fnencode' option to determine encoding
 *
 * When the second parameter is true the string will
 * be encoded only if non ASCII characters are detected -
 * This makes it safe to run it multiple times on the
 * same string (default is true)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    urlencode
 *
 * @param string $file file name
 * @param bool   $safe if true, only encoded when non ASCII characters detected
 * @return string
 */
function utf8_encodeFN($file,$safe=true){
    global $conf;
    if($conf['fnencode'] == 'utf-8') return $file;

    if($safe && preg_match('#^[a-zA-Z0-9/_\-\.%]+$#',$file)){
        return $file;
    }

    if($conf['fnencode'] == 'safe'){
        return SafeFN::encode($file);
    }

    $file = urlencode($file);
    $file = str_replace('%2F','/',$file);
    return $file;
}

/**
 * Decode a filename back to UTF-8
 *
 * Uses the 'fnencode' option to determine encoding
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    urldecode
 *
 * @param string $file file name
 * @return string
 */
function utf8_decodeFN($file){
    global $conf;
    if($conf['fnencode'] == 'utf-8') return $file;

    if($conf['fnencode'] == 'safe'){
        return SafeFN::decode($file);
    }

    return urldecode($file);
}

/**
 * Find a page in the current namespace (determined from $ID) or any
 * higher namespace that can be accessed by the current user,
 * this condition can be overriden by an optional parameter.
 *
 * Used for sidebars, but can be used other stuff as well
 *
 * @todo   add event hook
 *
 * @param  string $page the pagename you're looking for
 * @param bool $useacl only return pages readable by the current user, false to ignore ACLs
 * @return false|string the full page id of the found page, false if any
 */
function page_findnearest($page, $useacl = true){
    if (!$page) return false;
    global $ID;

    $ns = $ID;
    do {
        $ns = getNS($ns);
        $pageid = cleanID("$ns:$page");
        if(page_exists($pageid) && (!$useacl || auth_quickaclcheck($pageid) >= AUTH_READ)){
            return $pageid;
        }
    } while($ns);

    return false;
}
