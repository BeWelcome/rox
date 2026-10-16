<?php

class DeleteNotePage extends ProfilePage
{
    
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'deletenote';
    }
}