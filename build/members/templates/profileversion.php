<?php
if (count($languages) > 1) {
?>      
		<p>
        <?=$words->get('ProfileVersion')?>:
        <span class="activelanguage"><?=$profile_language_name ?> 
        <? if ($this->myself) { ?>
        <a class="button" href="editmyprofile/<?=$profile_language_code?>/delete"><?=$words->get('delete')?></a>
        <? } ?>
        </span>
 		</p>
        <p class="floatbox">
		<span><?=$words->get('ProfileVersionIn')?>:</span>        
        
        <?php
		$ii = 0;
		$max = count($languages);
        foreach($languages as $language) {
            if ($language->ShortCode != $profile_language_code) {
			$space = ($ii != 0 && $ii != $max -1) ? ', ' : '';
        ?><a class="availablelanguages" href="<?=$urlstring?>/<?=$language->ShortCode ?>"><?=$language->Name ?><?=$space?></a><?php
			$ii++;
            }
        } ?>
		</p>
<?php } ?>
