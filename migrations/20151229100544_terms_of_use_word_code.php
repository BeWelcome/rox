<?php

use Rox\Tools\RoxMigration;

class TermsOfUseWordCode extends RoxMigration
{
    public function up()
    {
        $this->AddWordCode('TermsOfUseWarning','<emph>Please note that the legally binding version of the Terms of Use is the French version.</emph>', 'Shown on the terms of use page in case the site isn\'t displayed in French');
        $this->AddWordCode('TermsOfUseFullText','Placeholder, edit using the translation tool.', 'The full text of the terms of use.');
    }

    public function down()
    {
        $this->RemoveWordCode('TermsOfUseWarning');
        $this->RemoveWordCode('TermsOfUseFullText');
    }
}
