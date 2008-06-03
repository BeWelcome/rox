<?php ?>

<script type="text/javascript" src="script/prototype162.js"></script>        
<script type="text/javascript"><!--//

// setting the baseuri for ajax calls, because sometimes it doesn't work.
var baseuri = "<?=PVars::getObj('env')->baseuri ?>";

//--------------- autoscroll -----------------------

var autoscroll_active = true;
function scroll_down() {
    if (!autoscroll_active) return;
    var chat_scroll_box = document.getElementById("chat_scroll_box");
    chat_scroll_box.scrollTop = chat_scroll_box.scrollHeight;
}
function on_manual_scroll() {
    var chat_scroll_box = $("chat_scroll_box");
    if (chat_scroll_box.scrollTop + chat_scroll_box.clientHeight == chat_scroll_box.scrollHeight) {
        autoscroll_active = true;
        // $('scrollmode_monitor').innerHTML = "true";
    } else {
        autoscroll_active = false;
        // $('scrollmode_monitor').innerHTML = (chat_scroll_box.scrollTop + chat_scroll_box.clientHeight - chat_scroll_box.scrollHeight);
    }
}

//--------------- chat update -----------------------


function chat_update()
{
    var min_key = (messages_sorted_max_key > messages_sorted_lookback_limit) ? messages_sorted_max_key : messages_sorted_lookback_limit;
    new Ajax.Request(baseuri + "json/ajaxchat/update/" + min_key, {
        method: "post",
        onComplete: chat_update_callback
    });
}

function chat_update_callback(transport)
{
    if (!transport.responseJSON) {
        var error_display = $('error-display');
        error_display.innerHTML = '<img src="images/icons/disconnect.png"> No connection\n\n' + transport.responseText;
    } else {
        var json = transport.responseJSON;
        show_json_alerts(json.alerts);
        show_json_text(json.text);
        if (json.messages) {
            add_json_messages(json.messages);
            if (transport.transport.wait_element) {
                var wait_element = transport.transport.wait_element;
                wait_element.parentNode.removeChild(wait_element);
            }
            if (json.new_lookback_limit) {
                messages_sorted_max_key = json.new_lookback_limit;
            }
        }
    }
}

var messages_sorted = new Object();
var messages_sorted_max_key = '0';
var messages_sorted_lookback_limit = '<?=$lookback_limit ?>';

function add_json_messages(messages_json)
{
    if (!messages_json) return;

    // alert(messages_json.length + ' new messages fetched from server');
    for (var i=0; i<messages_json.length; ++i) {
        var message = messages_json[i];
        parse_smilies(message);
        if (messages_sorted_lookback_limit < message.created && message.text) {
            // message.node = document.createElement('div');
            // message.node.innerHTML = innerHTML_for_message(message); 
            messages_sorted[message.created + '_' + message.id] = message;
        }
    }
    
    show_all_messages();
}

// do we really need this one?
function innerHTML_for_message(message) {
    return
        '<div style="margin:4px"><div style="color:#ddd">' + key + '<\/div><div>' +
        '<a href="bw/member.php?cid='+message.username+'">' + message.username + ':<\/a> ' +
        message.text + '<\/div>' + '<\/div>'
    ; 
}

function show_all_messages()
{
    var display = $('display');
    
    var accum_text = '';
    var username = false;
    
    for (var key in messages_sorted) {
        var message = messages_sorted[key];
        if (message.username != username) {
            username = message.username;
            // accum_text += '<div style="background:#aaccff;">' + username + '</div>';
            accum_text += '<hr style="border-color:#eee;"/>';
        }
        
        accum_text += 
            '<div style="margin:4px">' +
            '<div style="color:#ddd" class="small">' + key + '<\/div>' +
            '<div>' +
            '<a href="bw/member.php?cid=' + username + '">' + username + ':<\/a> ' +
            message.text + '<\/div><\/div>'
        ;
    }
    
    
    
    display.innerHTML = accum_text;
    
    scroll_down();
}


