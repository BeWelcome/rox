<?php
class ResetPasswordFinishPage extends PageWithActiveSkin
{
    public function teaserHeadline()
    {
        $words = $this->getWords();
        return "<h1>" . $words->get("ResetPassword") . "</h1>";
    }
    
    public function leftSidebar() {
        return;
    }
}
?>