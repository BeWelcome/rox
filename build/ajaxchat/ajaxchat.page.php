<?php
/** Ajax Chat
 * 
 * @package ajaxchat
 * @author lemon-head
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class AjaxchatPage extends PageWithActiveSkin
{
    public function column_col3()
    {
        echo '<h3>This is the AJAX Chat</h3>';
        // echo '<p><pre>';
        // print_r($this->model->getMessagesInRoom(1, false));
        // echo '</pre></p>';
        
        if (isset($_SESSION['Username'])) {
            ?>
            
<script type="text/javascript" src="script/prototype.js"></script>
<script type="text/javascript">

function chat_update_callback(a,b)
{
    $('display').innerHTML = a.responseText;
    $('waiting_update').innerHTML = '';
    scroll_down();
}

function scroll_down() {
    var chat_scroll_box = document.getElementById("chat_scroll_box");
    chat_scroll_box.scrollTop = chat_scroll_box.scrollHeight;
}


function chat_update()
{
    var ajax_request = new Ajax.Request(
        "ajaxchat/ajax/update?a=aaa&b=bbb",
        {
            method: "post",
            onComplete: chat_update_callback
        }
    );
}

/*
function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;
    if(level > 4) return "";
    
    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";
    // alert("try dump "+level);
    if(typeof(arr) == "object") { //Array/Hashes/Objects 
        for(var item in arr) {
            var value = arr[item];
            
            if(typeof(value) == "object") { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}
*/

function send_chat_message() {
    new Ajax.Request('ajaxchat/ajax/send', {
        method: "post",
        parameters: $('ajaxchat_form').serialize(true),
        onComplete: send_chat_message_callback
    });
    $('waiting_send').innerHTML = $('chat_textarea').value;
    $('chat_textarea').value = "";
    scroll_down();
}


function send_chat_message_callback(a,b) {
    $('waiting_update').innerHTML = $('waiting_send').innerHTML;
    $('waiting_send').innerHTML = '';
    scroll_down();
    chat_update();
}

function chat_textarea_keyup(e) {
    keycode = key(e);
    if (13 == keycode) {
        send_chat_message();
    } else {
        $('keycode_monitor').innerHTML = keycode;
    }
}

function key(e) {
    if (window.event) return window.event.keyCode;
    else if (e) return e.which;
    else return false;
}


/*
function showDown(evt) {
    clearCells( );
    evt = (evt) ? evt : ((event) ? event : null);
    if (evt) {
        document.getElementById("downKey").innerHTML = evt.keyCode;
        if (evt.charCode) {
            document.getElementById("downChar").innerHTML = evt.charCode;
        }
        showTarget(evt);
    }
}
*/

</script>



<p><a onclick="chat_update();">update</a></p>


<div style="overflow:auto; border:1px solid grey; height:10em;" id="chat_scroll_box">
<div id="display"></div>
<div style="color:#666" id="waiting_update"></div>
<div style="color:#aaa" id="waiting_send"></div>
</div>
<br>
<div id="keycode_monitor">x</div>
<br>
<form id="ajaxchat_form" method="POST" action="ajaxchat">
<?=$this->layoutkit->formkit->setPostCallback('AjaxchatController', 'sendChatMessageCallback') ?>
<textarea id="chat_textarea" name="chat_message_text" rows="7" cols="60"></textarea>
</form>

<script type="text/javascript">

$('chat_textarea').onkeyup = chat_textarea_keyup;
chat_update();
setInterval(chat_update, 2000);

</script>
            <?php
            
        } else {
            
            $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
            $loginWidget->render();
            
        }
    }
}


?>