<?php
class ResetPasswordPage extends PageWithActiveSkin
{
    public function teaserHeadline()
    {
        $words = $this->getWords();
        return "<h1>" . $words->get("ResetPassword") . "</h1>";
    }

    #[\Override]
    protected function getColumnNames()
    {
        // we don't need the other columns
        return ['col3'];
    }
}
?>