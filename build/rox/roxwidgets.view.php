<?php

class VolunteerLinksWidget
{
    private function _init() {
        
    }
    
    public function render()
    {
        $this->_init();
        $this->_model = new VolunteerbarModel();

		if (empty($_SESSION['IdMember'])) {
			return ; // Do nothing if user is not identified (thi cannot be a volunteer)
		}

        $R = MOD_right::get();
        $mayViewBar = $R->hasRightAny();
        
        if ($mayViewBar) {
            $numberPersonsToBeAccepted = 0;
            $numberPersonsToBeChecked = 0;
            if ($R->hasRight("Accepter")) {
                $numberPersonsToBeAccepted = $this->_model->getNumberPersonsToBeAccepted();
                $AccepterScope = $R->rightScope('Accepter');
                $numberPersonsToBeChecked =
                $this->_model->getNumberPersonsToBeChecked($AccepterScope);
            }
                        
            $numberPersonsToAcceptInGroup=0 ;
            if ($R->hasRight("Group")) {
                $numberPersonsToAcceptInGroup = $this->_model->getNumberPersonsToAcceptInGroup($R->rightScope('Group'));
            }
            
            $numberMessagesToBeChecked = 0;
            $numberSpamToBeChecked = 0;
            if ($R->hasRight("Checker")) {
                $numberMessagesToBeChecked = $this->_model->getNumberMessagesToBeChecked();
                $numberSpamToBeChecked = $this->_model->getNumberSpamToBeChecked();
            }

            require $this->getTemplatePath();
        }
    } // end of render() ;
}

class VolunteermenuWidget extends VolunteerLinksWidget {
    public function getTemplatePath() {
        return TEMPLATE_DIR.'../build/admin/templates/volunteermenu.php';
    }
}

class FooterWidget {
    public function render() {
        
    }
}

class FlaglistWidget {
    public function render() {
        
    }
}

class LinkSinglePictureLinkpathWidget {

    private function _init() {
        
    }
	
	public function render($fromID,$toID,$cssID) {
        $this->_init();
        $this->_model = new LinkModel();
        $user = new APP_user();
		$logged = $user->isBWLoggedIn('NeedMore,Pending');
		
		if ($fromID != $toID && $logged) {
			$linkpath = $this->_model->getLinksFull($fromID,$toID,1);
			if ($linkpath) require 'templates/link.widget.singlepicturelinkpath.php';
		}
	}

		
		
}

?>
