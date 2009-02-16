<?php
        $member = $this->member;
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'editMyProfileCallback');
        
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $profile_language_name = $lang->Name;
        $languages = $member->profile_languages; 
        $languages_spoken = $member->languages_spoken;
        $languages_all = $member->languages_all; 
        $words = $this->getWords();

        $vars = $this->editMyProfileFormPrepare($member);

        if (!$memory = $formkit->getMemFromRedirect()) {
            // no memory
            // echo 'no memory';
        } else {
            // from previous form
            if ($memory->post) {
                foreach ($memory->post as $key => $value) {
                    $vars[$key] = $value;
                }
                // update $vars for messengers
                if(isset($vars['messengers'])) { 
                    $ii = 0;
                    foreach($vars['messengers'] as $me) {
                        $val = 'chat_' . $me['network_raw'];
                        $vars['messengers'][$ii++]['address'] = $vars[$val];
                    }
                }
                // update $vars for $languages
                if(!isset($vars['languages_selected'])) { 
                    $vars['languages_selected'] = array();
                }
                $ii = 0;
                $ii2 = 0;
                $lang_used = array();
                foreach($vars['memberslanguages'] as $lang) {
                    if (ctype_digit($lang) and !in_array($lang,$lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                        $vars['languages_selected'][$ii]->IdLanguage = $lang;
                        $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                        array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                        $ii++;
                    }
                    $ii2++;
                }
            }
            // problems from previous form
            if (is_array($memory->problems)) {
                require_once 'templates/editmyprofile_warning.php';
            }
        }
        // var_dump($vars);

    $urlstring = 'editmyprofile';
    require 'profileversion.php'; 
?>
            	&nbsp;	&nbsp;	&nbsp;	&nbsp; 
            <select id="add_language">
                <option>- Add language -</option>
                <optgroup label="Your languages">
                  <?php
                  foreach ($languages_spoken as $lang) { 
                  if (!in_array($lang->ShortCode,$languages))
                  echo '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>'; 
                  } ?>
                </optgroup>
                <optgroup label="All languages">
                  <?php
                  foreach ($languages_all as $lang) { 
                  if (!in_array($lang->ShortCode,$languages))
                  echo '<option value="'.$lang->ShortCode.'">' . $lang->Name . '</option>'; 
                  } ?>
                </optgroup>
            </select>
             
        <hr>
        <?php
        // Check for errors and update status and display a message
        if (isset($vars['errors']) and count($vars['errors']) > 0) {
              echo '<div class="error">'.$words->get('EditmyprofileError').'</div>';
        } else {
            if ($this->status == 'finish') {
                  echo '<div class="note check">'.$words->get('EditmyprofileFinish').'</div>';
            }
            $vars['errors'] = array();
        }
        ?>
        <br />
        <form method="post" action="<?=$page_url?>" name="signup" id="profile">
        <input type="hidden"  name="memberid"  value="<?=$member->id?>" />
        <input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
        <?php
        
        echo $callback_tag;
        
        $this->editMyProfileFormContent($vars);
        
?>
        </form>
        <script type="text/javascript">//<!--
            function linkDropDown(event){
                var element = Event.element(event);
                var index = element.selectedIndex;
                var lang = element.options[index].value;
                window.location.href = http_baseuri + 'editmyprofile/' + lang;
            }
            
            var iterator = 1;
            function insertNewTemplate(event){
                var element = Event.element(event);
                if (iterator == 7) {
                    Event.stopObserving(element, 'click', insertNewTemplate);
                    element.disable;
                }
                var node1 = $('lang'+iterator);
                var node2 = node1.cloneNode(true);
                iterator++;
                node2.setAttribute('id', 'lang'+iterator);
                node1.parentNode.appendChild(node2);
            }
            
            document.observe("dom:loaded", function() {
              new FieldsetMenu('profile-edit-form', {active: "profilesummary"});
              $('langbutton').observe('click',insertNewTemplate);
              $('add_language').observe('change',linkDropDown);
            });
        //-->
        </script>
        </div>