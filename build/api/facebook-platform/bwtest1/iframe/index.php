<?php
include_once '../constants.php';
?>

<html>
<head>
  <!-- Put these in the head of your document to use these features-->
  <link rel="stylesheet" href="http://static.ak.new.facebook.com/css/fb_connect.css" type="text/css" />
  <script src="http://static.ak.new.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
</head>

<a href="http://apps.new.facebook.com/<?= APP_SUFFIX ?>" target="_parent"> <h2>Back to Smiley</h2> </a>

    This page shows how to use the functionality Smiley within an iframe. We'll try to keep it simple so you can use View Source or <a href="http://getfirebug.com/" target="_parent">Firebug</a> to check out the functionality.
<br><br>
Here is the button for adding a profile box.
<div id="addSection"></div>
<br>
Here is the button for adding an info box.
<div id="addInfo"></div>
<br>
Here is a link that will show a feed dialog. In a real application, you would want to run this js after a user action.
<br>
<a href="#" onclick="showFeed()">Feed Dialog</a>

<div id="thanks">
</div>

<script type="text/javascript">
    function showFeed() {
      // This first line is data for my feed story.
      // See http://wiki.developers.facebook.com/index.php/Feed.publishUserAction for more details
      var template_data = {'images' : [{'href':'http://www.facebook.com' , 'src' : '<?= IMAGE_LOCATION ?>smile0.jpg'}], 'mood': 'Happy', 'emote': ':)','mood_src': '<?= IMAGE_LOCATION ?>smile0.jpg'};

      // This the call to actually show the feed dialog
    FB.Integration.showFeedDialog(<?php echo FEED_STORY_1;?>, template_data, [], '', null, false,
                                   function(){document.getElementById('thanks').innerHTML = 'Thanks for looking at the feed dialog.';});
    }

    window.onload = function() {
    FB_RequireFeatures(["Integration"], function() {
        // Change the first arg to your api key, and the second to the path to your receiver.html file.
        // You can copy my receiver.html from http://fbplatform.mancrushonmcslee.com/smiley/iframe/receiver.html to your server and it will work fine.
        FB.Facebook.init('<?php echo API_KEY;?>','receiver.html', null);

        // These two lines add a "add to profile" button under the given id.
        FB.Integration.showAddSectionButton("profile", document.getElementById("addSection"));
        FB.Integration.showAddSectionButton("info",    document.getElementById("addInfo"));
      })};

</script>



</body>
</html>


<?php