function show_json_alerts(alerts)
{
    if (alerts) {
        var error_display = $('error-display');
        var error_text = '';
        for (var i=0; i<alerts.length; ++i) {
            error_text = 
                '<div class="error" style="margin: 1em">' + alerts[i] + '<\/div>'
            ;
        }
        error_display.innerHTML = error_text;
    }    
}

function show_json_text(text)
{
    // do nothing with the text..
}


//--------------- send message -----------------------

function chat_textarea_keyup(e) {
    keycode = key(e);
    if (13 == keycode) {
        send_chat_message();
    } else {
        // $("keycode_monitor").innerHTML = keycode;
    }
}

function key(e) {
    if (window.event) return window.event.keyCode;
    else if (e) return e.which;
    else return false;
}

function send_chat_message() {
    var params = $("ajaxchat_form").serialize(true);
    var wait_element = document.createElement("div");
    wait_element.innerHTML = "<?=$_SESSION['Username'] ?>: "+$('chat_textarea').value; 
    document.getElementById("waiting_send").appendChild(wait_element);
    document.getElementById("chat_textarea").value = "";
    var request = new Ajax.Request(baseuri + "json/ajaxchat/send", {
        method: "post",
        parameters: params,
        onComplete: chat_update_callback
    });
    request.transport.wait_element = wait_element;
    autoscroll_active = true;
    scroll_down();
}

// ADD SMILIES AND LINKS TO THE CHAT :) ;) :P :D

b = new Array();
b[0] = /(ftp|http|https|file):\/\/[\S]+(\b|$)/gim;
b[1] = /([^\/])(www[\S]+(\b|$))/gim;
b[2] = /:\)/gi; // :)
b[3] = /:D/gi; // :D
b[4] = /:p|:P/gi; // :P
b[5] = /;\)/ //;)
b[6] = /\[new\]/gi; // New symbol
b[7] = /\[info\]/gi; // Information symbol
b[8] = /\[star\]/gi; // Star symbol
b[9] = /\[ok\]/gi; // Ok symbol
b[10] = /\[\?\]/gi; // Help symbol

c = new Array()
c[0] = "<a href=\"$&\" class=\"my_link\" target=\"_blank\">$&</a>";
c[1] = "$1<a href=\"http://$2\" class=\"my_link\" target=\"_blank\">$2</a>";
c[2] = "<img src=\"images/icons/emoticon_smile.png\" title=\"Smile\">";
c[3] = "<img src=\"images/icons/emoticon_grin.png\" title=\"Grin\">";
c[4] = "<img src=\"images/icons/emoticon_tongue.png\" title=\"Tongue\">";
c[5] = "<img src=\"images/icons/emoticon_wink.png\" title=\"Wink\">";
c[6] = "<img src=\"images/icons/new.png\" title=\"New\">";
c[7] = "<img src=\"images/icons/information.png\" title=\"Information\">";
c[8] = "<img src=\"images/icons/star.png\" title=\"Star\">";
c[9] = "<img src=\"images/icons/accept.png\" title=\"Ok\">";
c[10] = "<img src=\"images/icons/help.png\" title=\"Help\">";

function parse_smilies(a) {
    for (var j = 0 ; j < b.length ; j ++) {
        a.text = a.text.replace(b[j],c[j]); 
    }
}

