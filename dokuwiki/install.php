<?php
/**
 * Dokuwiki installation assistance
 *
 * @author      Chris Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/');
if(!defined('DOKU_CONF')) define('DOKU_CONF',DOKU_INC.'conf/');
if(!defined('DOKU_LOCAL')) define('DOKU_LOCAL',DOKU_INC.'conf/');

// load and initialize the core system
require_once(DOKU_INC.'inc/init.php');

// check for error reporting override or set error reporting to sane values
if (!defined('DOKU_E_LEVEL')) { error_reporting(E_ALL ^ E_NOTICE); }
else { error_reporting(DOKU_E_LEVEL); }

// kill magic quotes
if (get_magic_quotes_gpc() && !defined('MAGIC_QUOTES_STRIPPED')) {
    if (!empty($_GET))    remove_magic_quotes($_GET);
    if (!empty($_POST))   remove_magic_quotes($_POST);
    if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
    if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
    @ini_set('magic_quotes_gpc', 0);
    define('MAGIC_QUOTES_STRIPPED',1);
}
if (function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);
@ini_set('magic_quotes_sybase',0);

// language strings
require_once(DOKU_INC.'inc/lang/en/lang.php');
if(isset($_REQUEST['l']) && !is_array($_REQUEST['l'])) {
    $LC = preg_replace('/[^a-z\-]+/','',$_REQUEST['l']);
}
if(empty($LC)) $LC = 'en';
if($LC && $LC != 'en' ) {
    require_once(DOKU_INC.'inc/lang/'.$LC.'/lang.php');
}

// initialise variables ...
$error = array();

$dokuwiki_hash = array(
    '2005-09-22'   => 'e33223e957b0b0a130d0520db08f8fb7',
    '2006-03-05'   => '51295727f79ab9af309a2fd9e0b61acc',
    '2006-03-09'   => '51295727f79ab9af309a2fd9e0b61acc',
    '2006-11-06'   => 'b3a8af76845977c2000d85d6990dd72b',
    '2007-05-24'   => 'd80f2740c84c4a6a791fd3c7a353536f',
    '2007-06-26'   => 'b3ca19c7a654823144119980be73cd77',
    '2008-05-04'   => '1e5c42eac3219d9e21927c39e3240aad',
    '2009-02-14'   => 'ec8c04210732a14fdfce0f7f6eead865',
    '2009-12-25'   => '993c4b2b385643efe5abf8e7010e11f4',
    '2010-11-07'   => '7921d48195f4db21b8ead6d9bea801b8',
    '2011-05-25'   => '4241865472edb6fa14a1227721008072',
    '2011-11-10'   => 'b46ff19a7587966ac4df61cbab1b8b31',
    '2012-01-25'   => '72c083c73608fc43c586901fd5dabb74',
    '2012-09-10'   => 'eb0b3fc90056fbc12bac6f49f7764df3',
    '2013-05-10'   => '7b62b75245f57f122d3e0f8ed7989623',
    '2013-12-08'   => '263c76af309fbf083867c18a34ff5214',
    '2014-05-05'   => '263c76af309fbf083867c18a34ff5214',
    '2015-08-10'   => '263c76af309fbf083867c18a34ff5214',
    '2016-06-26'   => 'fd3abb6d89853dacb032907e619fbd73',
    '2017-02-19'   => 'e4f2f5a34c9dbcd96a5ecc8f2df25bd9'
);


// begin output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?php echo $LC?>" dir="<?php echo $lang['direction']?>">
<head>
    <meta charset="utf-8" />
    <title><?php echo $lang['i_installer']?></title>
    <style type="text/css">
        body { width: 90%; margin: 0 auto; font: 84% Verdana, Helvetica, Arial, sans-serif; }
        img { border: none }
        br.cl { clear:both; }
        code { font-size: 110%; color: #800000; }
        fieldset { border: none }
        label { display: block; margin-top: 0.5em; }
        select.text, input.text { width: 30em; margin: 0 0.5em; }
        a {text-decoration: none}
    </style>
    <script type="text/javascript">
        function acltoggle(){
            var cb = document.getElementById('acl');
            var fs = document.getElementById('acldep');
            if(!cb || !fs) return;
            if(cb.checked){
                fs.style.display = '';
            }else{
                fs.style.display = 'none';
            }
        }
        window.onload = function(){
            acltoggle();
            var cb = document.getElementById('acl');
            if(cb) cb.onchange = acltoggle;
        };
    </script>
</head>
<body style="">
    <h1 style="float:left">
        <img src="lib/exe/fetch.php?media=wiki:dokuwiki-128.png"
             style="vertical-align: middle;" alt="" height="64" width="64" />
        <?php echo $lang['i_installer']?>
    </h1>
    <div style="float:right; margin: 1em;">
        <?php langsel()?>
    </div>
    <br class="cl" />

    <div style="float: right; width: 34%;">
        <?php
            if(file_exists(DOKU_INC.'inc/lang/'.$LC.'/install.html')){
                include(DOKU_INC.'inc/lang/'.$LC.'/install.html');
            }else{
                print "<div lang=\"en\" dir=\"ltr\">\n";
                include(DOKU_INC.'inc/lang/en/install.html');
                print "</div>\n";
            }
        ?>
        <a style="background: transparent url(data/security.png) left top no-repeat;
                  display: block; width:380px; height:73px; border:none; clear:both;"
           target="_blank"
           href="http://www.dokuwiki.org/security#web_access_security"></a>
    </div>

    <div style="float: left; width: 58%;">
        <?php
            if(! (check_functions() && check_permissions()) ){
                echo '<p>'.$lang['i_problems'].'</p>';
                print_errors();
                print_retry();
            }elseif(!check_configs()){
                echo '<p>'.$lang['i_modified'].'</p>';
                print_errors();
            }elseif(check_data($_REQUEST['d'])){
                // check_data has sanitized all input parameters
                if(!store_data($_REQUEST['d'])){
                    echo '<p>'.$lang['i_failure'].'</p>';
                    print_errors();
                }else{
                    echo '<p>'.$lang['i_success'].'</p>';
                }
            }else{
                print_errors();
                print_form($_REQUEST['d']);
            }
        ?>
    </div>


<div style="clear: both">
  <a href="http://dokuwiki.org/"><img src="lib/tpl/dokuwiki/images/button-dw.png" alt="driven by DokuWiki" /></a>
  <a href="http://php.net"><img src="lib/tpl/dokuwiki/images/button-php.gif" alt="powered by PHP" /></a>
</div>
</body>
</html>
<?php

/**
 * Print the input form
 *
 * @param array $d submitted entry 'd' of request data
 */
