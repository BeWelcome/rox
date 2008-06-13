<?php


class VisualComponent extends ObjectWithInjection
{
    /**
     * called by the framework, to inject some essential values..
     *
     * @param unknown_type $layoutkit
     */
    function setLayoutkit($layoutkit) {
        $this->layoutkit = $layoutkit;
        $this->words = $layoutkit->words;
    }
}


?>