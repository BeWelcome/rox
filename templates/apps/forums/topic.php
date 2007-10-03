<?php

	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');

	$i18n = new MOD_i18n('apps/forums/board.php');
	$boardText = $i18n->getText('boardText');

	$User = APP_User::login();
	$can_del = $User && $User->hasRight('delete@forums');
	$can_edit_own = $User && $User->hasRight('edit_own@forums');
	$can_edit_foreign = $User && $User->hasRight('edit_foreign@forums');
	
?>

<h2><?php echo $topic->topicinfo->title; ?><br />
<span class="forumsthreadtags"><strong>Tags:</strong> <?php

	$url = 'forums/';
	$breadcrumb = '';
	if (isset($topic->topicinfo->continent) && $topic->topicinfo->continent) {
		$url = $url.'k'.$topic->topicinfo->continent.'-'.Forums::$continents[$topic->topicinfo->continent].'/';
		$breadcrumb .= '<a href="'.$url.'">'.Forums::$continents[$topic->topicinfo->continent].'</a> ';
		
		if (isset($topic->topicinfo->countryname) && $topic->topicinfo->countryname) {
			$url = $url.'c'.$topic->topicinfo->countrycode.'-'.$topic->topicinfo->countryname.'/';
			$breadcrumb .= ':: <a href="'.$url.'">'.$topic->topicinfo->countryname.'</a> ';

			if (isset($topic->topicinfo->adminname) && $topic->topicinfo->adminname) {
				$url = $url.'a'.$topic->topicinfo->admincode.'-'.$topic->topicinfo->adminname.'/';
				$breadcrumb .= ':: <a href="'.$url.'">'.$topic->topicinfo->adminname.'</a> ';
				
				if (isset($topic->topicinfo->geonames_name) && $topic->topicinfo->geonames_name) {
					$url = $url.'g'.$topic->topicinfo->geonameid.'-'.$topic->topicinfo->geonames_name.'/';
					$breadcrumb .= ':: <a href="'.$url.'">'.$topic->topicinfo->geonames_name.'</a> ';
				}
			}	
		}
	}


	if (isset($topic->topicinfo->tag1) && $topic->topicinfo->tag1) {
		if ($breadcrumb) {
			$breadcrumb .= ':: ';
		}
		$url = $url.'t'.$topic->topicinfo->tag1id.'-'.$topic->topicinfo->tag1.'/';
		$breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag1.'</a> ';
	}

	if (isset($topic->topicinfo->tag2) && $topic->topicinfo->tag2) {
		if ($breadcrumb) {
			$breadcrumb .= ':: ';
		}
		$url = $url.'t'.$topic->topicinfo->tag2id.'-'.$topic->topicinfo->tag2.'/';
		$breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag2.'</a> ';
	}
	if (isset($topic->topicinfo->tag3) && $topic->topicinfo->tag3) {
		if ($breadcrumb) {
			$breadcrumb .= ':: ';
		}
		$url = $url.'t'.$topic->topicinfo->tag3id.'-'.$topic->topicinfo->tag3.'/';
		$breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag3.'</a> ';
	}
	if (isset($topic->topicinfo->tag4) && $topic->topicinfo->tag4) {
		if ($breadcrumb) {
			$breadcrumb .= ':: ';
		}
		$url = $url.'t'.$topic->topicinfo->tag4id.'-'.$topic->topicinfo->tag4.'/';
		$breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag4.'</a> ';
	}
	if (isset($topic->topicinfo->tag5) && $topic->topicinfo->tag5) {
		if ($breadcrumb) {
			$breadcrumb .= ':: ';
		}
		$url = $url.'t'.$topic->topicinfo->tag5id.'-'.$topic->topicinfo->tag5.'/';
		$breadcrumb .= '<a href="'.$url.'">'.$topic->topicinfo->tag5.'</a> ';
	}

	echo $breadcrumb;

?></span></h2>

<?php

if ($User) {

?>
	
	<div id="forumsthreadreplytop"><a class="button" href="<?php echo $uri; ?>reply"><?php echo $boardText['reply']; ?></a></div>

<?php

} // end if ($User)

	foreach ($topic->posts as $post) {
		require TEMPLATE_DIR.'apps/forums/singlepost.php';
	}
		
if ($User) {

?>
<div id="forumsthreadreplybottom"><a class="button" href="<?php echo $uri; ?>reply"><?php echo $boardText['reply']; ?></a></div>
<?php

}
?>