function print_form($d){
    global $lang;
    global $LC;

    include(DOKU_CONF.'license.php');

    if(!is_array($d)) $d = array();
    $d = array_map('htmlspecialchars',$d);

    if(!isset($d['acl'])) $d['acl']=1;
    if(!isset($d['pop'])) $d['pop']=1;

    ?>
    <form action="" method="post">
    <input type="hidden" name="l" value="<?php echo $LC ?>" />
    <fieldset>
        <label for="title"><?php echo $lang['i_wikiname']?>
        <input type="text" name="d[title]" id="title" value="<?php echo $d['title'] ?>" style="width: 20em;" />
        </label>

        <fieldset style="margin-top: 1em;">
            <label for="acl">
            <input type="checkbox" name="d[acl]" id="acl" <?php echo(($d['acl'] ? ' checked="checked"' : ''));?> />
            <?php echo $lang['i_enableacl']?></label>

            <fieldset id="acldep">
                <label for="superuser"><?php echo $lang['i_superuser']?></label>
                <input class="text" type="text" name="d[superuser]" id="superuser" value="<?php echo $d['superuser'] ?>" />

                <label for="fullname"><?php echo $lang['fullname']?></label>
                <input class="text" type="text" name="d[fullname]" id="fullname" value="<?php echo $d['fullname'] ?>" />

                <label for="email"><?php echo $lang['email']?></label>
                <input class="text" type="text" name="d[email]" id="email" value="<?php echo $d['email'] ?>" />

                <label for="password"><?php echo $lang['pass']?></label>
                <input class="text" type="password" name="d[password]" id="password" />

                <label for="confirm"><?php echo $lang['passchk']?></label>
                <input class="text" type="password" name="d[confirm]" id="confirm" />

                <label for="policy"><?php echo $lang['i_policy']?></label>
                <select class="text" name="d[policy]" id="policy">
                    <option value="0" <?php echo ($d['policy'] == 0)?'selected="selected"':'' ?>><?php echo $lang['i_pol0']?></option>
                    <option value="1" <?php echo ($d['policy'] == 1)?'selected="selected"':'' ?>><?php echo $lang['i_pol1']?></option>
                    <option value="2" <?php echo ($d['policy'] == 2)?'selected="selected"':'' ?>><?php echo $lang['i_pol2']?></option>
                </select>

                <label for="allowreg">
                    <input type="checkbox" name="d[allowreg]" id="allowreg" <?php echo(($d['allowreg'] ? ' checked="checked"' : ''));?> />
                    <?php echo $lang['i_allowreg']?>
                </label>
            </fieldset>
        </fieldset>

        <fieldset>
            <p><?php echo $lang['i_license']?></p>
            <?php
            array_push($license,array('name' => $lang['i_license_none'], 'url'=>''));
            if(empty($d['license'])) $d['license'] = 'cc-by-sa';
            foreach($license as $key => $lic){
                echo '<label for="lic_'.$key.'">';
                echo '<input type="radio" name="d[license]" value="'.htmlspecialchars($key).'" id="lic_'.$key.'"'.
                     (($d['license'] === $key)?' checked="checked"':'').'>';
                echo htmlspecialchars($lic['name']);
                if($lic['url']) echo ' <a href="'.$lic['url'].'" target="_blank"><sup>[?]</sup></a>';
                echo '</label>';
            }
            ?>
        </fieldset>

        <fieldset>
            <p><?php echo $lang['i_pop_field']?></p>
            <label for="pop">
                <input type="checkbox" name="d[pop]" id="pop" <?php echo(($d['pop'] ? ' checked="checked"' : ''));?> />
                <?php echo $lang['i_pop_label']?> <a href="http://www.dokuwiki.org/popularity" target="_blank"><sup>[?]</sup></a>
            </label>
        </fieldset>

    </fieldset>
    <fieldset id="process">
        <button type="submit" name="submit"><?php echo $lang['btn_save']?></button>
    </fieldset>
    </form>
    <?php
}

