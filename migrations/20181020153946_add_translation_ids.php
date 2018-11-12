<?php


use Rox\Tools\RoxMigration;

class AddTranslationIds extends RoxMigration
{
    public function up()
    {
        // group
        $this->AddWordCode('group.create.successful', 'You have submitted a request to set up a group %name% on BeWelcome. This request will be reviewed by the BW Moderators and you will be contacted as quickly as possible.', 'Flash notice after creating a new group. Make sure to keep the %name% tag.');
        $this->UpdateWordCode('GroupsCreateDescription', 'Thinking of creating a new group?<br>First, make sure that there is no group already covering your area or interest. Remember that it is better to join an active group covering a wider area than to start a very specialist or very local group that nobody sees.' .
            '<br>If you do want to start a new group, you need to submit a new group request to the Forum Moderators. Groups on BeWelcome are moderated, so you will need to agree to follow the <a href="/forums/rules#groups">Rules for Groups</a>.',
            'Text shown in the modal when you click on New group on the groups search page.');
        $this->AddWordCode('group.create', 'Create a group', 'Headline on the new group page');

        // admin
        $this->AddWordCode('admin.groups.awaiting.approval', '{0} No groups in queue|{1} One group in queue|]1,Inf[ %count% groups in queue', 'Button label make sure to keep the %count% so that the button shows the correct information.');
        $this->AddWordCode('admin.comments.reported', '{0} No reported comments|{1} One reported comment|]1,Inf[ %count% reported comments', 'Button label make sure to keep the %count% so that the button shows the correct information.');
        $this->AddWordCode('admin.spam.reported', '{0} No messages reported|{1} One message reported|]1,Inf[ %count% reported messages', 'Button label make sure to keep the %count% so that the button shows the correct information.');

        // home
        $this->AddWordCode('home.headline.bewelcome.cultural', 'BeWelcome is a cultural crossing network', 'Headline on the home page. Accompanied by home.abstract.bewelcome.cultural.');
        $this->AddWordCode('home.abstract.bewelcome.cultural', 'Which lets you share a place to stay, connect with travellers, meet up and find accommodation on your journey.', 'Abstract on the home page. Accompanied by home.headline.bewelcome.cultural.');
        $this->AddWordCode('home.button.join', 'Join BeWelcome!', 'Label for the signup button on home.');
        $this->AddWordCode('home.bewelcome.free', 'BeWelcome is and %link_start%will always be%link_end% a free, open source, non for profit, democratic community.', 'Make sure to keep %link_start% and %link_end% so that the link can be added.');
        $this->AddWordCode('home.headline.find.hosts', 'Find hosts and guests everywhere in the world', 'Headline on the home page');
        $this->UpdateWordCode('CopyrightByBV', '%link_start%BeVolunteer%link_end% and contributors.', 'Make sure to keep %link_start% and %link_end% so that the link can be added.');

        // activities
        $this->AddWordCode('activity.headline.join', 'Join', 'Headline for the section to decide if one joins the activity.');

        // signup
        $this->AddWordCode( 'signup.error.mothertongue', 'Please select a mother tongue.', 'Error message if no mother tongue was selected.');
        $this->AddWordCode('signup.error.username.taken', 'Please choose a different username that is at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is already in use.');
        $this->AddWordCode('signup.error.username', 'Username must be at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is malformed.');
        $this->AddWordCode('signup.popover.password', 'Please choose a strong password that is at least 6 characters long.', 'Popover text on signup regarding the password.');

        // profile
        $this->AddWordCode('profile.request.hosting', 'Request Stay', 'Menu entry on the members pages to sent a request for staying/be hosted by another member');
        $this->AddWordCode('profile.delete.cleanup', 'Please remove my data with the next cleanup (in the next 24 hours).', 'Label for the data cleanup checkbox on the retire profile page');
    }

    public function down()
    {
        // home
        $this->RemoveWordCode('home.headline.bewelcome.cultural', 'BeWelcome is a cultural crossing network', 'Headline on the home page. Accompanied by home.abstract.bewelcome.cultural.');
        $this->RemoveWordCode('home.abstract.bewelcome.cultural', 'which lets you share a place to stay, connect with travellers, meet up and find accommodation on your journey', 'Abstract on the home page. Accompanied by home.headline.bewelcome.cultural.');
        $this->RemoveWordCode('home.button.join', 'Join BeWelcome!', 'Label for the signup button on home.');
        $this->RemoveWordCode('home.bewelcome.free', 'BeWelcome is and %link_start%will always be%link_end% a free, open source, non for profit, democratic community', 'Make sure to keep %link_start% and %link_end% so that the link can be added.');
        $this->UpdateWordCode('CopyrightByBV', '%sBeVolunteer%s and contributors.', 'copyright statement in the footer. Please use one %s before and after BeVolunteer (without any space) and leave some space AFTER the last %s.');

        // activities
        $this->RemoveWordCode('activity.headline.join', 'Join', 'Headline for the section to decide if one joins the activity.');

        // signup
        $this->RemoveWordCode( 'signup.error.mothertongue', 'Please select a mother tongue.', 'Error message if no mother tongue was selected.');
        $this->RemoveWordCode('signup.error.username.taken', 'Please choose a different username that is at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is already in use.');
        $this->RemoveWordCode('signup.error.username', 'Username must be at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is malformed.');
        $this->RemoveWordCode('signup.popover.password', 'Please choose a strong password that is at least 6 characters long.', 'Popover text on signup regarding the password.');

        // profile
        $this->RemoveWordCode('profile.request.hosting', 'Request Stay', 'Menu entry on the members pages to sent a request for staying/be hosted by another member');
    }
}
