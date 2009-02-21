<?php


class MyPreferencesPage extends MemberPage
{
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/YAML/screen/custom/profile.css';
       return $stylesheets;
    }

    protected function leftSidebar()
    {
        $words = $this->getWords();
        ?>
          <h3><?=$words->get('No')." ".$words->get('Actions');?></h3>
        <?php
    }


    protected function getSubmenuActiveItem()
    {
        return 'mypreferences';
    }


    protected function column_col3()
    {
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'myPreferencesCallback');
        // $pw_callback_tag = $formkit->setPostCallback('MembersController', 'passwordCallback');
        // $av_callback_tag = $formkit->setPostCallback('MembersController', 'avatarCallback');

        // $post_args = $args->post;
        // $errors   = isset($vars['errors']) ? $vars['errors'] : array();
        // $messages = isset($vars['messages']) ? $vars['messages'] : array();

        echo '
        <form method="post" id="preferences" >';

        echo $callback_tag;

        $this->myPreferencesFormFields();

        echo '
        </form>';
    }


    protected function myPreferencesFormFields()
    {
        $words = $this->getWords();
        $languages = array(
            array('id' => 0, 'name' => 'English'),
            array('id' => 1, 'name' => 'francais'),
            array('id' => 2, 'name' => 'deutsch'),
        );
        $p = $this->member->preferences;
        // var_dump ($p);

        $ii = 1;
?>


        <h3><?=$words->get('PreferenceLanguage')?></h3>
        <p><?=$words->get('PreferenceLanguageDescription')?></p>
        <select name="PreferenceLanguage" >
            <?php foreach ($languages as $lang) { ?>
                <option value="<?=$lang['id'] ?>"><?=$lang['name'] ?></option>
            <?php } ?>
        </select>

        <h3><?=$words->get('PageSpamFolderTitle')?></h3>
        <p><?=$words->get('PreferenceInSpamFolderDesc')?></p>
        <ul>
            <li><input type="radio" name="PreferencesSpam" value="keep_onemonth" /><?=$words->get('PreferencesSpamDelete')?></li>
            <li><input type="radio" name="PreferencesSpam" value="keep_forever" /><?=$words->get('PreferencesSpamKeep')?></li>
        </ul>

        <h3><?=$words->get('PreferencesNewsletter')?></h3>
        <p><?=$words->get('PreferenceAcceptNewsByMailDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferencesNewsletter" value="yes" /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferencesNewsletter" value="no" /><?=$words->get('no')?></li>
        </ul>

        <h3><?=$words->get('PreferenceAdvanced')?></h3>
        <p><?=$words->get('PreferenceAdvancedDesc')?></p>
        <ul>
            <li><input type="radio" name="PreferencesAdvancedFeature" value="yes" /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferencesAdvancedFeature" value="no" /><?=$words->get('no')?></li>
        </ul>

        <h3><?=$words->get('PreferenceForumFirstPage')?></h3>
        <p><?=$words->get('PreferenceForumFirstPageDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferencesForum" value="categories" /><?=$words->get('Pref_ForumFirstPageCategory')?></li>
            <li><input type="radio" name="PreferencesForum" value="recent" /><?=$words->get('Pref_ForumFirstPageLastPost')?></li>
        </ul>

        <h3><?=$words->get('PreferencePublicProfile')?></h3>
        <p><?=$words->get('PreferencePublicProfileDesc')?></p>
        <ul>
            <li><input type="radio" name="PreferencesPublic" value="yes" /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferencesPublic" value="no" /><?=$words->get('no')?></li>
        </ul>

        <h3><?=$words->get('PreferencesPassword')?></h3>
        <p><?=$words->get('PreferencesPassword')?></p>
        <dl>
            <dt><?=$words->get('PreferencesPasswordOld')?></dt>
            <dd><input type="password" name="passwordold" /></dd>

            <dt><?=$words->get('PreferencesPasswordNew')?></dt>
            <dd><input type="password" name="passwordnew" /></dd>

            <dt><?=$words->get('PreferencesPasswordConfirm')?></dt>
            <dd><input type="password" name="passwordconfirm" /></dd>
        </dl>

        <p><input type="submit" id="submit"  value="Submit" /></p>

        <?php
    }
}

?>
