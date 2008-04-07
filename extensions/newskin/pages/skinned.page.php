<?php


class PageWithActiveSkin extends PageWithRoxLayout
{
    protected function body()
    {
        echo '<br>Using a different skin!<br>';
        parent::body();
    }
}


?>