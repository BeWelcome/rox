<?php


class SignupMailConfirmPage extends SignupBasePage
{

    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        if (!$this->error) {
            echo $words->get('SignupMailConfirmedTitle');
        } else {
            echo $words->get('SignupMailConfirmedErrorTitle');
        }
    }

    protected function column_col2()
    {
    }

    protected function column_col3()
    {
        $words = $this->layoutkit->words;
        if (!$this->error) {
            echo $words->get('SignupMailConfirmed');
        } else {
            echo '<p class="error">'.$words->get('SignupMailConfirmedError'.$this->error).'</p>';
        }
        echo "<p>&nbsp;</p>";
        $widg = $this->createWidget('LoginFormWidget');
        $widg->render();
    }
}
