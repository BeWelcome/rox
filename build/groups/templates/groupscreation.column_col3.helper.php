<?php
    // get translation module
    $layoutkit = $this->layoutkit;
    $words = $layoutkit->getWords();
    $model = $this->getModel();

    $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

    $formkit = $layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('GroupsController', 'createGroupCallback');
    
    $R = MOD_right::get();
    $GroupRight = $R->hasRight('Group');
    
    $IdGroup = false;
    $Group_ = false;
    $GroupDesc_ = false;

?>