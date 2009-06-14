<?php


class UpdateMandatoryPage extends EditMyProfilePage
{
    
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->get('UpdateMandatoryPage');
    }
}

?>
