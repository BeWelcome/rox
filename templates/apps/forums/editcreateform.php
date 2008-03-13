<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

$i18n = new MOD_i18n('apps/forums/editcreateform.php');
$formText = $i18n->getText('editCreateText');

$words = new MOD_words();

$request = PRequest::get()->request;
$uri = implode('/', $request);

$vars =& PPostHandler::getVars($callbackId);
print_r($vars);

if (isset($vars['tags']) && $vars['tags']) {
    $tags_with_commas = implode(', ', $vars['tags']);
} else if (isset($tags) && $tags) {
    $tags_with_commas = implode(', ', $tags);
} else {
    $tags_with_commas = false;
}

?>
<script type="text/javascript" src="script/forums_suggest.js"></script>
<script type="text/javascript" src="script/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    theme_advanced_resize_horizontal : false,
    mode: "exact",
    elements: "topic_text",
    theme: "advanced",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1: "bold,italic,underline,strikethrough,bullist,separator,forecolor,backcolor,charmap,link",
    theme_advanced_buttons2: "",
    theme_advanced_buttons3: "",    
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true
});
//-->
</script>
<h2>
<?php
if ($navichain_items = $boards->getNaviChain()) {
	$navichain = '<span class="forumsboardnavichain">';
	foreach ($navichain_items as $link => $title) {
		$navichain .= '<a href="'.$link.'">'.$title.'</a> :: ';
	}
	$navichain .= '<a href="'.$boards->getBoardLink().'">'.$boards->getBoardName().'</a><br /></span>';
} else {
	$navichain = '';
}

echo $navichain;
	
if ($allow_title) { // New Topic
	if ($edit) {
		echo $formText['edit_topic'];
	} else {
		echo $formText['new_topic'];
	}
} else { // Answer
	if ($edit) {
		echo $formText['edit_post'];
	} else {
		echo $formText['reply_title'].' &quot;'.$topic->topicinfo->title.'&quot;';
	}
} 
?></h2>

<form method="post" action="<?php echo $uri; ?>" id="forumsform">
<input type="hidden" name="<?php echo $callbackId; ?>" value="1" />

<?php
	if (isset($allow_title) && $allow_title) {
	
		if (isset($vars['errors']) && is_array($vars['errors'])) {
			if (in_array('title', $vars['errors'])) {
				echo '<div class="row error">'.$formText['error_title'].'</div>';
			}
		}
?>
		<div class="row">
		<label for="topic_title"><?php echo $formText['label_topicTitle']; ?></label><br />
		<input type="text" name="topic_title" size="50" maxlength="200" id="topic_title" value="<?php echo isset($vars['topic_title']) ? $vars['topic_title'] : ''; ?>" />
		</div>
<?php
	}
	
	
	if (isset($vars['errors']) && is_array($vars['errors'])) {
		if (in_array('text', $vars['errors'])) {
			echo '<div class="row error">'.$formText['error_post'].'</div>';
		}
	}
?>
<p></p>
<div class="row">
<label for="topic_text"><?php echo $formText['label_text']; ?></label><br />
<textarea name="topic_text" cols="70" rows="15" id="topic_text"><?php echo isset($vars['topic_text']) ? $vars['topic_text'] : ''; ?></textarea>
</div>
<p></p>

<?php
	if (isset($allow_title) && $allow_title) {
?>
	<div class="row">
		<label for="create-tags"><?php echo $formText['label_tags']; ?></label><br />
		<p class="small"><?php echo $formText['subline_tags']; ?></p><br />
		<textarea id="create-tags" name="tags" cols="60" rows="2"><?php 
		// the tags may be set
			echo ($tags_with_commas) ? htmlentities($tags_with_commas, ENT_COMPAT, 'utf-8') : ''; 
		?></textarea>
		<div id="suggestion"></div>
	</div>
	<p></p>
	<div class="row">
	<label for="dropdown">Place</label><br />
		<p class="small"><?php echo $formText['subline_place']; ?></p>
	<div id="dropdowns">
	<?php
		echo $locationDropdowns;
	?>
	</div>
	</div>
	<script type="text/javascript">
		function updateContinent() {
			var urlbit = 'k'+$('d_continent').value;
			update(urlbit);
		}
		
		function updateCountry() {
			var urlbit = 'k'+$('d_continent').value+'/c'+$('d_country').value;
			update(urlbit);
		}
		
		function updateAdmincode() {
			var urlbit = 'k'+$('d_continent').value+'/c'+$('d_country').value+'/a'+$('d_admin').value;
			update(urlbit);
		}
		
		function updateGeonames() {
			var urlbit = 'k'+$('d_continent').value+'/c'+$('d_country').value+'/a'+$('d_admin').value+'/g'+$('d_geoname').value;
			update(urlbit);
		}
		
		function update(urlbit) {
<?php /*
	if ($edit) {
		echo '$("forumsform").action = http_baseuri+"forums/edit/m'.$messageid.'/"+urlbit;';
	} else {
		echo '$("forumsform").action = http_baseuri+"forums/new/"+urlbit;';
	}
*/ ?>
			
			var url = http_baseuri+'forums/locationDropdowns/'+urlbit
			new Ajax.Request(url, 
			{
				method:'get', 
				onSuccess: function(req) {
					updateDropdowns(req.responseText);
				}
			});
		}
		
		function updateDropdowns(text) {
			Element.update('dropdowns', text);
		}
	</script>
<?php
	}
?>

<div class="row">
<?php 
	   		echo "<br /><input type=\"checkbox\" name=\"NotifyMe\" ",$notifymecheck,"> " ,$words->getFormatted("forum_NotifyMeForThisThread") ;

?>
</div>  
<p></p>
<div class="row">
<input type="submit" value="<?php 


if ($allow_title) { // New Topic
	if ($edit) {
		echo $formText['label_update_topic'];
	} else {
		echo $formText['label_create_topic'];
	}
} else { // Answer
	if ($edit) {
		echo $formText['label_update_post'];
	} else {
		echo $formText['label_create_post'];
	}
} 

 ?>" />
 
</div>

</form>
<script type="text/javascript">
ForumsSuggest.initialize();
</script>
