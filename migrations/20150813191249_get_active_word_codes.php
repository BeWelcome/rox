<?php

use Phinx\Migration\AbstractMigration;

class GetActiveWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     */
    public function up()
    {
        $this->AddWordCode('HelpBeWelcomeContact','contact','the word "contact" before the person to contact on the Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeDevTags','<a href="http://trac.bewelcome.org/">trac</a> &#124; <a href="https://github.com/BeWelcome/rox">github</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeTestingTags','<a href="groups/62">group</a> &#124; <a href="https://alpha.bewelcome.org">alpha</a> &#124; <a href="https://beta.bewelcome.org">beta</a> &#124; <a href="http://trac.bewelcome.org/">trac</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeSupportTags','<a href="http://www.bewelcome.org/groups/79/wiki">how to join</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeLocalTags','<a href="activities">local activities</a> &#124; <a href="activities/create">create an activity</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeDonationTags','<a href="donate">donate!</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeBVTags','<a href="http://www.bevolunteer.org">BeVolunteer website</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeDesignTags','<a href="groups/56">group</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeNMBWTags','<a href="wiki/NewMemberBeWelcomeGroup">how to join</a> &#124; <a href="groups/1169">public group</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeTranslateTags','<a href="groups/60/wiki">how to join</a> &#124; <a href="groups/60">group</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeSuggestionsTags','<a href="suggestions/team">how to join</a> &#124; <a href="groups/1582">group</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomePRTags','<a href="groups/64/wiki">wiki</a> &#124; <a href="groups/64">group</a>','Info shown on Help BeWelcome page');
        $this->AddWordCode('HelpBeWelcomeModTags','<a href="wiki/moderation_team">wiki</a> &#124; <a href="wiki/Forum_Moderator_Application">how to apply</a> &#124; <a href="groups">group</a>','Info shown on Help BeWelcome page');
    }

    public function down()
    {

    }
}