<?php


class UpdateMandatoryPage extends EditMyProfilePage
{
    
    #[\Override]
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->get('UpdateMandatoryPage');
    }
}
