<?php
class SignupResentMailPage extends SignupBasePage
{

    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        if (!$this->error) {
            echo $words->get('SignupResentMailTitle');
        } else {
            echo $words->get('SignupResentMailErrorTitle');
        }
    }

    protected function column_col2()
    {
    }

    protected function column_col3()
    {
        $words = $this->layoutkit->words;
        echo "<p>&nbsp;</p>";
        if (!$this->error) {
            echo '<p>' . $words->get('SignupResentMail') . '</p>';
        } else {
            echo '<p class="error">'.$words->get('SignupResentMail'.$this->error).'</p>';
            echo "<p>&nbsp;</p>";
            $widg = $this->createWidget('LoginFormWidget');
            $widg->render();
        }
    }
}
?>