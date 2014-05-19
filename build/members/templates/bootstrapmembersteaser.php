<?php        
        $member = $this->member;
        $words = $this->getWords();
        $thumbnail_url = 'members/avatar/'.$member->Username.'?150';
        $picture_url = 'members/avatar/'.$member->Username.'?500';
        ?>

<div id="profile_pic" >
    <a href="<?=$picture_url?>" id="profile_image"><img src="<?=$thumbnail_url?>" alt="Picture of <?=$member->Username?>" class="framed" height="150" width="150"/></a>
    <div id="profile_image_zoom_content" class="hidden">
        <img src="<?=$picture_url?>" alt="Picture of <?=$member->Username?>" />
    </div>
    <script type="text/javascript">
        // Activate FancyZoom for profile picture
        // (not for IE, which don't like FancyZoom)
        if ( typeof FancyZoom == "function" && is_ie === false) {
            new FancyZoom('profile_image');
        }
    </script>
</div>
<!-- profile_pic -->
            <ul class="nav nav-tabs" id="profile_linklist">
              <?php

        $active_menu_item = $this->getSubmenuActiveItem();
        foreach ($this->getSubmenuItems() as $index => $item) {
            $name = $item[0];
            $url = $item[1];
            $label = $item[2];
            $class = isset($item[3]) ? $item[3] : 'leftpadding';
            if ($name === $active_menu_item) {
                $attributes = ' class="active '.$class.'"';
                $around = '';
            } else {
                $attributes = ' class="'.$class.'"';
                $around = '';
            }

            ?><li id="sub<?=$index ?>" <?=$attributes ?> data-toggle="tab">
              <?=$around?><a style="cursor:pointer;" href="<?=$url ?>"><span><?=$label ?></span></a><?=$around?>
              <?=$words->flushBuffer(); ?>
            </li>
            <?php

        }

            ?></ul>