function print_retry() {
    global $lang;
    global $LC;
    ?>
    <form action="" method="get">
      <fieldset>
        <input type="hidden" name="l" value="<?php echo $LC ?>" />
        <button type="submit"><?php echo $lang['i_retry'];?></button>
      </fieldset>
    </form>
    <?php
}

/**
 * Check validity of data
 *
 * @author Andreas Gohr
 *
 * @param array $d
 * @return bool ok?
 */
function check_data(&$d){
    static $form_default = array(
        'title'     => '',
        'acl'       => '1',
        'superuser' => '',
        'fullname'  => '',
        'email'     => '',
        'password'  => '',
        'confirm'   => '',
        'policy'    => '0',
        'allowreg'  => '0',
        'license'   => 'cc-by-sa'
    );
    global $lang;
    global $error;

    if(!is_array($d)) $d = array();
    foreach($d as $k => $v) {
        if(is_array($v))
            unset($d[$k]);
        else
            $d[$k] = (string)$v;
    }

    //autolowercase the username
    $d['superuser'] = isset($d['superuser']) ? strtolower($d['superuser']) : "";

    $ok = false;

    if(isset($_REQUEST['submit'])) {
        $ok = true;

        // check input
        if(empty($d['title'])){
            $error[] = sprintf($lang['i_badval'],$lang['i_wikiname']);
            $ok      = false;
        }
        if(isset($d['acl'])){
            if(!preg_match('/^[a-z0-9_]+$/',$d['superuser'])){
                $error[] = sprintf($lang['i_badval'],$lang['i_superuser']);
                $ok      = false;
            }
            if(empty($d['password'])){
                $error[] = sprintf($lang['i_badval'],$lang['pass']);
                $ok      = false;
            }
            elseif(!isset($d['confirm']) || $d['confirm'] != $d['password']){
                $error[] = sprintf($lang['i_badval'],$lang['passchk']);
                $ok      = false;
            }
            if(empty($d['fullname']) || strstr($d['fullname'],':')){
                $error[] = sprintf($lang['i_badval'],$lang['fullname']);
                $ok      = false;
            }
            if(empty($d['email']) || strstr($d['email'],':') || !strstr($d['email'],'@')){
                $error[] = sprintf($lang['i_badval'],$lang['email']);
                $ok      = false;
            }
        }
    }
    $d = array_merge($form_default, $d);
    return $ok;
}

/**
 * Writes the data to the config files
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 *
 * @param array $d
 * @return bool
 */
