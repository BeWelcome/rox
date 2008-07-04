<?php


class VolunteerbarWidget extends RoxWidget
{
    public function render()
    {
        $R = MOD_right::get();
        if (!$R->hasRightAny()) {
            // donothing
        } else {
            $model = new VolunteerbarModel();
            $args['numberPersonsToBeAccepted'] = 0;
            $args['numberPersonsToBeChecked'] = 0;
            if ($R->hasRight("Accepter")) {
                $numberPersonsToBeAccepted = $model->getNumberPersonsToBeAccepted();
                $AccepterScope = $R->rightScope('Accepter');
                $numberPersonsToBeChecked =
                $model->getNumberPersonsToBeChecked($AccepterScope);
            }
                        
            $args['numberPersonsToAcceptInGroup']=0 ;
            if ($R->hasRight("Group")) {
                $args['$numberPersonsToAcceptInGroup'] = $model->getNumberPersonsToAcceptInGroup($R->rightScope('Group'));
            }
            
            $args['numberMessagesToBeChecked'] = 0;
            $args['numberSpamToBeChecked'] = 0;
            if ($R->hasRight("Checker")) {
                $args['numberMessagesToBeChecked'] = $model->getNumberMessagesToBeChecked();
                $args['numberSpamToBeChecked'] = $model->getNumberSpamToBeChecked();
            }
            
            if ($this->layoutkit) { //quick work-around
                $this->layoutkit->showTemplate('apps/rox/volunteerbar.php', $args);
            } else {
                echo "Please fix volunteerbar.widget.php";
            }
        }
    }
}


?>