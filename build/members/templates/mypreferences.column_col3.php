       <form method="post" id="preferences" >
        <?=$callback_tag ?>
        <input type="hidden" name="memberid" value="<?=$this->member->id?>" />
        <h3><?=$words->get('PreferenceLanguage')?></h3>
        <p><?=$words->get('PreferenceLanguageDescription')?></p>
        <select name="PreferenceLanguage" >
            <?php foreach ($languages as $lang) { ?>
                <option value="<?=$lang->id ?>" <?=($lang->id == $p['PreferenceLanguage']->Value) ? 'selected' : ''?> ><?=$lang->Name ?></option>
            <?php } ?>
        </select>

        <h3><?=$words->get('PageSpamFolderTitle')?></h3>
        <p><?=$words->get('PreferenceInSpamFolderDesc')?></p>
        <ul>
            <li><input type="radio" name="PreferenceInSpamFolder" value="Yes" <?=($p['PreferenceInSpamFolder']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('PreferencesSpamDelete')?></li>
            <li><input type="radio" name="PreferenceInSpamFolder" value="No" <?=($p['PreferenceInSpamFolder']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('PreferencesSpamKeep')?></li>
        </ul>

        <h3><?=$words->get('PreferencesNewsletter')?></h3>
        <p><?=$words->get('PreferenceAcceptNewsByMailDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceAcceptNewsByMail" value="Yes" <?=($p['PreferenceAcceptNewsByMail']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceAcceptNewsByMail" value="No" <?=($p['PreferenceAcceptNewsByMail']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>
        
        <h3><?=$words->get('PreferencesNewsletter')?></h3>
        <p><?=$words->get('PreferenceLinkPrivacyDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceLinkPrivacy" value="Yes" <?=($p['PreferenceLinkPrivacy']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceLinkPrivacy" value="No" <?=($p['PreferenceLinkPrivacy']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>
        
        <h3><?=$words->get('PreferenceForumFirstPage')?></h3>
        <p><?=$words->get('PreferenceForumFirstPageDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceForumFirstPage" value="Pref_ForumFirstPageLastPost" <?=($p['PreferenceForumFirstPage']->Value == 'Pref_ForumFirstPageLastPost') ? 'checked' : '' ?> /><?=$words->get('Pref_ForumFirstPageLastPost')?></li>
            <li><input type="radio" name="PreferenceForumFirstPage" value="Pref_ForumFirstPageCategory" <?=($p['PreferenceForumFirstPage']->Value == 'Pref_ForumFirstPageCategory') ? 'checked' : '' ?> /><?=$words->get('Pref_ForumFirstPageCategory')?></li>
        </ul>
        
        <h3><?=$words->get('PreferenceLocalEvent')?></h3>
        <p><?=$words->get('PreferenceLocalEventDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceLocalEvent" value="Yes" <?=($p['PreferenceLocalEvent']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceLocalEvent" value="No" <?=($p['PreferenceLocalEvent']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>
        
        <h3><?=$words->get('PreferenceForumCity')?></h3>
        <p><?=$words->get('PreferenceForumCityDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceForumCity" value="Yes" <?=($p['PreferenceForumCity']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceForumCity" value="No" <?=($p['PreferenceForumCity']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>
        
        <h3><?=$words->get('PreferenceForumRegion')?></h3>
        <p><?=$words->get('PreferenceForumRegionDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceForumRegion" value="Yes" <?=($p['PreferenceForumRegion']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceForumRegion" value="No" <?=($p['PreferenceForumRegion']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>
        
        <h3><?=$words->get('PreferenceCountryRegion')?></h3>
        <p><?=$words->get('PreferenceCountryRegionDesc ')?></p>
        <ul>
            <li><input type="radio" name="PreferenceCountryRegion" value="Yes" <?=($p['PreferenceCountryRegion']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceCountryRegion" value="No" <?=($p['PreferenceCountryRegion']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>

        <h3><?=$words->get('PreferenceAdvanced')?></h3>
        <p><?=$words->get('PreferenceAdvancedDesc')?></p>
        <ul>
            <li><input type="radio" name="PreferenceAdvanced" value="Yes" <?=($p['PreferenceAdvanced']->Value == 'Yes') ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferenceAdvanced" value="No" <?=($p['PreferenceAdvanced']->Value == 'No') ? 'checked' : '' ?> /><?=$words->get('no')?></li>
        </ul>

        <h3><?=$words->get('PreferencePublicProfile')?></h3>
        <p><?=$words->get('PreferencePublicProfileDesc')?></p>
        <ul>
            <li><input type="radio" name="PreferencePublicProfile" value="Yes" <?=($this->member->publicProfile == true) ? 'checked' : '' ?> /><?=$words->get('yes')?></li>
            <li><input type="radio" name="PreferencePublicProfile" value="No" <?=($this->member->publicProfile == false) ? 'checked' : '' ?> /><?=$words->get('no')?></li>
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
        
        </form>