<?php


class UpdateMandatoryFinishPage extends EditMyProfilePage
{
    
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->get('UpdateMandatoryPage');
    }
    
    protected function column_col3() {
        $words = $this->getWords();
        ?>
        <div class="note">
            <?=$words->get('UpdateMantatoryConfirm');?>
        </div>
        <?php
    }
}
