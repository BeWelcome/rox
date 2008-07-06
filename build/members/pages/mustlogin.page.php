<?php


class MembersMustloginPage extends RoxPageView
{
    protected function column_col3()
    {
        $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
        $loginWidget->render();
    }
}


?>
