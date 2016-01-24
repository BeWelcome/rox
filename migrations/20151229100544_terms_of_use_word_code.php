<?php

use Rox\Tools\RoxMigration;

class TermsOfUseWordCode extends RoxMigration
{
    public function up()
    {
        $this->AddWordCode('TermsOfUseWarning','<emph>Please note that the legally binding version of the Terms of Use is the French version.</emph>', 'Shown on the terms of use page.');
        $this->AddWordCode('TermsOfTranslation','A translation to your language might be here (otherwise it is in English)', 'Shown on the terms of use page above the French version.');
        $this->AddWordCode('TermsOfUseFullText','Placeholder, edit using the translation tool.', 'The full text of the terms of use.');
    }

    public function down()
    {
        $this->RemoveWordCode('TermsOfUseWarning');
        $this->RemoveWordCode('TermsOfTranslation');
        $this->RemoveWordCode('TermsOfUseFullText');
    }
}
