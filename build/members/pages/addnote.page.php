<?php

class AddNotePage extends ProfilePage
{
    
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'addnote';
    }
}