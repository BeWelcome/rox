<div class="forumspost">
	<div class="forumsauthor">	
		<div class="forumsauthorname">
			<a href="user/<?php echo $post->user_handle; ?>"><?php echo $post->user_handle; ?></a>
			<a href="country/<?php echo $post->fk_countrycode; ?>"><img src="images/icons/flags/<?php echo strtolower($post->fk_countrycode); ?>.png" alt="" /></a>
		</div>	
		<div class="forumsavatar">
			<img src="user/avatar/<?php echo $post->user_handle; ?>?xs=1" alt="avatar" title=""  />
		</div>
	</div>
	<div class="forumsmessage">
		<p class="forumstime">Posted: <?php echo date($format['short'], $post->posttime); ?></p>
		<?php echo $post->message; ?>
	</div>
</div>