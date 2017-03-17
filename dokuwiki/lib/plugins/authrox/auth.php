<?php

class auth_plugin_rox extends DokuWiki_Auth_Plugin {

    function __construct() {
        $this->cando['external'] = true;
        $this->cando['logout'] = true;
    }

    function trustExternal($user, $pass, $sticky = false) {

        global $USERINFO;

        $sticky ? $sticky = true : $sticky = false; //sanity check

        if (!empty($_SESSION[DOKU_COOKIE]['auth']['info'])) {
            $USERINFO['name'] = $_SESSION[DOKU_COOKIE]['auth']['info']['user'];
            $USERINFO['mail'] = $_SESSION[DOKU_COOKIE]['auth']['info']['mail'];
            $USERINFO['grps'] = $_SESSION[DOKU_COOKIE]['auth']['info']['grps'];
            $_SERVER['REMOTE_USER'] = $_SESSION[DOKU_COOKIE]['auth']['user'];
            return true;
        }

        if (!empty($user)) {
            $USERINFO['name'] = 'member-1223';
            $USERINFO['mail'] = 'a@b.c';
            $USERINFO['grps'] = array('admin','user');
            $_SERVER['REMOTE_USER'] = 'member-1223';
            $_SESSION[DOKU_COOKIE]['auth']['user'] = 'member-1223';
            $_SESSION[DOKU_COOKIE]['auth']['mail'] = 'a@b,c';
            $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
            $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
            return true;
        } else {
            return false;
        }

    }
}