function store_data($d){
    global $LC;
    $ok = true;
    $d['policy'] = (int) $d['policy'];

    // create local.php
    $now    = gmdate('r');
    $output = <<<EOT
<?php
/**
 * Dokuwiki's Main Configuration File - Local Settings
 * Auto-generated by install script
 * Date: $now
 */

EOT;
    // add any config options set by a previous installer
    $preset = __DIR__.'/install.conf';
    if(file_exists($preset)){
        $output .= "# preset config options\n";
        $output .= file_get_contents($preset);
        $output .= "\n\n";
        $output .= "# options selected in installer\n";
        @unlink($preset);
    }

    $output .= '$conf[\'title\'] = \''.addslashes($d['title'])."';\n";
    $output .= '$conf[\'lang\'] = \''.addslashes($LC)."';\n";
    $output .= '$conf[\'license\'] = \''.addslashes($d['license'])."';\n";
    if($d['acl']){
        $output .= '$conf[\'useacl\'] = 1'.";\n";
        $output .= "\$conf['superuser'] = '@admin';\n";
    }
    if(!$d['allowreg']){
        $output .= '$conf[\'disableactions\'] = \'register\''.";\n";
    }
    $ok = $ok && fileWrite(DOKU_LOCAL.'local.php',$output);

    if ($d['acl']) {
        // hash the password
        $phash = new PassHash();
        $pass = $phash->hash_smd5($d['password']);

        // create users.auth.php
        // --- user:SMD5password:Real Name:email:groups,comma,seperated
        $output = join(":",array($d['superuser'], $pass, $d['fullname'], $d['email'], 'admin,user'));
        $output = @file_get_contents(DOKU_CONF.'users.auth.php.dist')."\n$output\n";
        $ok = $ok && fileWrite(DOKU_LOCAL.'users.auth.php', $output);

        // create acl.auth.php
        $output = <<<EOT
# acl.auth.php
# <?php exit()?>
# Don't modify the lines above
#
# Access Control Lists
#
# Auto-generated by install script
# Date: $now

EOT;
        if($d['policy'] == 2){
            $output .=  "*               @ALL          0\n";
            $output .=  "*               @user         8\n";
        }elseif($d['policy'] == 1){
            $output .=  "*               @ALL          1\n";
            $output .=  "*               @user         8\n";
        }else{
            $output .=  "*               @ALL          8\n";
        }
        $ok = $ok && fileWrite(DOKU_LOCAL.'acl.auth.php', $output);
    }

    // enable popularity submission
    if($d['pop']){
        @touch(DOKU_INC.'data/cache/autosubmit.txt');
    }

    // disable auth plugins til needed
    $output = <<<EOT
<?php
/*
 * Local plugin enable/disable settings
 *
 * Auto-generated by install script
 * Date: $now
 */

\$plugins['authad']    = 0;
\$plugins['authldap']  = 0;
\$plugins['authmysql'] = 0;
\$plugins['authpgsql'] = 0;

EOT;
    $ok = $ok && fileWrite(DOKU_LOCAL.'plugins.local.php', $output);

    return $ok;
}

/**
 * Write the given content to a file
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 *
 * @param string $filename
 * @param string $data
 * @return bool
 */
function fileWrite($filename, $data) {
    global $error;
    global $lang;

    if (($fp = @fopen($filename, 'wb')) === false) {
        $filename = str_replace($_SERVER['DOCUMENT_ROOT'],'{DOCUMENT_ROOT}/', $filename);
        $error[]  = sprintf($lang['i_writeerr'],$filename);
        return false;
    }

    if (!empty($data)) { fwrite($fp, $data);  }
    fclose($fp);
    return true;
}


/**
 * check installation dependent local config files and tests for a known
 * unmodified main config file
 *
 * @author      Chris Smith <chris@jalakai.co.uk>
 *
 * @return bool
 */
