<?php


class AddCommentPage extends ProfilePage
{
    
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'commmentsadd';
    }
}