function insert_bbtags(aTag, eTag) {
  var input = document.forms['ajaxchat_form'].elements['chat_textarea'];
  input.focus();
  /* für Internet Explorer */
  if(typeof document.selection != 'undefined') {
    /* Einfügen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = aTag + insText + eTag;
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);      
    }
    range.select();
  }
  /* für neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length == 0) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* für die übrigen Browser */
  else
  {
    /* Abfrage der Einfügeposition */
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    /* Einfügen des Formatierungscodes */
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
  }
}


//--------------------------------------------------------


//--></script>



<p><span id="update_button" class="button" style="cursor: pointer">update</span></p>

<div id="error-display"></div>

<div style="overflow:auto; border:1px solid grey; height:20em; width:40em;" id="chat_scroll_box" onscroll="on_manual_scroll()">
<div id="display"></div>
<div style="color:#666" id="waiting_update"></div>
<div style="color:#aaa" id="waiting_send"></div>
</div>
<br>
<!-- <div><span id="keycode_monitor"></span>, <span id="scrollmode_monitor"></span></div> -->
<br>
<form id="ajaxchat_form" method="POST" action="ajaxchat">
<textarea id="chat_textarea" name="chat_message_text" rows="4" cols="60"></textarea>
<div style="margin-top: 0.3em">
<a class="bigbutton" id="send_button" style="cursor: pointer;"><span>Send</span></a><br />
<span>Add smilies: 
<img title="Wink" onclick="insert_bbtags(';\)', '')" src="images/icons/emoticon_wink.png"/>
<img title="Smile" onclick="insert_bbtags(':\)', '')" src="images/icons/emoticon_smile.png"/>
<img title="Grin" onclick="insert_bbtags(':D', '')" src="images/icons/emoticon_grin.png"/>
<img title="Tongue" onclick="insert_bbtags(':P', '')" src="images/icons/emoticon_tongue.png"/>
<img title="Grin" onclick="insert_bbtags('\[new\]', '')" src="images/icons/new.png"/>
<img title="Tongue" onclick="insert_bbtags('\[info\]', '')" src="images/icons/information.png"/>
<img title="Grin" onclick="insert_bbtags('\[star\]', '')" src="images/icons/star.png"/>
<img title="Tongue" onclick="insert_bbtags('\[ok\]', '')" src="images/icons/accept.png"/>
<img title="Tongue" onclick="insert_bbtags('\[?\]', '')" src="images/icons/help.png"/>
</span>
</div>
</form>

<div id="ajax_monitor">..</div>

<script type="text/javascript">

document.getElementById("send_button").onclick = send_chat_message;
document.getElementById("update_button").onclick = chat_update;
document.getElementById("chat_textarea").onkeyup = chat_textarea_keyup;
chat_update();
setInterval(chat_update, 500);






/*
Array.prototype.print_r = function()
{ 
    document.write( this.getDebugString() );
}
 
Array.prototype.alert_r = function()
{
    alert( this.getDebugString() );
}
 
Array.prototype.getDebugString = function( numTabs )
{
    // We subract one from numTabs here and add two later because of an odd illegal radix error
    numTabs  = ( typeof numTabs == 'undefined' ) ? 0 : numTabs - 1;
    var tabs    = ('\t').replicate( numTabs );
    var output = 'Array\n' + tabs + '(\n';
    for ( var i = 0, l = this.length; i < l; i++ )
    {
        output += tabs + '\t[' + i + '] => ' + this[i].getDebugString( numTabs + 2 ) + '\n';
    }
    return output + tabs + ')';
}
 
Object.prototype.getDebugString = function( numTabs )
{
    // We subract one from numTabs here and add two later because of an odd illegal radix error
    numTabs  = ( typeof numTabs == 'undefined' ) ? 0 : numTabs - 1;
    var tabs    = ('\t').replicate( numTabs );
    var output = 'Object\n' + tabs + '{\n';
    for ( var i in this )
    {
        output += tabs + '\t[' + i + '] => ' + this[i].getDebugString( numTabs + 2 ) + '\n';
    }
    return output + tabs + '}';
}
 
Function.prototype.getDebugString = function()
{
    return 'function() { ... }';
}
 
String.prototype.getDebugString  = String.prototype.toString;
Number.prototype.getDebugString  = Number.prototype.toString;
Boolean.prototype.getDebugString    = Boolean.prototype.toString;
 
// helper method
String.prototype.replicate = function( qty )
{
    var output = '';
    for ( var i = 0; i < qty; i++ )
    {
        output += this;
    }
    return output;
}


*/



</script>
