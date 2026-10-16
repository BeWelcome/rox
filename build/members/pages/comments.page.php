<?php


class CommentsPage extends ProfilePage
{
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'comments';
    }
    
}
