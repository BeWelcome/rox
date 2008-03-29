<?php

class VolunteerLinksWidget
{
    private function _init() {
        
    }
    
    public function render()
    {
        $this->_init();
        $this->_model = new VolunteermenuModel();
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
    }
}

class VolunteermenuWidget extends VolunteerLinksWidget {
    public function getTemplatePath() {
        return TEMPLATE_DIR.'apps/rox/volunteermenu.php';
    }
}

class VolunteerbarWidget extends VolunteerLinksWidget {
    public function getTemplatePath() {
        return TEMPLATE_DIR.'apps/rox/volunteerbar.php';
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

?>