<?php
/* phpFlickr Class 2.1.0
 * Written by Dan Coulter (dan@dancoulter.com)
 * Sourceforge Project Page: http://www.sourceforge.net/projects/phpflickr/
 * Released under GNU Lesser General Public License (http://www.gnu.org/copyleft/lgpl.html)
 * For more information about the class and upcoming tools and toys using it,
 * visit http://www.phpflickr.com/ or http://phpflickr.sourceforge.net
 *
 *     For installation instructions, open the README.txt file packaged with this
 *     class. If you don't have a copy, you can see it at:
 *     http://www.phpflickr.com/README.txt
 *
 *     Please submit all problems or questions to the Help Forum on my project page:
 *         http://sourceforge.net/forum/forum.php?forum_id=469652
 *
 */
if (session_id() == "") {
    @session_start();
}

// Decides which include path delimiter to use.  Windows should be using a semi-colon
// and everything else should be using a colon.  If this isn't working on your system,
// comment out this if statement and manually set the correct value into $path_delimiter.
if (strpos(__FILE__, ':') !== false) {
    $path_delimiter = ';';
} else {
    $path_delimiter = ':';
}

// This will add the packaged PEAR files into the include path for PHP, allowing you
// to use them transparently.  This will prefer officially installed PEAR files if you
// have them.  If you want to prefer the packaged files (there shouldn't be any reason
// to), swap the two elements around the $path_delimiter variable.  If you don't have
// the PEAR packages installed, you can leave this like it is and move on.

ini_set('include_path', ini_get('include_path') . $path_delimiter . dirname(__FILE__) . '/PEAR');

// If you have problems including the default PEAR install (like if your open_basedir
// setting doesn't allow you to include files outside of your web root), comment out
// the line above and uncomment the next line:

// ini_set('include_path', dirname(__FILE__) . '/PEAR' . $path_delimiter . ini_get('include_path'));

class phpFlickr {
    var $api_key;
    var $secret;
    var $REST = 'http://api.flickr.com/services/rest/';
    var $Upload = 'http://api.flickr.com/services/upload/';
    var $Replace = 'http://api.flickr.com/services/replace/';
    var $req;
    var $response;
    var $parsed_response;
    var $cache = false;
    var $cache_db = null;
    var $cache_table = null;
    var $cache_dir = null;
    var $cache_expire = null;
    var $die_on_error;
    var $error_code;
    Var $error_msg;
    var $token;
    var $php_version;

    /*
     * When your database cache table hits this many rows, a cleanup
     * will occur to get rid of all of the old rows and cleanup the
     * garbage in the table.  For most personal apps, 1000 rows should
     * be more than enough.  If your site gets hit by a lot of traffic
     * or you have a lot of disk space to spare, bump this number up.
     * You should try to set it high enough that the cleanup only
     * happens every once in a while, so this will depend on the growth
     * of your table.
     */
    var $max_cache_rows = 1000;

    function phpFlickr ($api_key, $secret = NULL, $die_on_error = false)
    {
        //The API Key must be set before any calls can be made.  You can
        //get your own at http://www.flickr.com/services/api/misc.api_keys.html
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->die_on_error = $die_on_error;
        $this->service = "flickr";

        //Find the PHP version and store it for future reference
        $this->php_version = explode("-", phpversion());
        $this->php_version = explode(".", $this->php_version[0]);

        //All calls to the API are done via the POST method using the PEAR::HTTP_Request package.
        require_once 'HTTP/Request.php';
        $this->req =& new HTTP_Request();
        $this->req->setMethod(HTTP_REQUEST_METHOD_POST);
    }