function check_configs(){
    global $error;
    global $lang;
    global $dokuwiki_hash;

    $ok = true;

    $config_files = array(
        'local' => DOKU_LOCAL.'local.php',
        'users' => DOKU_LOCAL.'users.auth.php',
        'auth'  => DOKU_LOCAL.'acl.auth.php'
    );

    // main dokuwiki config file (conf/dokuwiki.php) must not have been modified
    $installation_hash = md5(preg_replace("/(\015\012)|(\015)/","\012",
                             @file_get_contents(DOKU_CONF.'dokuwiki.php')));
    if (!in_array($installation_hash, $dokuwiki_hash)) {
        $error[] = sprintf($lang['i_badhash'],$installation_hash);
        $ok = false;
    }

    // configs shouldn't exist
    foreach ($config_files as $file) {
        if (file_exists($file) && filesize($file)) {
            $file    = str_replace($_SERVER['DOCUMENT_ROOT'],'{DOCUMENT_ROOT}/', $file);
            $error[] = sprintf($lang['i_confexists'],$file);
            $ok      = false;
        }
    }
    return $ok;
}


/**
 * Check other installation dir/file permission requirements
 *
 * @author      Chris Smith <chris@jalakai.co.uk>
 *
 * @return bool
 */
function check_permissions(){
    global $error;
    global $lang;

    $dirs = array(
        'conf'        => DOKU_LOCAL,
        'data'        => DOKU_INC.'data',
        'pages'       => DOKU_INC.'data/pages',
        'attic'       => DOKU_INC.'data/attic',
        'media'       => DOKU_INC.'data/media',
        'media_attic' => DOKU_INC.'data/media_attic',
        'media_meta'  => DOKU_INC.'data/media_meta',
        'meta'        => DOKU_INC.'data/meta',
        'cache'       => DOKU_INC.'data/cache',
        'locks'       => DOKU_INC.'data/locks',
        'index'       => DOKU_INC.'data/index',
        'tmp'         => DOKU_INC.'data/tmp'
    );

    $ok = true;
    foreach($dirs as $dir){
        if(!file_exists("$dir/.") || !is_writable($dir)){
            $dir     = str_replace($_SERVER['DOCUMENT_ROOT'],'{DOCUMENT_ROOT}', $dir);
            $error[] = sprintf($lang['i_permfail'],$dir);
            $ok      = false;
        }
    }
    return $ok;
}

/**
 * Check the availability of functions used in DokuWiki and the PHP version
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @return bool
 */
function check_functions(){
    global $error;
    global $lang;
    $ok = true;

    if(version_compare(phpversion(),'5.3.3','<')){
        $error[] = sprintf($lang['i_phpver'],phpversion(),'5.3.3');
        $ok = false;
    }

    if(ini_get('mbstring.func_overload') != 0){
        $error[] = $lang['i_mbfuncoverload'];
        $ok = false;
    }

    $funcs = explode(' ','addslashes call_user_func chmod copy fgets '.
                         'file file_exists fseek flush filesize ftell fopen '.
                         'glob header ignore_user_abort ini_get mail mkdir '.
                         'ob_start opendir parse_ini_file readfile realpath '.
                         'rename rmdir serialize session_start unlink usleep '.
                         'preg_replace file_get_contents htmlspecialchars_decode '.
                         'spl_autoload_register stream_select fsockopen pack');

    if (!function_exists('mb_substr')) {
        $funcs[] = 'utf8_encode';
        $funcs[] = 'utf8_decode';
    }

    foreach($funcs as $func){
        if(!function_exists($func)){
            $error[] = sprintf($lang['i_funcna'],$func);
            $ok = false;
        }
    }
    return $ok;
}

/**
 * Print language selection
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function langsel(){
    global $lang;
    global $LC;

    $dir = DOKU_INC.'inc/lang';
    $dh  = opendir($dir);
    if(!$dh) return;

    $langs = array();
    while (($file = readdir($dh)) !== false) {
        if(preg_match('/^[\._]/',$file)) continue;
        if(is_dir($dir.'/'.$file) && file_exists($dir.'/'.$file.'/lang.php')){
            $langs[] = $file;
        }
    }
    closedir($dh);
    sort($langs);

    echo '<form action="">';
    echo $lang['i_chooselang'];
    echo ': <select name="l" onchange="submit()">';
    foreach($langs as $l){
        $sel = ($l == $LC) ? 'selected="selected"' : '';
        echo '<option value="'.$l.'" '.$sel.'>'.$l.'</option>';
    }
    echo '</select> ';
    echo '<button type="submit">'.$lang['btn_update'].'</button>';
    echo '</form>';
}

/**
 * Print global error array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function print_errors(){
    global $error;
    if(!empty($error)) {
        echo '<ul>';
        foreach ($error as $err){
            echo "<li>$err</li>";
        }
        echo '</ul>';
    }
}
