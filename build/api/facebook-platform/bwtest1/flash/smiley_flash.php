<?php
include_once '../constants.php';
?>


<fb:swf width="200" height="200" id ="smile" swfsrc="<?= ROOT_LOCATION ?>/flash/Smiley.swf" />

<a href="#" onclick="changeSmiley()" >Smile!</a>

<script>
 function changeSmiley() {
    var smile = document.getElementById("smile");
    smile.callSWF("setText", ":)");
  }
</script>