<?php


use Rox\Tools\RoxMigration;

class AddTranslationIds extends RoxMigration
{
    public function up()
    {
        // group
        $this->AddWordCode('group.create.successful',
            'You have submitted a request to set up a group %name% on BeWelcome. This request will be reviewed by the BW Moderators and you will be contacted as quickly as possible.', 'Flash notice after creating a new group. Make sure to keep the %name% tag.');
        $this->UpdateWordCode('GroupsCreateDescription', 'Thinking of creating a new group?<br>First, make sure that there is no group already covering your area or interest. Remember that it is better to join an active group covering a wider area than to start a very specialist or very local group that nobody sees.' .
            '<br>If you do want to start a new group, you need to submit a new group request to the Forum Moderators. Groups on BeWelcome are moderated, so you will need to agree to follow the <a href="/forums/rules#groups">Rules for Groups</a>.',
            'Text shown in the modal when you click on New group on the groups search page.');
        $this->AddWordCode('group.create', 'Create a group', 'Headline on the new group page');

        // admin
        $this->AddWordCode('admin.groups.awaiting.approval', '{0} No groups in queue|{1} One group in queue|]1,Inf[ %count% groups in queue', 'Button label make sure to keep the %count% so that the button shows the correct information.');
        $this->AddWordCode('admin.comments.reported', '{0} No reported comments|{1} One reported comment|]1,Inf[ %count% reported comments', 'Button label make sure to keep the %count% so that the button shows the correct information.');
        $this->AddWordCode('admin.spam.reported', '{0} No messages reported|{1} One message reported|]1,Inf[ %count% reported messages', 'Button label make sure to keep the %count% so that the button shows the correct information.');
        $this->AddWordCode('admin.comment.reported', 'Reported comments', 'Headline for the admin comments tool.');

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
        $this->AddWordCode('signup.confirm.email', 'Please confirm your email address', 'Subject line for the signup confirmation email');

        // profile
        $this->AddWordCode('profile.request.hosting', 'Request Stay', 'Menu entry on the members pages to sent a request for staying/be hosted by another member');
        $this->AddWordCode('profile.delete.cleanup', 'Please remove my data with the next cleanup (in the next 24 hours).', 'Label for the data cleanup checkbox on the retire profile page');

        // landing page
        $this->AddWordCode('landing.welcomeback', 'Welcome back', 'Greeting on the opening page after logging in, followed my the members username');
        $this->AddWordCode('landing.yourhostingstatus', 'Your hosting status is', 'After this text comes, for example \'Maybe hosting\'');
        $this->AddWordCode('landing.whereyougo', 'Where are you going?', 'Invitation to make people search for a specific place');
        $this->AddWordCode('landing.tab.messagesreceived', 'Messages received', 'Text for tab on landing page');
        $this->AddWordCode('landing.tab.notifications', 'Notifications', 'Text for tab on landing page');
        $this->AddWordCode('landing.button.all', 'All', 'Text for a button showing all messages');
        $this->AddWordCode('landing.button.unread', 'Unread', 'Text for a button showing unread messages');
        $this->AddWordCode('landing.button.myinbox', 'My inbox', 'Text for a link to members inbox');
        $this->AddWordCode('landing.tab.forum', 'Forum', 'Text for tab on landing page');
        $this->AddWordCode('landing.tab.activities ', 'Activities', 'Text for tab on landing page');
        $this->AddWordCode('landing.button.groups', 'groups', 'Filters groups discussions');
        $this->AddWordCode('landing.button.forum', 'forum', 'Filters forum discussions');
        $this->AddWordCode('landing.button.discussions', 'Discussions', 'Text for a button to go to the discussion pages');
        $this->AddWordCode('landing.button.mygroups', 'My groups', 'Text for a button to go to the members groups');
        $this->AddWordCode('landing.button.subscriptions', 'My subscriptions', 'Text for a button to go to forum subscriptions');
        $this->AddWordCode('landing.activities.allactivities', 'All activities', 'Text for a button that redirects to all activities');
        $this->AddWordCode('landing.activities.myactivities', 'My activities', 'Text for a button that redirects to activities of the member');
        $this->AddWordCode('landing.activities.create', 'Create activity', 'Text for a button to create an activity');
        $this->AddWordCode('landing.beinvolved.title', 'Be Involved', 'Title for information box');
        $this->AddWordCode('landing.beinvolved.subtitle', 'help the community', 'Subtitle of \'Be Involved\'');
        $this->AddWordCode('landing.beinvolved.goalfor', 'Goal for', 'Text comes in front of a year, for example \'Goal for 2020-2021\'');
        $this->AddWordCode('landing.beinvolved.pleasedonate', 'Please donate', 'Text on button to ask for donations');
        $this->AddWordCode('landing.beinvolved.intro', 'BeWelcome is run by volunteers - free of charge and open source. Volunteering for the project is a great opportunity for you to get to know enthusiastic people who really believe in promoting hospitality and respect for each other. And it\'s fun, too!', 'Motivational text to ask people to volunteer and/or donate');
        $this->AddWordCode('landing.beinvolved.helpbewelcome', 'Help BeWelcome', 'Text for a button to redirect members to become volunteers');
        $this->AddWordCode('landing.bwnews.title', 'BeWelcome News', 'Title of info box');
        $this->AddWordCode('landing.bwnews.subtitle', 'and other updates', 'Subtitle for \'BeWelcome News\'');
        $this->AddWordCode('landing.bwnews.allnews', 'All news', 'Button text to see all the news messages');
        $this->AddWordCode('landing.beinformed.title', 'Be Informed', 'Title of info box');
        $this->AddWordCode('landing.beinformed.subtitle', 'Transparency', 'Subtitle of \'Be Informed\', keep it short');
        $this->AddWordCode('landing.beinformed.about', 'About', 'keep it very short');
        $this->AddWordCode('landing.beinformed.faq', 'FAQ', 'Frequently Asked Questions - keep it very short');
        $this->AddWordCode('landing.beinformed.safety', 'Safety', 'keep it very short');
        $this->AddWordCode('landing.beinformed.bevolunteer', 'BeVolunteer', 'It\'s the name of the organisation');
        $this->AddWordCode('landing.beinformed.annualreport', 'Annual Report', 'keep it very short');
        $this->AddWordCode('landing.beinformed.finances', 'Finances', 'keep it very short');
        $this->AddWordCode('landing.beinformed.termsofuse', 'Terms of Use', 'keep it very short');
        $this->AddWordCode('landing.beinformed.privacypolicy', 'Privacy Policy', 'keep it very short');

        // donation page
        $this->AddWordCode('donation.donatetime', 'Donate time', 'explanation how people can volunteer for BeWelcome');
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

        // landing page
        $this->RemoveWordCode('landing.welcomeback', 'Welcome back', 'Greeting on the opening page after logging in, followed my the members username');
        $this->RemoveWordCode('landing.yourhostingstatus', 'Your hosting status is', 'After this text comes, for example \'Maybe hosting\'');
        $this->RemoveWordCode('landing.whereyougo', 'Where are you going?', 'Invitation to make people search for a specific place');
        $this->RemoveWordCode('landing.tab.messagesreceived', 'Messages received', 'Text for tab on landing page');
        $this->RemoveWordCode('landing.tab.notifications', 'Notifications', 'Text for tab on landing page');
        $this->RemoveWordCode('landing.button.all', 'All', 'Text for a button showing all messages');
        $this->RemoveWordCode('landing.button.unread', 'Unread', 'Text for a button showing unread messages');
        $this->RemoveWordCode('landing.button.myinbox', 'My inbox', 'Text for a link to members inbox');
        $this->RemoveWordCode('landing.tab.forum', 'Forum', 'Text for tab on landing page');
        $this->RemoveWordCode('landing.tab.activities ', 'Activities', 'Text for tab on landing page');
        $this->RemoveWordCode('landing.button.groups', 'groups', 'Filters groups discussions');
        $this->RemoveWordCode('landing.button.forum', 'forum', 'Filters forum discussions');
        $this->RemoveWordCode('landing.button.discussions', 'Discussions', 'Text for a button to go to the discussion pages');
        $this->RemoveWordCode('landing.button.mygroups', 'My groups', 'Text for a button to go to the members groups');
        $this->RemoveWordCode('landing.button.subscriptions', 'My subscriptions', 'Text for a button to go to forum subscriptions');
        $this->RemoveWordCode('landing.activities.allactivities', 'All activities', 'Text for a button that redirects to all activities');
        $this->RemoveWordCode('landing.activities.myactivities', 'My activities', 'Text for a button that redirects to activities of the member');
        $this->RemoveWordCode('landing.activities.create', 'Create activity', 'Text for a button to create an activity');
        $this->RemoveWordCode('landing.beinvolved.title', 'Be Involved', 'Title for information box');
        $this->RemoveWordCode('landing.beinvolved.subtitle', 'help the community', 'Subtitle of \'Be Involved\'');
        $this->RemoveWordCode('landing.beinvolved.goalfor', 'Goal for', 'Text comes in front of a year, for example \'Goal for 2020-2021\'');
        $this->RemoveWordCode('landing.beinvolved.pleasedonate', 'Please donate', 'Text on button to ask for donations');
        $this->RemoveWordCode('landing.beinvolved.intro', 'BeWelcome is run by volunteers - free of charge and open source. Volunteering for the project is a great opportunity for you to get to know enthusiastic people who really believe in promoting hospitality and respect for each other. And it\'s fun, too!', 'Motivational text to ask people to volunteer and/or donate');
        $this->RemoveWordCode('landing.beinvolved.helpbewelcome', 'Help BeWelcome', 'Text for a button to redirect members to become volunteers');
        $this->RemoveWordCode('landing.bwnews.title', 'BeWelcome News', 'Title of info box');
        $this->RemoveWordCode('landing.bwnews.subtitle', 'and other updates', 'Subtitle for \'BeWelcome News\'');
        $this->RemoveWordCode('landing.bwnews.allnews', 'All news', 'Button text to see all the news messages');
        $this->RemoveWordCode('landing.beinformed.title', 'Be Informed', 'Title of info box');
        $this->RemoveWordCode('landing.beinformed.subtitle', 'Transparency', 'Subtitle of \'Be Informed\', keep it short');
        $this->RemoveWordCode('landing.beinformed.about', 'About', 'keep it very short');
        $this->RemoveWordCode('landing.beinformed.faq', 'FAQ', 'Frequently Asked Questions - keep it very short');
        $this->RemoveWordCode('landing.beinformed.safety', 'Safety', 'keep it very short');
        $this->RemoveWordCode('landing.beinformed.bevolunteer', 'BeVolunteer', 'It\'s the name of the organisation');
        $this->RemoveWordCode('landing.beinformed.annualreport', 'Annual Report', 'keep it very short');
        $this->RemoveWordCode('landing.beinformed.finances', 'Finances', 'keep it very short');
        $this->RemoveWordCode('landing.beinformed.termsofuse', 'Terms of Use', 'keep it very short');
        $this->RemoveWordCode('landing.beinformed.privacypolicy', 'Privacy Policy', 'keep it very short');

        // donation page
        $this->RemoveWordCode('donation.donatetime', 'Donate time', 'explanation how people can volunteer for BeWelcome');
    }
}
