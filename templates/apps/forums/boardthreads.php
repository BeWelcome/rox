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
	$i18n = new MOD_i18n('date.php');
	$format = $i18n->getText('format');
	$styles = array( 'highlight', 'blank' );
	$words = new MOD_words();
?>

<table class="forumsboardthreads floatbox framed">

<tr>
	<th><?php echo $words->getFormatted('Thread'); ?></th>
	<th><?php echo $words->getFormatted('Replies'); ?></th>
	<th><?php echo $words->getFormatted('Author'); ?></th>
	<th><?php echo $words->getFormatted('Views'); ?></th>
	<th><?php echo $words->getFormatted('LastPost'); ?></th>
</tr>

<?php

	foreach ($threads as $cnt =>  $thread) {
	//[threadid] => 10 [title] => aswf [replies] => 0 [views] => 0 [first_postid] => 1 [first_authorid] => 1 [first_create_time] => 1165322369 [last_postid] => 1 [last_authorid] => 1 [last_create_time] => 1165322369 [first_author] => dave [last_author] => dave )
		$url = $uri.'s'.$thread->threadid.'-'.$thread->title;
		
		$max = $thread->replies + 1;
		$maxPage = ceil($max / Forums::POSTS_PER_PAGE);
		
		$last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
		
		
		?>
			<tr class="<?php echo $styles[$cnt%2]; ?>">
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
						echo $words->getFormatted('tags');			
						echo $breadcrumb;
					}
					
					?></span>
				</td>
				<td class="forumsboardthreadreplies"><?php echo $thread->replies; ?></td>
				<td class="forumsboardthreadauthor"><a href="bw/member.php?cid=<?php echo $thread->first_author; ?>"><?php echo $thread->first_author; ?></a></td>
				<td class="forumsboardthreadviews"><?php echo number_format($thread->views); ?></td>
				<td class="forumsboardthreadlastpost">
					<span class="small grey"><?php echo date($format['short'], $thread->last_create_time); ?></span><br />
					<a href="bw/member.php?cid=<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
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
<div id="boardnewtopicbottom"><span class="button"><a href="<?php echo $uri; ?>new"><?php echo $words->getFormatted('ForumNewTopic'); ?></a></span></div>
<?php
}
?>

<p></p>

<?php

require TEMPLATE_DIR.'apps/forums/pages.php';

?>