    function enableCache($type, $connection, $cache_expire = 600, $table = 'flickr_cache')
    {
        // Turns on caching.  $type must be either "db" (for database caching) or "fs" (for filesystem).
        // When using db, $connection must be a PEAR::DB connection string. Example:
        //      "mysql://user:password@server/database"
        // If the $table, doesn't exist, it will attempt to create it.
        // When using file system, caching, the $connection is the folder that the web server has write
        // access to. Use absolute paths for best results.  Relative paths may have unexpected behavior
        // when you include this.  They'll usually work, you'll just want to test them.
        if ($type == 'db') {
            require_once 'DB.php';
            $db =& DB::connect($connection);
            if (PEAR::isError($db)) {
                die($db->getMessage());
            }

            /*
             * If high performance is crucial, you can easily comment
             * out this query once you've created your database table.
             */

            $db->query("
                CREATE TABLE IF NOT EXISTS `$table` (
                    `request` CHAR( 35 ) NOT NULL ,
                    `response` MEDIUMTEXT NOT NULL ,
                    `expiration` DATETIME NOT NULL ,
                    INDEX ( `request` )
                ) TYPE = MYISAM");

            if ($db->getOne("SELECT COUNT(*) FROM $table") > $this->max_cache_rows) {
                $db->query("DELETE FROM $table WHERE expiration < DATE_SUB(NOW(), INTERVAL $cache_expire second)");
                $db->query('OPTIMIZE TABLE ' . $this->cache_table);
            }

            $this->cache = 'db';
            $this->cache_db = $db;
            $this->cache_table = $table;
        } elseif ($type == 'fs') {
            $this->cache = 'fs';
            $connection = realpath($connection);
            $this->cache_dir = $connection;
            if ($dir = opendir($this->cache_dir)) {
                while ($file = readdir($dir)) {
                    if (substr($file, -6) == '.cache' && ((filemtime($this->cache_dir . '/' . $file) + $cache_expire) < time()) ) {
                        unlink($this->cache_dir . '/' . $file);
                    }
                }
            }
        }
        $this->cache_expire = $cache_expire;
    }

    function getCached ($request)
    {
        //Checks the database or filesystem for a cached result to the request.
        //If there is no cache result, it returns a value of false. If it finds one,
        //it returns the unparsed XML.
        $reqhash = md5(serialize($request));
        if ($this->cache == 'db') {
            $result = $this->cache_db->getOne("SELECT response FROM " . $this->cache_table . " WHERE request = ? AND DATE_SUB(NOW(), INTERVAL " . (int) $this->cache_expire . " SECOND) < expiration", $reqhash);
            if (!empty($result)) {
                return $result;
            }
        } elseif ($this->cache == 'fs') {
            $file = $this->cache_dir . '/' . $reqhash . '.cache';
            if (file_exists($file)) {
				if ($this->php_version[0] > 4 || ($this->php_version[0] == 4 && $this->php_version[1] >= 3)) {
					return file_get_contents($file);
				} else {
					return implode('', file($file));
				}
            }
        }
        return false;
    }

    function cache ($request, $response)
    {
        //Caches the unparsed XML of a request.
        $reqhash = md5(serialize($request));
        if ($this->cache == 'db') {
            //$this->cache_db->query("DELETE FROM $this->cache_table WHERE request = '$reqhash'");
            if ($this->cache_db->getOne("SELECT COUNT(*) FROM {$this->cache_table} WHERE request = '$reqhash'")) {
                $sql = "UPDATE " . $this->cache_table . " SET response = ?, expiration = ? WHERE request = ?";
                $this->cache_db->query($sql, array($response, strftime("%Y-%m-%d %H:%M:%S"), $reqhash));
            } else {
                $sql = "INSERT INTO " . $this->cache_table . " (request, response, expiration) VALUES ('$reqhash', '" . str_replace("'", "''", $response) . "', '" . strftime("%Y-%m-%d %H:%M:%S") . "')";
                $this->cache_db->query($sql);
            }
        } elseif ($this->cache == "fs") {
            $file = $this->cache_dir . "/" . $reqhash . ".cache";
            $fstream = fopen($file, "w");
            $result = fwrite($fstream,$response);
            fclose($fstream);
            return $result;
        }
        return false;
    }

    function request ($command, $args = array(), $nocache = false)
    {
        //Sends a request to Flickr's REST endpoint via POST.
        $this->req->setURL($this->REST);
        $this->req->clearPostData();
        if (substr($command,0,7) != "flickr.") {
            $command = "flickr." . $command;
        }

        //Process arguments, including method and login data.
        $args = array_merge(array("method" => $command, "format" => "php_serial", "api_key" => $this->api_key), $args);
        if (!empty($this->token)) {
            $args = array_merge($args, array("auth_token" => $this->token));
        } elseif (!empty($_SESSION['phpFlickr_auth_token'])) {
            $args = array_merge($args, array("auth_token" => $_SESSION['phpFlickr_auth_token']));
        }
        ksort($args);
        $auth_sig = "";
        if (!($this->response = $this->getCached($args)) || $nocache) {
            foreach ($args as $key => $data) {
                $auth_sig .= $key . $data;
                $this->req->addPostData($key, $data);
            }
            if (!empty($this->secret)) {
                $api_sig = md5($this->secret . $auth_sig);
                $this->req->addPostData("api_sig", $api_sig);
            }

            //Send Requests
            if ($this->req->sendRequest()) {
                $this->response = $this->req->getResponseBody();
                $this->cache($args, $this->response);
            } else {
                die("There has been a problem sending your command to the server.");
            }
        }
        /*
         * Uncomment this line (and comment out the next one) if you're doing large queries
         * and you're concerned about time.  This will, however, change the structure of
         * the result, so be sure that you look at the results.
         */
        //$this->parsed_response = unserialize($this->response);
        $this->parsed_response = $this->clean_text_nodes(unserialize($this->response));
        if ($this->parsed_response['stat'] == 'fail') {
            if ($this->die_on_error) die("The Flickr API returned the following error: #{$this->parsed_response['code']} - {$this->parsed_response['message']}");
            else {
                $this->error_code = $this->parsed_response['code'];
                $this->error_msg = $this->parsed_response['message'];
                $this->parsed_response = false;
            }
        } else {
            $this->error_code = false;
            $this->error_msg = false;
        }
        return $this->response;
    }

    function clean_text_nodes($arr) {
        if (!is_array($arr)) {
            return $arr;
        } elseif (count($arr) == 0) {
            return $arr;
        } elseif (count($arr) == 1 && array_key_exists('_content', $arr)) {
            return $arr['_content'];
        } else {
            foreach ($arr as $key => $element) {
                $arr[$key] = $this->clean_text_nodes($element);
            }
            return($arr);
        }
    }

    function setToken($token)
    {
        // Sets an authentication token to use instead of the session variable
        $this->token = $token;
    }

    function setProxy($server, $port)
    {
        // Sets the proxy for all phpFlickr calls.
        $this->req->setProxy($server, $port);
    }

    function getErrorCode()
    {
		// Returns the error code of the last call.  If the last call did not
		// return an error. This will return a false boolean.
		return $this->error_code;
    }

    function getErrorMsg()
    {
		// Returns the error message of the last call.  If the last call did not
		// return an error. This will return a false boolean.
		return $this->error_msg;
    }

    /* These functions are front ends for the flickr calls */

    function buildPhotoURL ($photo, $size = "Medium")
    {
        //receives an array (can use the individual photo data returned
        //from an API call) and returns a URL (doesn't mean that the
        //file size exists)
        $sizes = array(
            "square" => "_s",
            "thumbnail" => "_t",
            "small" => "_m",
            "medium" => "",
            "large" => "_b",
            "original" => "_o"
        );
        
        $size = strtolower($size);
        if (!array_key_exists($size, $sizes)) {
            $size = "medium";
        }
        
        if ($size == "original") {
            $url = "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['originalsecret'] . "_o" . "." . $photo['originalformat'];
        } else {
            $url = "http://farm" . $photo['farm'] . ".static.flickr.com/" . $photo['server'] . "/" . $photo['id'] . "_" . $photo['secret'] . $sizes[$size] . ".jpg";
        }
        return $url;
    }

    function getFriendlyGeodata($lat, $lon) {
        /* I've added this method to get the friendly geodata (i.e. 'in New York, NY') that the
         * website provides, but isn't available in the API. I'm providing this service as long
         * as it doesn't flood my server with requests and crash it all the time.
         */
        return unserialize(file_get_contents('http://phpflickr.com/geodata/?format=php&lat=' . $lat . '&lon=' . $lon));
    }

    function sync_upload ($photo, $title = null, $description = null, $tags = null, $is_public = null, $is_friend = null, $is_family = null) {
        $upload_req =& new HTTP_Request();
        $upload_req->setMethod(HTTP_REQUEST_METHOD_POST);


        $upload_req->setURL($this->Upload);
        $upload_req->clearPostData();

        //Process arguments, including method and login data.
        $args = array("api_key" => $this->api_key, "title" => $title, "description" => $description, "tags" => $tags, "is_public" => $is_public, "is_friend" => $is_friend, "is_family" => $is_family);
        if (!empty($this->email)) {
            $args = array_merge($args, array("email" => $this->email));
        }
        if (!empty($this->password)) {
            $args = array_merge($args, array("password" => $this->password));
        }
        if (!empty($this->token)) {
            $args = array_merge($args, array("auth_token" => $this->token));
        } elseif (!empty($_SESSION['phpFlickr_auth_token'])) {
            $args = array_merge($args, array("auth_token" => $_SESSION['phpFlickr_auth_token']));
        }

        ksort($args);
        $auth_sig = "";
        foreach ($args as $key => $data) {
            if ($data !== null) {
                $auth_sig .= $key . $data;
                $upload_req->addPostData($key, $data);
            }
        }
        if (!empty($this->secret)) {
            $api_sig = md5($this->secret . $auth_sig);
            $upload_req->addPostData("api_sig", $api_sig);
        }

        $photo = realpath($photo);

        $result = $upload_req->addFile("photo", $photo);

        if (PEAR::isError($result)) {
            die($result->getMessage());
        }

        //Send Requests
        if ($upload_req->sendRequest()) {
            $this->response = $upload_req->getResponseBody();
        } else {
            die("There has been a problem sending your command to the server.");
        }

        $rsp = explode("\n", $this->response);
        foreach ($rsp as $line) {
            if (ereg('<err code="([0-9]+)" msg="(.*)"', $line, $match)) {
                if ($this->die_on_error)
                    die("The Flickr API returned the following error: #{$match[1]} - {$match[2]}");
                else {
                    $this->error_code = $match[1];
                    $this->error_msg = $match[2];
                    $this->parsed_response = false;
                    return false;
                }
            } elseif (ereg("<photoid>(.*)</photoid>", $line, $match)) {
                $this->error_code = false;
                $this->error_msg = false;
                return $match[1];
            }
        }
    }

    function async_upload ($photo, $title = null, $description = null, $tags = null, $is_public = null, $is_friend = null, $is_family = null) {
        $upload_req =& new HTTP_Request();
        $upload_req->setMethod(HTTP_REQUEST_METHOD_POST);

        $upload_req->setURL($this->Upload);
        $upload_req->clearPostData();

        //Process arguments, including method and login data.
        $args = array("async" => 1, "api_key" => $this->api_key, "title" => $title, "description" => $description, "tags" => $tags, "is_public" => $is_public, "is_friend" => $is_friend, "is_family" => $is_family);
        if (!empty($this->email)) {
            $args = array_merge($args, array("email" => $this->email));
        }
        if (!empty($this->password)) {
            $args = array_merge($args, array("password" => $this->password));
        }
        if (!empty($this->token)) {
            $args = array_merge($args, array("auth_token" => $this->token));
        } elseif (!empty($_SESSION['phpFlickr_auth_token'])) {
            $args = array_merge($args, array("auth_token" => $_SESSION['phpFlickr_auth_token']));
        }

        ksort($args);
        $auth_sig = "";
        foreach ($args as $key => $data) {
            if ($data !== null) {
                $auth_sig .= $key . $data;
                $upload_req->addPostData($key, $data);
            }
        }
        if (!empty($this->secret)) {
            $api_sig = md5($this->secret . $auth_sig);
            $upload_req->addPostData("api_sig", $api_sig);
        }

        $photo = realpath($photo);

        $result = $upload_req->addFile("photo", $photo);

        if (PEAR::isError($result)) {
            die($result->getMessage());
        }

        //Send Requests
        if ($upload_req->sendRequest()) {
            $this->response = $upload_req->getResponseBody();
        } else {
            die("There has been a problem sending your command to the server.");
        }

        $rsp = explode("\n", $this->response);
        foreach ($rsp as $line) {
            if (ereg('<err code="([0-9]+)" msg="(.*)"', $line, $match)) {
                if ($this->die_on_error)
                    die("The Flickr API returned the following error: #{$match[1]} - {$match[2]}");
                else {
                    $this->error_code = $match[1];
                    $this->error_msg = $match[2];
                    $this->parsed_response = false;
                    return false;
                }
            } elseif (ereg("<ticketid>(.*)</", $line, $match)) {
                $this->error_code = false;
                $this->error_msg = false;
                return $match[1];
            }
        }
    }

    // Interface for new replace API method.
    function replace ($photo, $photo_id, $async = null) {
        $upload_req =& new HTTP_Request();
        $upload_req->setMethod(HTTP_REQUEST_METHOD_POST);

        $upload_req->setURL($this->Replace);
        $upload_req->clearPostData();

        //Process arguments, including method and login data.
        $args = array("api_key" => $this->api_key, "photo_id" => $photo_id, "async" => $async);
        if (!empty($this->email)) {
            $args = array_merge($args, array("email" => $this->email));
        }
        if (!empty($this->password)) {
            $args = array_merge($args, array("password" => $this->password));
        }
        if (!empty($this->token)) {
            $args = array_merge($args, array("auth_token" => $this->token));
        } elseif (!empty($_SESSION['phpFlickr_auth_token'])) {
            $args = array_merge($args, array("auth_token" => $_SESSION['phpFlickr_auth_token']));
        }

        ksort($args);
        $auth_sig = "";
        foreach ($args as $key => $data) {
            if ($data !== null) {
                $auth_sig .= $key . $data;
                $upload_req->addPostData($key, $data);
            }
        }
        if (!empty($this->secret)) {
            $api_sig = md5($this->secret . $auth_sig);
            $upload_req->addPostData("api_sig", $api_sig);
        }

        $photo = realpath($photo);

        $result = $upload_req->addFile("photo", $photo);

        if (PEAR::isError($result)) {
            die($result->getMessage());
        }

        //Send Requests
        if ($upload_req->sendRequest()) {
            $this->response = $upload_req->getResponseBody();
        } else {
            die("There has been a problem sending your command to the server.");
        }
        if ($async == 1)
            $find = 'ticketid';
         else
            $find = 'photoid';

        $rsp = explode("\n", $this->response);
        foreach ($rsp as $line) {
            if (ereg('<err code="([0-9]+)" msg="(.*)"', $line, $match)) {
                if ($this->die_on_error)
                    die("The Flickr API returned the following error: #{$match[1]} - {$match[2]}");
                else {
                    $this->error_code = $match[1];
                    $this->error_msg = $match[2];
                    $this->parsed_response = false;
                    return false;
                }
            } elseif (ereg("<" . $find . ">(.*)</", $line, $match)) {
                $this->error_code = false;
                $this->error_msg = false;
                return $match[1];
            }
        }
    }

    function auth ($perms = "read", $remember_uri = true)
    {
        // Redirects to Flickr's authentication piece if there is no valid token.
        // If remember_uri is set to false, the callback script (included) will
        // redirect to its default page.

        if (empty($_SESSION['phpFlickr_auth_token']) && empty($this->token)) {
            if ($remember_uri) {
                $redirect = $_SERVER['REQUEST_URI'];
            }
            $api_sig = md5($this->secret . "api_key" . $this->api_key . "extra" . $redirect . "perms" . $perms);
			if ($this->service == "23") {
				header("Location: http://www.23hq.com/services/auth/?api_key=" . $this->api_key . "&extra=" . $redirect . "&perms=" . $perms . "&api_sig=". $api_sig);
			} else {
				header("Location: http://www.flickr.com/services/auth/?api_key=" . $this->api_key . "&extra=" . $redirect . "&perms=" . $perms . "&api_sig=". $api_sig);
			}
            exit;
        } else {
            $tmp = $this->die_on_error;
            $this->die_on_error = false;
            $rsp = $this->auth_checkToken();
            if ($this->error_code !== false) {
                unset($_SESSION['phpFlickr_auth_token']);
                $this->auth($perms, $remember_uri);
            }
            $this->die_on_error = $tmp;
            return $rsp['perms'];
        }
    }

    /*******************************

    To use the phpFlickr::call method, pass a string containing the API method you want
    to use and an associative array of arguments.  For example:
        $result = $f->call("flickr.photos.comments.getList", array("photo_id"=>'34952612'));
    This method will allow you to make calls to arbitrary methods that haven't been
    implemented in phpFlickr yet.

    *******************************/

    function call($method, $arguments)
    {
        $this->request($method, $arguments);
        return $this->parsed_response;
    }

    /*
        These functions are the direct implementations of flickr calls.
        For method documentation, including arguments, visit the address
        included in a comment in the function.
    */

    /* Activity methods */
    function activity_userComments ($per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.activity.userComments.html */
        $this->request('flickr.activity.userComments', array("per_page" => $per_page, "page" => $page));
        return $this->parsed_response ? $this->parsed_response['items']['item'] : false;
    }

    function activity_userPhotos ($timeframe = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.activity.userPhotos.html */
        $this->request('flickr.activity.userPhotos', array("timeframe" => $timeframe, "per_page" => $per_page, "page" => $page));
        return $this->parsed_response ? $this->parsed_response['items']['item'] : false;
    }

    /* Authentication methods */
    function auth_checkToken ()
    {
        /* http://www.flickr.com/services/api/flickr.auth.checkToken.html */
        $this->request('flickr.auth.checkToken');
        return $this->parsed_response ? $this->parsed_response['auth'] : false;
    }

    function auth_getFrob ()
    {
        /* http://www.flickr.com/services/api/flickr.auth.getFrob.html */
        $this->request('flickr.auth.getFrob');
        return $this->parsed_response ? $this->parsed_response['frob'] : false;
    }

    function auth_getFullToken ($mini_token)
    {
        /* http://www.flickr.com/services/api/flickr.auth.getFullToken.html */
        $this->request('flickr.auth.getFullToken', array('mini_token'=>$mini_token));
        return $this->parsed_response ? $this->parsed_response['auth'] : false;
    }

    function auth_getToken ($frob)
    {
        /* http://www.flickr.com/services/api/flickr.auth.getToken.html */
        $this->request('flickr.auth.getToken', array('frob'=>$frob));
        session_register('phpFlickr_auth_token');
        $_SESSION['phpFlickr_auth_token'] = $this->parsed_response['auth']['token'];
        return $this->parsed_response ? $this->parsed_response['auth'] : false;
    }

    /* Blogs methods */
    function blogs_getList ()
    {
        /* http://www.flickr.com/services/api/flickr.blogs.getList.html */
        $this->request('flickr.blogs.getList');
        return $this->parsed_response ? $this->parsed_response['blogs']['blog'] : false;
    }

    function blogs_postPhoto($blog_id, $photo_id, $title, $description, $blog_password = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.blogs.postPhoto.html */
        $this->request('flickr.blogs.postPhoto', array('blog_id'=>$blog_id, 'photo_id'=>$photo_id, 'title'=>$title, 'description'=>$description, 'blog_password'=>$blog_password), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Contacts Methods */
    function contacts_getList ($filter = NULL, $page = NULL, $per_page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.contacts.getList.html */
        $this->request('flickr.contacts.getList', array('filter'=>$filter, 'page'=>$page, 'per_page'=>$per_page));
        return $this->parsed_response ? $this->parsed_response['contacts'] : false;
    }

    function contacts_getPublicList($user_id, $page = NULL, $per_page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.contacts.getPublicList.html */
        $this->request('flickr.contacts.getPublicList', array('user_id'=>$user_id, 'page'=>$page, 'per_page'=>$per_page));
        return $this->parsed_response ? $this->parsed_response['contacts'] : false;
    }

    /* Favorites Methods */
    function favorites_add ($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.favorites.add.html */
        $this->request('flickr.favorites.add', array('photo_id'=>$photo_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function favorites_getList($user_id = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.favorites.getList.html */
        if (is_array($extras)) { $extras = implode(",", $extras); }
        $this->request("flickr.favorites.getList", array("user_id"=>$user_id, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function favorites_getPublicList($user_id = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.favorites.getPublicList.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }
        $this->request("flickr.favorites.getPublicList", array("user_id"=>$user_id, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function favorites_remove($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.favorites.remove.html */
        $this->request("flickr.favorites.remove", array("photo_id"=>$photo_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Groups Methods */
    function groups_browse ($cat_id = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.groups.browse.html */
        $this->request("flickr.groups.browse", array("cat_id"=>$cat_id));
        return $this->parsed_response ? $this->parsed_response['category'] : false;
    }

    function groups_getInfo ($group_id)
    {
        /* http://www.flickr.com/services/api/flickr.groups.getInfo.html */
        $this->request("flickr.groups.getInfo", array("group_id"=>$group_id));
        return $this->parsed_response ? $this->parsed_response['group'] : false;
    }

	function groups_search ($text, $per_page=NULL, $page=NULL)
	{
		/* http://www.flickr.com/services/api/flickr.groups.search.html */
		$this->request("flickr.groups.search", array("text"=>$text,"per_page"=>$per_page,"page"=>$page));
        return $this->parsed_response ? $this->parsed_response['groups'] : false;
	}

    /* Groups Pools Methods */
    function groups_pools_add ($photo_id, $group_id)
    {
        /* http://www.flickr.com/services/api/flickr.groups.pools.add.html */
        $this->request("flickr.groups.pools.add", array("photo_id"=>$photo_id, "group_id"=>$group_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function groups_pools_getContext ($photo_id, $group_id)
    {
        /* http://www.flickr.com/services/api/flickr.groups.pools.getContext.html */
        $this->request("flickr.groups.pools.getContext", array("photo_id"=>$photo_id, "group_id"=>$group_id));
        return $this->parsed_response ? $this->parsed_response : false;
    }

    function groups_pools_getGroups ($page = NULL, $per_page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.groups.pools.getGroups.html */
        $this->request("flickr.groups.pools.getGroups", array('page'=>$page, 'per_page'=>$per_page));
        return $this->parsed_response ? $this->parsed_response['groups'] : false;
    }

    function groups_pools_getPhotos ($group_id, $tags = NULL, $user_id = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
    {
		/* http://www.flickr.com/services/api/flickr.groups.pools.getPhotos.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }
        $this->request("flickr.groups.pools.getPhotos", array("group_id"=>$group_id, "tags"=>$tags, "user_id"=>$user_id, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function groups_pools_remove ($photo_id, $group_id)
    {
        /* http://www.flickr.com/services/api/flickr.groups.pools.remove.html */
        $this->request("flickr.groups.pools.remove", array("photo_id"=>$photo_id, "group_id"=>$group_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Interestingness methods */
	function interestingness_getList($date = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
	{
        /* http://www.flickr.com/services/api/flickr.interestingness.getList.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }

        $this->request("flickr.interestingness.getList", array("date"=>$date, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    /* People methods */
    function people_findByEmail ($find_email)
    {
        /* http://www.flickr.com/services/api/flickr.people.findByEmail.html */
        $this->request("flickr.people.findByEmail", array("find_email"=>$find_email));
        return $this->parsed_response ? $this->parsed_response['user'] : false;
    }

    function people_findByUsername ($username)
    {
        /* http://www.flickr.com/services/api/flickr.people.findByUsername.html */
        $this->request("flickr.people.findByUsername", array("username"=>$username));
        return $this->parsed_response ? $this->parsed_response['user'] : false;
    }

    function people_getInfo($user_id)
    {
        /* http://www.flickr.com/services/api/flickr.people.getInfo.html */
        $this->request("flickr.people.getInfo", array("user_id"=>$user_id));
        return $this->parsed_response ? $this->parsed_response['person'] : false;
    }

    function people_getPublicGroups($user_id)
    {
        /* http://www.flickr.com/services/api/flickr.people.getPublicGroups.html */
        $this->request("flickr.people.getPublicGroups", array("user_id"=>$user_id));
        return $this->parsed_response ? $this->parsed_response['groups']['group'] : false;
    }

    function people_getPublicPhotos($user_id, $extras = NULL, $per_page = NULL, $page = NULL) {
        /* http://www.flickr.com/services/api/flickr.people.getPublicPhotos.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }

        $this->request("flickr.people.getPublicPhotos", array("user_id"=>$user_id, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function people_getUploadStatus()
    {
        /* http://www.flickr.com/services/api/flickr.people.getUploadStatus.html */
        /* Requires Authentication */
        $this->request("flickr.people.getUploadStatus");
        return $this->parsed_response ? $this->parsed_response['user'] : false;
    }


    /* Photos Methods */
    function photos_addTags ($photo_id, $tags)
    {
        /* http://www.flickr.com/services/api/flickr.photos.addTags.html */
        $this->request("flickr.photos.addTags", array("photo_id"=>$photo_id, "tags"=>$tags), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_delete($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.delete.html */
        $this->request("flickr.photos.delete", array("photo_id"=>$photo_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_getAllContexts ($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getAllContexts.html */
        $this->request("flickr.photos.getAllContexts", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response : false;
    }

    function photos_getContactsPhotos ($count = NULL, $just_friends = NULL, $single_photo = NULL, $include_self = NULL, $extras = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getContactsPhotos.html */
        $this->request("flickr.photos.getContactsPhotos", array("count"=>$count, "just_friends"=>$just_friends, "single_photo"=>$single_photo, "include_self"=>$include_self, "extras"=>$extras));
        return $this->parsed_response ? $this->parsed_response['photos']['photo'] : false;
    }

    function photos_getContactsPublicPhotos ($user_id, $count = NULL, $just_friends = NULL, $single_photo = NULL, $include_self = NULL, $extras = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getContactsPublicPhotos.html */
        $this->request("flickr.photos.getContactsPublicPhotos", array("user_id"=>$user_id, "count"=>$count, "just_friends"=>$just_friends, "single_photo"=>$single_photo, "include_self"=>$include_self, "extras"=>$extras));
        return $this->parsed_response ? $this->parsed_response['photos']['photo'] : false;
    }

    function photos_getContext ($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getContext.html */
        $this->request("flickr.photos.getContext", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response : false;
    }

    function photos_getCounts ($dates = NULL, $taken_dates = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getCounts.html */
        $this->request("flickr.photos.getCounts", array("dates"=>$dates, "taken_dates"=>$taken_dates));
        return $this->parsed_response ? $this->parsed_response['photocounts']['photocount'] : false;
    }

    function photos_getExif ($photo_id, $secret = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getExif.html */
        $this->request("flickr.photos.getExif", array("photo_id"=>$photo_id, "secret"=>$secret));
        return $this->parsed_response ? $this->parsed_response['photo'] : false;
    }
    
    function photos_getFavorites($photo_id, $page = NULL, $per_page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getFavorites.html */
        $this->request("flickr.photos.getFavorites", array("photo_id"=>$photo_id, "page"=>$page, "per_page"=>$per_page));
        return $this->parsed_response ? $this->parsed_response['photo'] : false;
    }

    function photos_getInfo($photo_id, $secret = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getInfo.html */
        $this->request("flickr.photos.getInfo", array("photo_id"=>$photo_id, "secret"=>$secret));
        return $this->parsed_response ? $this->parsed_response['photo'] : false;
    }

    function photos_getNotInSet($min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getNotInSet.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }
        $this->request("flickr.photos.getNotInSet", array("min_upload_date"=>$min_upload_date, "max_upload_date"=>$max_upload_date, "min_taken_date"=>$min_taken_date, "max_taken_date"=>$max_taken_date, "privacy_filter"=>$privacy_filter, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_getPerms($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getPerms.html */
        $this->request("flickr.photos.getPerms", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response['perms'] : false;
    }

    function photos_getRecent($extras = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getRecent.html */

        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }
        $this->request("flickr.photos.getRecent", array("extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_getSizes($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getSizes.html */
        $this->request("flickr.photos.getSizes", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response['sizes']['size'] : false;
    }

    function photos_getUntagged($min_upload_date = NULL, $max_upload_date = NULL, $min_taken_date = NULL, $max_taken_date = NULL, $privacy_filter = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getUntagged.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }
        $this->request("flickr.photos.getUntagged", array("min_upload_date"=>$min_upload_date, "max_upload_date"=>$max_upload_date, "min_taken_date"=>$min_taken_date, "max_taken_date"=>$max_taken_date, "privacy_filter"=>$privacy_filter, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_getWithGeoData($args = NULL) {
        /* See the documentation included with the photos_search() function.
         * I'm using the same style of arguments for this function. The only
         * difference here is that this doesn't require any arguments. The
         * flickr.photos.search method requires at least one search parameter.
         */
        /* http://www.flickr.com/services/api/flickr.photos.getWithGeoData.html */
        if (is_null($args)) {
            $args = array();
        }
        $this->request("flickr.photos.getWithGeoData", $args);
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_getWithoutGeoData($args = NULL) {
        /* See the documentation included with the photos_search() function.
         * I'm using the same style of arguments for this function. The only
         * difference here is that this doesn't require any arguments. The
         * flickr.photos.search method requires at least one search parameter.
         */
        /* http://www.flickr.com/services/api/flickr.photos.getWithoutGeoData.html */
        if (is_null($args)) {
            $args = array();
        }
        $this->request("flickr.photos.getWithoutGeoData", $args);
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_recentlyUpdated($min_date = NULL, $extras = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.getUntagged.html */
        if (is_array($extras)) {
            $extras = implode(",", $extras);
        }
        $this->request("flickr.photos.recentlyUpdated", array("min_date"=>$min_date, "extras"=>$extras, "per_page"=>$per_page, "page"=>$page));
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_removeTag($tag_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.removeTag.html */
        $this->request("flickr.photos.removeTag", array("tag_id"=>$tag_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_search($args)
    {
        /* This function strays from the method of arguments that I've
         * used in the other functions for the fact that there are just
         * so many arguments to this API method. What you'll need to do
         * is pass an associative array to the function containing the
         * arguments you want to pass to the API.  For example:
         *   $photos = $f->photos_search(array("tags"=>"brown,cow", "tag_mode"=>"any"));
         * This will return photos tagged with either "brown" or "cow"
         * or both. See the API documentation (link below) for a full
         * list of arguments.
         */

        /* http://www.flickr.com/services/api/flickr.photos.search.html */
        $this->request("flickr.photos.search", $args);
        return $this->parsed_response ? $this->parsed_response['photos'] : false;
    }

    function photos_setDates($photo_id, $date_posted = NULL, $date_taken = NULL, $date_taken_granularity = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.setDates.html */
        $this->request("flickr.photos.setDates", array("photo_id"=>$photo_id, "date_posted"=>$date_posted, "date_taken"=>$date_taken, "date_taken_granularity"=>$date_taken_granularity), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_setMeta($photo_id, $title, $description)
    {
        /* http://www.flickr.com/services/api/flickr.photos.setMeta.html */
        $this->request("flickr.photos.setMeta", array("photo_id"=>$photo_id, "title"=>$title, "description"=>$description), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_setPerms($photo_id, $is_public, $is_friend, $is_family, $perm_comment, $perm_addmeta)
    {
        /* http://www.flickr.com/services/api/flickr.photos.setPerms.html */
        $this->request("flickr.photos.setPerms", array("photo_id"=>$photo_id, "is_public"=>$is_public, "is_friend"=>$is_friend, "is_family"=>$is_family, "perm_comment"=>$perm_comment, "perm_addmeta"=>$perm_addmeta), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_setTags($photo_id, $tags)
    {
        /* http://www.flickr.com/services/api/flickr.photos.setTags.html */
        $this->request("flickr.photos.setTags", array("photo_id"=>$photo_id, "tags"=>$tags), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Photos - Comments Methods */
    function photos_comments_addComment($photo_id, $comment_text) {
        /* http://www.flickr.com/services/api/flickr.photos.comments.addComment.html */
        $this->request("flickr.photos.comments.addComment", array("photo_id" => $photo_id, "comment_text"=>$comment_text), TRUE);
        return $this->parsed_response ? $this->parsed_response['comment'] : false;
    }

    function photos_comments_deleteComment($comment_id) {
        /* http://www.flickr.com/services/api/flickr.photos.comments.deleteComment.html */
        $this->request("flickr.photos.comments.deleteComment", array("comment_id" => $comment_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_comments_editComment($comment_id, $comment_text) {
        /* http://www.flickr.com/services/api/flickr.photos.comments.editComment.html */
        $this->request("flickr.photos.comments.editComment", array("comment_id" => $comment_id, "comment_text"=>$comment_text), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_comments_getList($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.comments.getList.html */
        $this->request("flickr.photos.comments.getList", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response['comments'] : false;
    }

    /* Photos - Geo Methods */
    function photos_geo_getLocation($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.geo.getLocation.html */
        $this->request("flickr.photos.geo.getLocation", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response['photo'] : false;
    }

    function photos_geo_getPerms($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.geo.getPerms.html */
        $this->request("flickr.photos.geo.getPerms", array("photo_id"=>$photo_id));
        return $this->parsed_response ? $this->parsed_response['perms'] : false;
    }

    function photos_geo_removeLocation($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.geo.removeLocation.html */
        $this->request("flickr.photos.geo.removeLocation", array("photo_id"=>$photo_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_geo_setLocation($photo_id, $lat, $lon, $accuracy = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photos.geo.setLocation.html */
        $this->request("flickr.photos.geo.setLocation", array("photo_id"=>$photo_id, "lat"=>$lat, "lon"=>$lon, "accuracy"=>$accuracy), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_geo_setPerms($photo_id, $is_public, $is_contact, $is_friend, $is_family)
    {
        /* http://www.flickr.com/services/api/flickr.photos.geo.setPerms.html */
        $this->request("flickr.photos.geo.setPerms", array("photo_id"=>$photo_id, "is_public"=>$is_public, "is_contact"=>$is_contact, "is_friend"=>$is_friend, "is_family"=>$is_family), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Photos - Licenses Methods */
    function photos_licenses_getInfo()
    {
        /* http://www.flickr.com/services/api/flickr.photos.licenses.getInfo.html */
        $this->request("flickr.photos.licenses.getInfo");
        return $this->parsed_response ? $this->parsed_response['licenses']['license'] : false;
    }

    function photos_licenses_setLicense($photo_id, $license_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.licenses.setLicense.html */
        /* Requires Authentication */
        $this->request("flickr.photos.licenses.setLicense", array("photo_id"=>$photo_id, "license_id"=>$license_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Photos - Notes Methods */
    function photos_notes_add($photo_id, $note_x, $note_y, $note_w, $note_h, $note_text)
    {
        /* http://www.flickr.com/services/api/flickr.photos.notes.add.html */
        $this->request("flickr.photos.notes.add", array("photo_id" => $photo_id, "note_x" => $note_x, "note_y" => $note_y, "note_w" => $note_w, "note_h" => $note_h, "note_text" => $note_text), TRUE);
        return $this->parsed_response ? $this->parsed_response['note'] : false;
    }

    function photos_notes_delete($note_id)
    {
        /* http://www.flickr.com/services/api/flickr.photos.notes.delete.html */
        $this->request("flickr.photos.notes.delete", array("note_id" => $note_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photos_notes_edit($note_id, $note_x, $note_y, $note_w, $note_h, $note_text)
    {
        /* http://www.flickr.com/services/api/flickr.photos.notes.edit.html */
        $this->request("flickr.photos.notes.edit", array("note_id" => $note_id, "note_x" => $note_x, "note_y" => $note_y, "note_w" => $note_w, "note_h" => $note_h, "note_text" => $note_text), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Photos - Transform Methods */
    function photos_transform_rotate($photo_id, $degrees)
    {
        /* http://www.flickr.com/services/api/flickr.photos.transform.rotate.html */
        $this->request("flickr.photos.transform.rotate", array("photo_id" => $photo_id, "degrees" => $degrees), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Photos - Upload Methods */
    function photos_upload_checkTickets($tickets)
    {
        /* http://www.flickr.com/services/api/flickr.photos.upload.checkTickets.html */
        if (is_array($tickets)) {
            $tickets = implode(",", $tickets);
        }
        $this->request("flickr.photos.upload.checkTickets", array("tickets" => $tickets), TRUE);
        return $this->parsed_response ? $this->parsed_response['uploader']['ticket'] : false;
    }

    /* Photosets Methods */
    function photosets_addPhoto($photoset_id, $photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.addPhoto.html */
        $this->request("flickr.photosets.addPhoto", array("photoset_id" => $photoset_id, "photo_id" => $photo_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_create($title, $description, $primary_photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.create.html */
        $this->request("flickr.photosets.create", array("title" => $title, "primary_photo_id" => $primary_photo_id, "description" => $description), TRUE);
        return $this->parsed_response ? $this->parsed_response['photoset'] : false;
    }

    function photosets_delete($photoset_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.delete.html */
        $this->request("flickr.photosets.delete", array("photoset_id" => $photoset_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_editMeta($photoset_id, $title, $description = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.editMeta.html */
        $this->request("flickr.photosets.editMeta", array("photoset_id" => $photoset_id, "title" => $title, "description" => $description), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_editPhotos($photoset_id, $primary_photo_id, $photo_ids)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.editPhotos.html */
        $this->request("flickr.photosets.editPhotos", array("photoset_id" => $photoset_id, "primary_photo_id" => $primary_photo_id, "photo_ids" => $photo_ids), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_getContext($photo_id, $photoset_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.getContext.html */
        $this->request("flickr.photosets.getContext", array("photo_id" => $photo_id, "photoset_id" => $photoset_id));
        return $this->parsed_response ? $this->parsed_response : false;
    }

    function photosets_getInfo($photoset_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.getInfo.html */
        $this->request("flickr.photosets.getInfo", array("photoset_id" => $photoset_id));
        return $this->parsed_response ? $this->parsed_response['photoset'] : false;
    }

    function photosets_getList($user_id = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.getList.html */
        $this->request("flickr.photosets.getList", array("user_id" => $user_id));
        return $this->parsed_response ? $this->parsed_response['photosets'] : false;
    }

    function photosets_getPhotos($photoset_id, $extras = NULL, $privacy_filter = NULL, $per_page = NULL, $page = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.getPhotos.html */
        $this->request("flickr.photosets.getPhotos", array("photoset_id" => $photoset_id, "extras" => $extras, "privacy_filter" => $privacy_filter, "per_page" => $per_page, "page" => $page));
        return $this->parsed_response ? $this->parsed_response['photoset'] : false;
    }

    function photosets_orderSets($photoset_ids)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.orderSets.html */
        if (is_array($photoset_ids)) {
            $photoset_ids = implode(",", $photoset_ids);
        }
        $this->request("flickr.photosets.orderSets", array("photoset_ids" => $photoset_ids), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_removePhoto($photoset_id, $photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.removePhoto.html */
        $this->request("flickr.photosets.removePhoto", array("photoset_id" => $photoset_id, "photo_id" => $photo_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    /* Photosets Comments Methods */
    function photosets_comments_addComment($photoset_id, $comment_text) {
        /* http://www.flickr.com/services/api/flickr.photosets.comments.addComment.html */
        $this->request("flickr.photosets.comments.addComment", array("photoset_id" => $photoset_id, "comment_text"=>$comment_text), TRUE);
        return $this->parsed_response ? $this->parsed_response['comment'] : false;
    }

    function photosets_comments_deleteComment($comment_id) {
        /* http://www.flickr.com/services/api/flickr.photosets.comments.deleteComment.html */
        $this->request("flickr.photosets.comments.deleteComment", array("comment_id" => $comment_id), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_comments_editComment($comment_id, $comment_text) {
        /* http://www.flickr.com/services/api/flickr.photosets.comments.editComment.html */
        $this->request("flickr.photosets.comments.editComment", array("comment_id" => $comment_id, "comment_text"=>$comment_text), TRUE);
        return $this->parsed_response ? true : false;
    }

    function photosets_comments_getList($photoset_id)
    {
        /* http://www.flickr.com/services/api/flickr.photosets.comments.getList.html */
        $this->request("flickr.photosets.comments.getList", array("photoset_id"=>$photoset_id));
        return $this->parsed_response ? $this->parsed_response['comments'] : false;
    }

    /* Reflection Methods */
    function reflection_getMethodInfo($method_name)
    {
        /* http://www.flickr.com/services/api/flickr.reflection.getMethodInfo.html */
        $this->request("flickr.reflection.getMethodInfo", array("method_name" => $method_name));
        return $this->parsed_response ? $this->parsed_response : false;
    }

    function reflection_getMethods()
    {
        /* http://www.flickr.com/services/api/flickr.reflection.getMethods.html */
        $this->request("flickr.reflection.getMethods");
        return $this->parsed_response ? $this->parsed_response['methods']['method'] : false;
    }

    /* Tags Methods */
    function tags_getHotList($period = NULL, $count = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.tags.getHotList.html */
        $this->request("flickr.tags.getHotList", array("period" => $period, "count" => $count));
        return $this->parsed_response ? $this->parsed_response['hottags'] : false;
    }

    function tags_getListPhoto($photo_id)
    {
        /* http://www.flickr.com/services/api/flickr.tags.getListPhoto.html */
        $this->request("flickr.tags.getListPhoto", array("photo_id" => $photo_id));
        return $this->parsed_response ? $this->parsed_response['photo']['tags']['tag'] : false;
    }

    function tags_getListUser($user_id = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.tags.getListUser.html */
        $this->request("flickr.tags.getListUser", array("user_id" => $user_id));
        return $this->parsed_response ? $this->parsed_response['who']['tags']['tag'] : false;
    }

    function tags_getListUserPopular($user_id = NULL, $count = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.tags.getListUserPopular.html */
        $this->request("flickr.tags.getListUserPopular", array("user_id" => $user_id, "count" => $count));
        return $this->parsed_response ? $this->parsed_response['who']['tags']['tag'] : false;
    }

    function tags_getListUserRaw($tag)
    {
        /* http://www.flickr.com/services/api/flickr.tags.getListUserRaw.html */
        $this->request("flickr.tags.getListUserRaw", array("tag" => $tag));
        return $this->parsed_response ? $this->parsed_response['who']['tags']['tag'][0]['raw'] : false;
    }

    function tags_getRelated($tag)
    {
        /* http://www.flickr.com/services/api/flickr.tags.getRelated.html */
        $this->request("flickr.tags.getRelated", array("tag" => $tag));
        return $this->parsed_response ? $this->parsed_response['tags'] : false;
    }

    function test_echo($args = array())
    {
        /* http://www.flickr.com/services/api/flickr.test.echo.html */
        $this->request("flickr.test.echo", $args);
        return $this->parsed_response ? $this->parsed_response : false;
    }

    function test_login()
    {
        /* http://www.flickr.com/services/api/flickr.test.login.html */
        $this->request("flickr.test.login");
        return $this->parsed_response ? $this->parsed_response['user'] : false;
    }

    function urls_getGroup($group_id)
    {
        /* http://www.flickr.com/services/api/flickr.urls.getGroup.html */
        $this->request("flickr.urls.getGroup", array("group_id"=>$group_id));
        return $this->parsed_response ? $this->parsed_response['group']['url'] : false;
    }

    function urls_getUserPhotos($user_id = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.urls.getUserPhotos.html */
        $this->request("flickr.urls.getUserPhotos", array("user_id"=>$user_id));
        return $this->parsed_response ? $this->parsed_response['user']['url'] : false;
    }

    function urls_getUserProfile($user_id = NULL)
    {
        /* http://www.flickr.com/services/api/flickr.urls.getUserProfile.html */
        $this->request("flickr.urls.getUserProfile", array("user_id"=>$user_id));
        return $this->parsed_response ? $this->parsed_response['user']['url'] : false;
    }

    function urls_lookupGroup($url)
    {
        /* http://www.flickr.com/services/api/flickr.urls.lookupGroup.html */
        $this->request("flickr.urls.lookupGroup", array("url"=>$url));
        return $this->parsed_response ? $this->parsed_response['group'] : false;
    }

    function urls_lookupUser($url)
    {
        /* http://www.flickr.com/services/api/flickr.photos.notes.edit.html */
        $this->request("flickr.urls.lookupUser", array("url"=>$url));
        return $this->parsed_response ? $this->parsed_response['user'] : false;
    }
}


?>
