<?php
if (count($languages) > 1) {
?>      
        <?=$words->get('ProfileVersion')?>:
        <span class="activelanguage"><?=$profile_language_name ?></span>

        <span><?=$words->get('ProfileVersionIn')?>:</span>        
        
        <?php
        foreach($languages as $language) {
            if ($language->ShortCode != $profile_language_code) {
        ?>
            <a class="availablelanguages" href="<?=$urlstring?>/<?=$language->ShortCode ?>">
                <?=$language->Name ?>
            </a>
        <?php
            }
        } ?>
<?php } ?>
