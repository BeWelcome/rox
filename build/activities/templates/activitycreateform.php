<script type="text/javascript" src="script/fieldset.js"></script>
<script type="text/javascript" src="script/blog_suggest.js"></script>
<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    mode: "exact",
    elements: "event-description",
    plugins : "advimage,preview,fullscreen,autolink",
    theme: "advanced",
    content_css : "styles/css/minimal/screen/content_minimal.css",    
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1: "bold,italic,underline,strikethrough,separator,bullist,numlist,separator,forecolor,backcolor,charmap,link,image,separator,preview,fullscreen",
    theme_advanced_buttons2: "",
    theme_advanced_buttons3: "",
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true,
    theme_advanced_resize_horizontal : false,
    plugin_preview_width : "800",
    plugin_preview_height : "600"
});
//-->
</script>
<div>
<form method="post" action="events/create" class="def-form" id="event-create-form">
<fieldset id="event-create"><legend>Create an Event</legend>
    <div class="subcolumns">
        <div class="c33l">
        <label for="event-name">Name:</label><br/>
        <input type="text" id="event-name" name="event-name" class="long" value="Name of the event..." />
        </div>
        <div class="c33l">
        <label for="event-start-date">Begin date:</label><br />
        <input type="text" id="event-start-date" name="startdate" class="date" maxlength="10" style="width:9em" value="Jan 10, 2013" />
            <script type="text/javascript">
                /*<[CDATA[*/
                var datepicker	= new DatePicker({
                relative	: 'event-start-date',
                language	: '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
                current_date : '', 
                topOffset   : '25',
                relativeAppend : true
                });
                /*]]>*/
            </script>
        </div>
        <div class="c33r">
        <label for="event-start-time">Begin time:</label><br />
        <input type="text" id="event-start-time" name="event-start-time" class="time" maxlength="10" style="width:9em" value="10:00am" />
        </div>
    </div>
    <div class="subcolumns">
        <div class="c33l">
            <label for="event-address">Address:</label><br/>
            <input type="text" id="event-address" name="event-address" class="long" value="Address of the event..." />
        </div>
        <div class="c33l">
            <label for="event-end-date">End date:</label><br />
            <input type="text" id="event-end-date" name="enddate" class="date" maxlength="10" style="width:9em" value="Jan 10, 2013" />
            <script type="text/javascript">
                /*<[CDATA[*/
                var datepicker	= new DatePicker({
                relative	: 'event-end-date',
                language	: '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
                current_date : '', 
                topOffset   : '25',
                relativeAppend : true
                });
            /*]]>*/
            </script>
        </div>
        <div class="c33r">
            <label for="event-end-time">End time:</label><br />
            <input type="text" id="event-end-time" name="event-end-time" class="time" maxlength="10" style="width:9em" value="1:00pm" />
        </div>
    </div>
    <div class="subcolumns">
    <label>Categories</label><br />
    <table style="width:100%">
    <?php 
    $cats = array ("Culture", "Food", "Music", "Travel", "Sport", "Urban exploration", "Teach/learn", "Outdoor", "Flashmob");
    $str = "";
    $ii = 0;
    $lineout = true;
    foreach($cats as $cat) {
        if ($lineout) {
            $str .= '<tr>';
            $lineout = false;
        }
        $str .= '<td><input type="checkbox" name="event-categories[]" id="event-category-' . $ii . '" /> <label for="event-category-' . $ii . '">' . $cat . '</label></td>';
        $ii++;
        if ($ii % 3 == 0) {
            $str .= '</tr>';
            $lineout = true;
        }
    }
    echo $str;
    ?>
    </table>
    </div>
    <div class="subcolumns">
            <label for="event-description">Description:</label><br/>
            <textarea id="event-description" name="event-description" rows="10" cols="80" style="width:90%"></textarea>
    </div>
    <div>
        <br /><input type="submit" value="Submit" class="submit" />
    </div>
</fieldset>
</form>
</div>
