<?php

	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');
	
?>

<table class="forumsboardthreads floatbox framed">

<tr>
	<th><?php echo $boardText['thread']; ?></th>
	<th><?php echo $boardText['replies']; ?></th>
	<th><?php echo $boardText['author']; ?></th>
	<th><?php echo $boardText['views']; ?></th>
	<th><?php echo $boardText['last_post']; ?></th>
</tr>

<?php

	foreach ($threads as $thread) {
	//[threadid] => 10 [title] => aswf [replies] => 0 [views] => 0 [first_postid] => 1 [first_authorid] => 1 [first_create_time] => 1165322369 [last_postid] => 1 [last_authorid] => 1 [last_create_time] => 1165322369 [first_author] => dave [last_author] => dave )
		$url = $uri.'s'.$thread->threadid.'-'.$thread->title;
		
		$max = $thread->replies + 1;
		$maxPage = ceil($max / Forums::POSTS_PER_PAGE);
		
		$last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
		
		?>
			<tr class="highlight">
				<td class="forumsboardthreadtitle">
					<a href="<?php echo $url; ?>"><?php echo $thread->title; ?></a><br />
					<span class="forumsboardthreadtags"><?php
						
						$breadcrumb = '';
						
						if (isset($thread->continent) && $thread->continent) {
							$url_bit = 'k'.$thread->continentid.'-'.$thread->continent;
							if (!in_array($url_bit, $request)) {
								$url = $uri.$url_bit.'/';
								$breadcrumb .= '<a href="'.$url.'">'.$thread->continent.'</a> ';
							} else {
//								$url = 'forums/'.$url_bit.'/';
								$url = $uri;
								$breadcrumb .= ''.$thread->continent.' ';
							}
							
							if (isset($thread->countryname) && $thread->countryname) {
								$url_bit = 'c'.$thread->countrycode.'-'.$thread->countryname;
								if (!in_array($url_bit, $request)) {
									$url = $url.$url_bit.'/';
									$breadcrumb .= ':: <a href="'.$url.'">'.$thread->countryname.'</a> ';
								} else {
//									$url = $url.$url_bit.'/';
									$breadcrumb .= ':: '.$thread->countryname.' ';
								}
							
							
								if (isset($thread->adminname) && $thread->adminname) {
									$url_bit = 'a'.$thread->admincode.'-'.$thread->adminname;
									if (!in_array($url_bit, $request)) {
										$url = $url.$url_bit.'/';
										$breadcrumb .= ':: <a href="'.$url.'">'.$thread->adminname.'</a> ';
									} else {
	//									$url = $url.$url_bit.'/';
										$breadcrumb .= ':: '.$thread->adminname.' ';
									}
//									echo '<a href="'.$uri.'k'.$thread->continentid.'-'.$thread->continent.'/c'.$thread->countrycode.'-'.$thread->countryname.'/a'.$thread->admincode.'-'.$thread->adminname.'">'.$thread->adminname.'</a> ';
								
									if (isset($thread->geonames_name) && $thread->geonames_name) {
										$url_bit = 'g'.$thread->geonameid.'-'.$thread->geonames_name;
										if (!in_array($url_bit, $request)) {
											$url = $url.$url_bit.'/';
											$breadcrumb .= ':: <a href="'.$url.'">'.$thread->geonames_name.'</a> ';
										} else {
		//									$url = $url.$url_bit.'/';
											$breadcrumb .= ':: '.$thread->geonames_name.' ';
										}


//										echo '<a href="'.$uri.'k'.$thread->continentid.'-'.$thread->continent.'/c'.$thread->countrycode.'-'.$thread->countryname.'/a'.$thread->admincode.'-'.$thread->adminname.'/g'.$thread->geonameid.'-'.$thread->geonames_name.'">'.$thread->geonames_name.'</a> ';
									}
								
								}

							}
						}
					
						
					
						if (isset($thread->tag1) && $thread->tag1) {
							if ($breadcrumb) {
								$breadcrumb .= ':: ';
							}
							$url_bit = 't'.$thread->tag1id.'-'.$thread->tag1;
							if (!in_array($url_bit, $request)) {
								$url = $uri.$url_bit.'/';
								$breadcrumb .= '<a href="'.$url.'">'.$thread->tag1.'</a> ';
							} else {
								$breadcrumb .= ''.$thread->tag1.' ';
							}
						}
					
						if (isset($thread->tag2) && $thread->tag2) {
							if ($breadcrumb) {
								$breadcrumb .= ':: ';
							}
							$url_bit = 't'.$thread->tag2id.'-'.$thread->tag2;
							if (!in_array($url_bit, $request)) {
								$url = $uri.$url_bit.'/';
								$breadcrumb .= '<a href="'.$url.'">'.$thread->tag2.'</a> ';
							} else {
								$breadcrumb .= ''.$thread->tag2.' ';
							}
						}
					
						if (isset($thread->tag3) && $thread->tag3) {
							if ($breadcrumb) {
								$breadcrumb .= ':: ';
							}
							$url_bit = 't'.$thread->tag3id.'-'.$thread->tag3;
							if (!in_array($url_bit, $request)) {
								$url = $uri.$url_bit.'/';
								$breadcrumb .= '<a href="'.$url.'">'.$thread->tag3.'</a> ';
							} else {
								$breadcrumb .= ''.$thread->tag3.' ';
							}
						}
					
						if (isset($thread->tag4) && $thread->tag4) {
							if ($breadcrumb) {
								$breadcrumb .= ':: ';
							}
							$url_bit = 't'.$thread->tag4id.'-'.$thread->tag4;
							if (!in_array($url_bit, $request)) {
								$url = $uri.$url_bit.'/';
								$breadcrumb .= '<a href="'.$url.'">'.$thread->tag4.'</a> ';
							} else {
								$breadcrumb .= ''.$thread->tag4.' ';
							}
						}
					
						if (isset($thread->tag5) && $thread->tag5) {
							if ($breadcrumb) {
								$breadcrumb .= ':: ';
							}
							$url_bit = 't'.$thread->tag5id.'-'.$thread->tag5;
							if (!in_array($url_bit, $request)) {
								$url = $uri.$url_bit.'/';
								$breadcrumb .= '<a href="'.$url.'">'.$thread->tag5.'</a> ';
							} else {
								$breadcrumb .= ''.$thread->tag5.' ';
							}
						}
					
					if ($breadcrumb) {
						echo $boardText['tags'];				
						echo $breadcrumb;
					}
					
					?></span>
				</td>
				<td class="forumsboardthreadreplies"><?php echo $thread->replies; ?></td>
				<td class="forumsboardthreadauthor"><a href="user/<?php echo $thread->first_author; ?>"><?php echo $thread->first_author; ?></a></td>
				<td class="forumsboardthreadviews"><?php echo number_format($thread->views); ?></td>
				<td class="forumsboardthreadlastpost">
					<span class="small grey"><?php echo date($format['short'], $thread->last_create_time); ?></span><br />
					<a href="user/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
					<a href="<?php echo $last_url; ?>"><img src="images/icons/last.gif" alt="<?php echo $boardText['to_last']; ?>" title="<?php echo $boardText['to_last']; ?>" /></a>
				</td>
			</tr>
		<?php
	}


?>

</table>

<?php
if ($User && $uri != 'forums/') {
?>
<div id="boardnewtopicbottom"><a class="button" href="<?php echo $uri; ?>new"><?php echo $boardText['new_topic']; ?></a></div>
<?php
}
?>

<p></p>

<?php

require TEMPLATE_DIR.'apps/forums/pages.php';

?>