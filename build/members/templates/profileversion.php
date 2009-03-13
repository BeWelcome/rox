<?php
if (count($languages) > 1) {
?>      
        <?=$words->get('ProfileVersion')?>:
        <span class="activelanguage"><?=$profile_language_name ?> <a class="button" href="editmyprofile/<?=$profile_language_code?>/delete"><?=$words->get('delete')?></a></span>
 		&nbsp;  &nbsp;  &nbsp;  &nbsp;
        <span><?=$words->get('ProfileVersionIn')?>:</span>        
        
        <?php
		$ii = 0;
		$max = count($languages);
        foreach($languages as $language) {
            if ($language->ShortCode != $profile_language_code) {
			echo ($ii != 0 && $ii != $max -1) ? ',' : '';
        ?>  
            <a class="availablelanguages" href="<?=$urlstring?>/<?=$language->ShortCode ?>"><?=$language->Name ?></a>
        <?php
			$ii++;
            }
        } ?>
<?php } ?>
