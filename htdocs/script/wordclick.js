
/*
  *  This mechanism allows to click all words for translation, when in wordclick mode.
  *  The function wordclick(..) is called in  "\modules\i18n\lib\words.lib.php".
  */
var wordclick_enabled = false;
function wordclick(code) {
    if(wordclick_enabled) {
        document.location.href="http://localhost/bw-trunk/htdocs/bw/admin/adminwords.php?IdLanguage=en&code="+code;
    }
};
function edit_mode_click() {
    toggle_wordclick_mode();
};
function toggle_wordclick_mode() {
    if(wordclick_enabled) disable_wordclick_mode();
    else enable_wordclick_mode();
};
function enable_wordclick_mode() {
    wordclick_enabled=true;
    document.body.className="wordclick_mode";
};
function disable_wordclick_mode() {
    wordclick_enabled=false;
    document.body.className="no_wordclick_mode";
};

