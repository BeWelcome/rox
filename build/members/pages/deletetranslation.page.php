<?php


class DeleteTranslationPage extends EditProfilePage
{    

    protected function column_col3()
    {
		$member = $this->member;
		$ww = $this->ww;
		$layoutkit = $this->layoutkit;
		$formkit = $layoutkit->formkit;
		$callback_tag = $formkit->setPostCallback('MembersController', 'deleteTranslationCallback');
		$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request); 
		$lang = $this->model->get_profile_language();
		$profile_language = $lang->id;
		?>
		<form method="post" action="<?=$page_url?>" name="signup" id="profile" enctype="multipart/form-data">
		<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
		<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
		<?=$callback_tag?>
			<h3><?=$ww->deleteProfileTranslation?>: <?=$lang->Name?></h3>
			<p><?=$ww->AreYouSure?></p>
			<button class="button" type="submit" name="choice" value="yes" ><?=$ww->yes?></button>
			<button class="button" type="submit" name="choice" value="no" ><?=$ww->no?></button>
		</form>
		<?php
		
	}
}




?>