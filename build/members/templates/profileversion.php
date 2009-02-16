<?php
if (count($languages) > 1) {
?>
            <?=$words->get('ProfileVersion')?>: 
            <?php if (file_exists('bw/images/flags/'.$profile_language_code.'.png')) { ?>
            <img height="11px"  width="16px"  src="bw/images/flags/<?=$profile_language_code ?>.png" style="<?=$css?>" alt="<?=$profile_language_code ?>.png">
            <?php } ?>
            <strong><?=$profile_language_name ?></strong> 
            	&nbsp;	&nbsp;	&nbsp;	&nbsp; <?=$words->get('ProfileVersionIn')?>:
        <?php 
        foreach($languages as $language) { 
            $css = 'opacity: 0.5';
            if ($language->ShortCode != $profile_language_code) {
        ?>
            <a href="<?=$urlstring?>/<?=$language->ShortCode ?>">
            <?php if (file_exists('bw/images/flags/'.$language->ShortCode.'.png')) { ?>
             <img height="11px"  width="16px"  src="bw/images/flags/<?=$language->ShortCode ?>.png" style="<?=$css?>" alt="<?=$language->ShortCode ?>.png">
            <?php } ?>
            <strong><?=$language->Name ?></strong>
            </a> 
        <?php 
            }
        } ?>
<?php } ?>