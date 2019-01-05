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

        // translations
        $this->AddWordCode('translation.missing', 'Translation missing', 'Hint that a translation is missing in the list of translations.');
        $this->AddWordCode('translation.edit', 'Translation edited', 'Flashbag that a translation was updated.');

        // home
        $this->AddWordCode('home.headline.bewelcome.cultural', 'BeWelcome is a cultural crossing network', 'Headline on the home page. Accompanied by home.abstract.bewelcome.cultural.');
        $this->AddWordCode('home.abstract.bewelcome.cultural', 'Which lets you share a place to stay, connect with travellers, meet up and find accommodation on your journey.', 'Abstract on the home page. Accompanied by home.headline.bewelcome.cultural.');
        $this->AddWordCode('home.button.join', 'Join BeWelcome!', 'Label for the signup button on home.');
        $this->AddWordCode('home.bewelcome.free', 'BeWelcome is and %link_start%will always be%link_end% a free, open source, non for profit, democratic community.', 'Make sure to keep %link_start% and %link_end% so that the link can be added.');
        $this->AddWordCode('home.headline.find.hosts', 'Find hosts and guests everywhere in the world', 'Headline on the home page');
        $this->UpdateWordCode('CopyrightByBV', '%link_start%BeVolunteer%link_end% and contributors.', 'Make sure to keep %link_start% and %link_end% so that the link can be added.');

        // activities
        $this->AddWordCode('activity.headline.join', 'Join', 'Headline for the section to decide if one joins the activity.');

        // search
        $this->AddWordCode('search.show.advanced', 'Advanced options', 'Button label on the search page (Find Members).');
        $this->AddWordCode('search.find.members', 'Find Members', 'Button label on the search page (Find Members).');
        $this->AddWordCode('search.show.map', 'Show map', 'Checkbox to toggle the map display.');
        $this->AddWordCode('search.location.invalid', 'Please select a location from the auto complete list', 'Information shown when there isn\'t enough information to find members.' );

        // signup
        $this->AddWordCode( 'signup.error.mothertongue', 'Please select a mother tongue.', 'Error message if no mother tongue was selected.');
        $this->AddWordCode('signup.error.username.taken', 'Please choose a different username that is at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is already in use.');
        $this->AddWordCode('signup.error.username', 'Username must be at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is malformed.');
        $this->AddWordCode('signup.popover.password', 'Please choose a strong password that is at least 6 characters long.', 'Popover text on signup regarding the password.');
        $this->AddWordCode('signup.confirm.email', 'Please confirm your email address', 'Subject line for the signup confirmation email');
        $this->AddWordCode( 'signup.error.name.empty', 'Please provide a name.', 'Error message shown when first or last name are left empty.');

        // profile
        $this->AddWordCode('profile.request.hosting', 'Request Stay', 'Menu entry on the members pages to sent a request for staying/be hosted by another member');
        $this->AddWordCode('profile.delete.cleanup', 'Please remove my data with the next cleanup (in the next 24 hours).', 'Label for the data cleanup checkbox on the retire profile page');
        $this->AddWordCode('profile.accommodation.hes.label', 'Hosting Eagerness', 'Label for the Hosting Eagerness Slider');
        $this->AddWordCode('profile.accommodation.hes.dont-boost', 'Move to end of search results instead of to the top', 'Label for the Hosting Eagerness Slider do not boost checkbox');
        $this->AddWordCode('profile.accommodation.hes.helptext', 'You can influence your position in search results by selecting a date in the future. The further away the date is the less high your boost becomes. You can also decide to be shown at the end of the list instead by ticking the checkbox. ', 'Help text for the hosting eagerness slider.');

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
        $this->AddWordCode('landing.tab.activities', 'Activities', 'Text for tab on landing page');
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

        // credits
        $this->AddWordCode('credits.title', 'Credits', 'Title for the credits page');
        $this->AddWordCode('credits.headline', 'Credits', 'Headline on the credits page');
        $this->AddWordCode('credits.abstract', 'This website wouldn\'t be possible without the help of other.', 'Title for the credits page');

        // requests
        $this->AddWordCode('request.guest.open', 'No reply yet by your host', 'Request status for guest (open)');
        $this->AddWordCode('request.guest.cancelled', 'Request cancelled on your behalf.', 'Request status for guest (cancelled)');
        $this->AddWordCode('request.guest.declined', 'Request has been declined.', 'Request status for guest (declined by host)');
        $this->AddWordCode('request.guest.accepted', 'Request has been accepted.', 'Request status for guest (accepted by host)');
        $this->AddWordCode('request.guest.tentatively', 'Your host has replied "Maybe" to this request', 'Request status for guest (host hasn\'t decided yet)');
        $this->AddWordCode('request.host.open', 'Please reply to this request.', 'Request status for host (just received)');
        $this->AddWordCode('request.host.declined', 'You declined this request.', 'Request status for host (declined)');
        $this->AddWordCode('request.host.accepted', 'You accepted this request.', 'Request status for host (hosting)');
        $this->AddWordCode('request.host.tentatively', 'You have replied "Maybe" to this request', 'Request status for host (undecided)');
        $this->AddWordCode('request.not.hosting', 'This person says they are not willing to host.<br>You might send a message instead.', 'Flash message shown if accommodation is set to No.');

        // admin volunteer tools
        $this->AddWordCode('admin.tools.title', 'Volunteer Tools', 'Title of the volunteer tools pages');
        $this->AddWordCode('admin.tools.headline', 'Volunteer Tools', 'Headline on the volunteer tools pages');
        $this->AddWordCode('admin.tools.change_username', 'Change username', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.find_user', 'Find member', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.check_feedback', 'Check Feedback', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.check_spam_messages', 'Check spam', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.damage_done', 'Damage done', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.age_by_country', 'Country by age', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.messages_last_week', 'Messages last week', 'Submenu item on the volunteer tools pages');
        $this->AddWordCode('admin.tools.nothing', 'Nothing to show. Odd isn\'t it?', 'Text shown when nothing was found to display.');

        // requests
        $this->AddWordCode('request.arrival', 'Arrival', 'Label used on request form and in email notification');
        $this->AddWordCode('request.departure', 'Departure', 'Label used on request form and in email notification');
        $this->AddWordCode('request.flexible', 'Flexible', 'Label used on request form');
        $this->AddWordCode('request.number_of_travellers', 'Number of travellers', 'Label user on request form and in email notification');

        // emails
        $this->AddWordCode('email.greeting', 'Dear %username%,', 'Greeting in emails. Make sure to include the %username% when translating.');
        $this->AddWordCode('email.message', '<a href="https://www.bewelcome.org/members/%username%">%username%</a> sent you a message.', 'First paragraph in a message. Make sure to include the %username% when translating.');
        $this->AddWordCode('email.request.stay', '%username% would like to stay with you.', 'first paragraph in a hosting request');
        $this->AddWordCode('email.request.reply.host', '%username% replied to your request to stay with them.', 'first paragraph in a reply from the host');
        $this->AddWordCode('email.request.reply.host.open', 'Please read your messages below.', 'State of the request in reply from host (accepted)');
        $this->AddWordCode('email.request.reply.host.accepted', 'You can stay with %username%.', 'State of the request in reply from host (accepted)');
        $this->AddWordCode('email.request.reply.host.declined', '%username% declined to host you.', 'State of the request in reply from host (declined)');
        $this->AddWordCode('email.request.reply.host.tentatively', '%username% has replied "Maybe" to this request', 'State of the request in reply from host (maybe)');
        $this->AddWordCode('email.request.reply.guest', '%username% replied to your request to stay with them.', 'First paragraph in a reply from the host. Make sure to include the %username when translating.');
        $this->AddWordCode('email.request.reply.guest.open', 'This request is still open.', 'State of the request in reply from guest (accepted)');
        $this->AddWordCode('email.request.reply.guest.accepted', 'You accepted this request.', 'State of the request in reply from guest (accepted)');
        $this->AddWordCode('email.request.reply.guest.declined', 'You declined this request.', 'State of the request in reply from guest (declined)');
        $this->AddWordCode('email.request.reply.guest.tentatively', 'You have replied "Maybe" to this request', 'State of the request in reply from guest (maybe)');
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

        // search
        $this->RemoveWordCode('search.show.advanced', 'Advanced options', 'Button label on the search page (Find Members).');
        $this->RemoveWordCode('search.find.members', 'Find Members', 'Button label on the search page (Find Members).');
        $this->RemoveWordCode('search.show.map', 'Show map', 'Checkbox to toggle the map display.');
        $this->RemoveWordCode('search.location.invalid', 'Please select a location from the auto complete list', 'Information shown when there isn\'t enough information to find members.' );

        // signup
        $this->RemoveWordCode( 'signup.error.mothertongue', 'Please select a mother tongue.', 'Error message if no mother tongue was selected.');
        $this->RemoveWordCode('signup.error.username.taken', 'Please choose a different username that is at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is already in use.');
        $this->RemoveWordCode('signup.error.username', 'Username must be at least 4 and maximum 20 characters long. They have to start with a letter, they have to end with either a letter or a number. In between the following characters may be used: . _ -', 'Error message if username is malformed.');
        $this->RemoveWordCode('signup.popover.password', 'Please choose a strong password that is at least 6 characters long.', 'Popover text on signup regarding the password.');
        $this->RemoveWordCode('signup.confirm.email', 'Please confirm your email address', 'Subject line for the signup confirmation email');
        $this->RemoveWordCode( 'signup.names.hidden', 'Your first, second and last name are hidden to other users by default. If you\'d like to share these with other members you can change this later.');
        $this->RemoveWordCode('signup.error.name.empty');

        // profile
        $this->RemoveWordCode('profile.request.hosting', 'Request Stay', 'Menu entry on the members pages to sent a request for staying/be hosted by another member');
        $this->RemoveWordCode('profile.delete.cleanup', 'Please remove my data with the next cleanup (in the next 24 hours).', 'Label for the data cleanup checkbox on the retire profile page');
        $this->RemoveWordCode('profile.accommodation.hes.label', 'Hosting Eagerness', 'Label for the Hosting Eagerness Slider');
        $this->RemoveWordCode('profile.accommodation.hes.dont-boost', 'Move to end of search results instead of to the top', 'Label for the Hosting Eagerness Slider do not boost checkbox');
        $this->RemoveWordCode('profile.accommodation.hes.helptext', 'You can influence your position in search results by selecting a date in the future. The further away the date is the less high your boost becomes. You can also decide to be shown at the end of the list instead by ticking the checkbox. ', 'Help text for the hosting eagerness slider.');

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
        $this->RemoveWordCode('landing.tab.activities', 'Activities', 'Text for tab on landing page');
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

        // groups
        $this->RemoveWordCode('group.create.successful');
        $this->RemoveWordCode('group.create');
        $this->RemoveWordCode('admin.groups.awaiting.approval');

        // admin
        $this->RemoveWordCode('admin.comments.reported');
        $this->RemoveWordCode('admin.spam.reported');
        $this->RemoveWordCode('admin.comment.reported');
        $this->RemoveWordCode('home.headline.find.hosts');
        $this->RemoveWordCode('signup.confirm.email');
        $this->RemoveWordCode('profile.delete.cleanup');

        // translations
        $this->RemoveWordCode('translation.missing', 'Translation missing', 'Hint that a translation is missing in the list of translations.');
        $this->RemoveWordCode('translation.edit', 'Translation edited', 'Flashbag that a translation was updated');

        // credits
        $this->RemoveWordCode('credits.title', 'Credits', 'Title for the credits page');
        $this->RemoveWordCode('credits.headline', 'Credits', 'Headline on the credits page');
        $this->RemoveWordCode('credits.abstract', 'This website wouldn\'t be possible without the help of other.', 'Title for the credits page');

        // requests
        $this->RemoveWordCode('request.guest.open', 'No reply yet by your host', 'Request status for guest (open)');
        $this->RemoveWordCode('request.guest.cancelled', 'Request cancelled on your behalf.', 'Request status for guest (cancelled)');
        $this->RemoveWordCode('request.guest.declined', 'Request has been declined.', 'Request status for guest (declined by host)');
        $this->RemoveWordCode('request.guest.accepted', 'Request has been accepted.', 'Request status for guest (accepted by host)');
        $this->RemoveWordCode('request.guest.tentatively', 'Your host has replied "Maybe" to this request', 'Request status for guest (host hasn\'t decided yet)');
        $this->RemoveWordCode('request.host.open', 'Please reply to this request.', 'Request status for host (just received)');
        $this->RemoveWordCode('request.host.declined', 'You declined this request.', 'Request status for host (declined)');
        $this->RemoveWordCode('request.host.accepted', 'You accepted this request.', 'Request status for host (hosting)');
        $this->RemoveWordCode('request.host.tentatively', 'You have replied "Maybe" to this request', 'Request status for host (undecided)');
        $this->RemoveWordCode('request.not.hosting', 'This person says they are not willing to host.<br>You may send a message instead.', 'Flash message shown if accommodation is set to No.');

        // admin volunteer tools
        $this->RemoveWordCode('admin.tools.title', 'Volunteer Tools', 'Title of the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.headline', 'Volunteer Tools', 'Headline on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.find_user', 'Find member', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.change_username', 'Change username', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.check_feedback', 'Check Feedback', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.check_spam_messages', 'Top spammer', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.damage_done', 'Damage done', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.age_by_country', 'Country by age', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.messages_last_week', 'Messages last week', 'Submenu item on the volunteer tools pages');
        $this->RemoveWordCode('admin.tools.nothing', 'Nothing to show. Odd isn\'t it?', 'Text shown when nothing was found to display.');

        // requests
        $this->RemoveWordCode('request.arrival', 'Arrival', 'Label used on request form and in email notification');
        $this->RemoveWordCode('request.departure', 'Departure', 'Label used on request form and in email notification');
        $this->RemoveWordCode('request.flexible', 'Flexible', 'Label used on request form');
        $this->RemoveWordCode('request.number_of_travellers', 'Number of travellers', 'Label user on request form and in email notification');

        // emails
        $this->RemoveWordCode('email.greeting', 'Dear %username%,', 'Greeting in emails. Make sure to include the %username% when translating.');
        $this->RemoveWordCode('email.message', '%username% sent you a message.', 'First paragraph in a message. Make sure to include the %username% when translating.');
        $this->RemoveWordCode('email.request.stay', '%username% would like to stay with you.', 'first paragraph in a hosting request');
        $this->RemoveWordCode('email.request.reply.host', '%username% replied to your request to stay with them.', 'first paragraph in a reply from the host');
        $this->RemoveWordCode('email.request.reply.host.open', 'Please read your messages below.', 'State of the request in reply from host (accepted)');
        $this->RemoveWordCode('email.request.reply.host.accepted', 'You can stay with %username%.', 'State of the request in reply from host (accepted)');
        $this->RemoveWordCode('email.request.reply.host.declined', '%username% declined to host you.', 'State of the request in reply from host (declined)');
        $this->RemoveWordCode('email.request.reply.host.tentatively', '%username% has replied "Maybe" to this request', 'State of the request in reply from host (tentatively)');
        $this->RemoveWordCode('email.request.reply.guest', '%username% replied to your request to stay with them.', 'first paragraph in a reply from the host');
        $this->RemoveWordCode('email.request.reply.guest.open', 'This request is still open.', 'State of the request in reply from guest (accepted)');
        $this->RemoveWordCode('email.request.reply.guest.accepted', 'You accepted this request.', 'State of the request in reply from guest (accepted)');
        $this->RemoveWordCode('email.request.reply.guest.declined', 'You declined this request.', 'State of the request in reply from guest (declined)');
        $this->RemoveWordCode('email.request.reply.guest.tentatively', 'You have replied "Maybe" to this request', 'State of the request in reply from guest (tentatively)');
    }
}
