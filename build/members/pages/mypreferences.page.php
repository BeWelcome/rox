<?php


class MyPreferencesPage extends MemberPage
{
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
        <div class="info" >
        <form method="post" id="preferences" >';
        
        echo $callback_tag;
        
        $this->myPreferencesFormFields();
        
        echo '
        </form>
        </div>';
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
		<?=($p[$ii]->Value != "") ? $p[$ii]->Value : $p[$ii]->DefaultValue?> <br />
        <fieldset>
        <legend class="heading_legend"><?=$words->get($p[$ii]->codeName)?></legend>
        <p>
        <?=$words->get($p[$ii]->codeDescription)?>By default, all text on the website should be displayed in<br>
        <select name="PreferenceLanguage"  class="prefsel">
        <?php foreach ($languages as $lang) { ?>
        <option value="<?=$lang['id'] ?>"><?=$lang['name'] ?></option>
        <?php } ?>
        </select>
        </p>
        
        <br>
        <p>
        If a translation in that language is not available, try these other languages that I defined in my profile:
        <ol>
        <li>russion</li>
        <li>polski</li>
        </ol>
        You can edit this list on the <a href="editmyprofile" target="new">editmyprofile page</a>. 
        </p>
        
        <?php /*
            * I take this one out.
            * Messages should in any case be kept until they are deleted by the member.
            * No need for a preference.
            * ?>
        <h3>Spam Folder</h3>
        If I mark a message as spam, it will
        <input type="radio" name="PreferenceInSpamFolder" value="keep_onemonth"> stay there for one month<br>
        <input type="radio" name="PreferenceInSpamFolder" value="keep_forever"> stay there forever<br>
        <?php */ ?>
        <?$ii++?>
        <br>
        </fieldset>
        
        <fieldset>
        <legend class="heading_legend"><?=$words->get($p[$ii]->codeName)?></legend>
        <p>
        <?=$words->get($p[$ii]->codeDescription)?>There are different stylesheets you can choose from..<br>
        <input type="radio" name="PreferenceStyleSheet" value="stylesheet1"> Default (Orange)<br>
        <input type="radio" name="PreferenceStyleSheet" value="stylesheet1"> HC Nostalgia<br>
        <input type="radio" name="PreferenceStyleSheet" value="stylesheet1"> Micha old experiment<br>
        <input type="radio" name="PreferenceStyleSheet" value="stylesheet1"> YAML experiment<br>
        </p>
        <?$ii++?>
        <br>
        </fieldset>
        
        <fieldset>
        <legend  class="heading_legend"><?=$words->get($p[$ii]->codeName)?>Advanced Features</legend>
        <p>
        <?=$words->get($p[$ii]->codeDescription)?>Some features can be switched on and off.
        <ul>
        <li>Notes on profile</li>
        <li>Drafts messages</li>
        <li>Special relations</li>
        <li>Other ?</li>
        </ul>
        <input type="radio" name="PreferenceAdvanced" value="advanced"> I want to use <strong>all</strong> features<br>
        <input type="radio" name="PreferenceAdvanced" value="normal"> I don't need advanced features, I prefer a light interface<br>
        </p>
        <?$ii++?>
        <br>
        </fieldset>
        
        <fieldset>
        <legend  class="heading_legend"><?=$words->get($p[$ii]->codeName)?>Privacy Settings</legend>
        <p>
        <?=$words->get($p[$ii]->codeDescription)?>Who can see your profile and your avatar picture?<br>
        <input type="radio" name="PreferencePublicProfile" value="everyone"> Everyone can see my profile and my avatar picture.<br>
        <input type="radio" name="PreferencePublicProfile" value="only_members"> Only logged-in BeWelcome members can see my profile and my avatar picture.<br>
        </p>
        <br>
        </fieldset>
        
        <fieldset>
        <legend  class="heading_legend"><?=$words->get($p[$ii]->codeName)?>Mail Notifications</legend>
        <p>
        What about notifications?<br>
        <input type="radio" name="notify" value="opt1"> option 1.<br>
        <input type="radio" name="notify" value="opt2"> option 2.<br>
        </p>
        <?$ii++?>
        <br>
        </fieldset>
        
        <hr>
        
        <fieldset>
        <legend  class="heading_legend"><?=$words->get($p[$ii]->codeName)?>Change my Password</legend>
        <p>
        <?=$words->get($p[$ii]->codeDescription)?>Old password<br>
        <input type="password" name="pw_old"><br>
        Old password, again<br>
        <input type="password" name="pw_old_confirm"><br>
        New password<br>
        <input type="password" name="pw_new"><br>
        </p>
        </fieldset>
        
        <INPUT type="submit"  id="submit"  value="Submit" >
        
        <?php
    }
}


?>