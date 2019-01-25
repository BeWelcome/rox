<?php


use Rox\Tools\RoxMigration;

class MissingWordcodesNewWebsite extends RoxMigration
{
    public function up()
    {
        // navigation
        $this->AddWordCode('MyRequests', 'My requests', 'Link to all the requests the member has received and sent');
        $this->AddWordCode('menu.faq', 'FAQ', 'Frequently Asked Question as a link in the navigational menu - keep it short');
        $this->AddWordCode('menu.about', 'About', 'Link to the About section in the navigational menu');
        $this->AddWordCode('login.password', 'Password', 'The label and placeholder for the password when logging in');
        $this->AddWordCode('navbar.button.signup', 'Signup', 'Either visitors can login or are shown a button to Sign up.');


        // home page and footer
        $this->AddWordCode('tour_link_travel', 'Travel', 'Header on the opening page to provide info on BeWelcome - Traveling');
        $this->AddWordCode('tour_link_host', 'Host', 'Header on the opening page to provide info on BeWelcome - Hosting');
        $this->AddWordCode('home.header.statistics', 'Some statistics', 'Header on the opening page which shows some statistics');
        $this->AddWordCode('home.stats.members', 'members', 'Statistics on the opening page: 123456 members');
        $this->AddWordCode('home.stats.countries', 'countries', 'Statistics on the opening page: 123456 countries');
        $this->AddWordCode('home.stats.languages', 'languages', 'Statistics on the opening page: 123456 languages');
        $this->AddWordCode('home.stats.positive_comments', 'positive comments', 'Statistics on the opening page: 123456 positive comments');
        $this->AddWordCode('home.stats.activities', 'activities', 'Statistics on the opening page: 123456 activities');
        $this->AddWordCode('home.title.onestep', 'The world is just one step from here', 'Header on the opening page just before entering a username or location to invite to signup');
        $this->AddWordCode('home.title.onestep.sub', 'We believe that sharing creates a better world. You can let people stay over or show them around. It\'s easy and it\'s called hospitality.', 'Sub-header on the opening page before chosing a username to signup');
        $this->AddWordCode('home.username', 'Choose your username!', 'Invitation to chose a username and start signing up');
        $this->AddWordCode('home.username.subheader', 'and start meeting people from everywhere.', 'Text after the invitation \'Choose your username!\'');
        $this->AddWordCode('home.username.signup', 'Submit', 'Button on the opening page after inviting visitors to chose a username to signup');
        $this->AddWordCode('home.searchplace', 'Find a place to stay', 'Header somewhere in the opening page');
        $this->AddWordCode('home.searchplace.subheader', 'and meet locals when traveling worldwide.', 'Subheader somewhere in the opening page');
        $this->AddWordCode('home.searchplace.question', 'Where do you want to go?', 'Header somewhere in the opening page');
        $this->AddWordCode('footer.imprint', 'Imprint', 'Link in the footer to the Imprint page');
        $this->AddWordCode('footer.privacy', 'Privacy', 'Link in the footer to the Privacy Policy');
        $this->AddWordCode('footer.contactus', 'Contact us', 'Link in the footer to contact BeWelcome');
        $this->AddWordCode('footer.reportbug', 'Report a bug', 'Link to a page where people can report bugs (errors in the website) so they can be fixed');


        // dashboard page
        $this->AddWordCode('dashboard.notifications.none', 'No notifications', 'This text is shown on the dashboard page in the notification widget when there are no notifications');
        $this->AddWordCode('dashboard.hosting.yes', 'hosting', 'Choice in the dropdown on the dashboard page for the hosting status - hosting');
        $this->AddWordCode('dashboard.hosting.maybe', 'maybe hosting', 'Choice in the dropdown on the dashboard page for the hosting status - maybe hosting');
        $this->AddWordCode('dashboard.hosting.no', 'not hosting', 'Choice in the dropdown on the dashboard page for the hosting status - not hosting');
        $this->AddWordCode('dashboard.messages.none', 'No messages', 'This text is shown on the dashboard page in the messages widget when there are no messages');
        $this->AddWordCode('dashboard.activities.none', 'No activities near you', 'This text is shown on the dashboard page in the activities widget when there are no activities');
        $this->AddWordCode('dashboard.load.messages', 'Please wait while we load the messages', 'Text shown while retrieving messages from the database.');
        $this->AddWordCode('dashboard.load.notifications', 'Please wait while we load the notifications', 'Text shown while retrieving notifications from the database.');
        $this->AddWordCode('dashboard.load.threads', 'Please wait while we load the forum threads', 'Text shown while retrieving forum threads from the database.');
        $this->AddWordCode('dashboard.load.activities', 'Please wait while we load the activities near you', 'Text shown while retrieving activities from the database.');
        $this->AddWordCode('dashboard.donation.received', 'received', 'Amount in donations RECEIVED');
        $this->AddWordCode('thread.lastpostby', 'last post by', 'shown on the dashboard page, under forum and group posts. Wordcode is followed by a username');

        // community pages
        $this->AddWordCode('community.newsletters', 'Newsletters', 'Link on the community page to the newsletter archive');
        $this->AddWordCode('group.create.check.double', 'We found a few existing groups that match (part) of your group\'s name. You might want to check if you really need a new group:', 'Text is shown when someone wants to create a group that might already exist.');
        $this->AddWordCode('group.create.warning', 'We\'re happy that you want to open a new group, but unfortunately spammers like them as well, so please note that the group will need to be activated by the BW Forum Moderators. Please also read the %link_start%Rules for Groups%link_end% when thinking about the new group\'s name and description.', 'A warning text that members should check if the group already exists and that the new groups will be checked and possibly deleted by the forum moderators');
        $this->AddWordCode('group.create.name.hint', 'For local groups, include the country (e.g. Place, Country) as this will help with searches.', 'Tips for naming the group logically');
        $this->AddWordCode('group.create.description.hint', 'Include the purpose of the group or the main features of the place covered by the group.', 'Tips for using a logic description of the group');
        $this->AddWordCode('activities.allactivities', 'All activities', 'Header for the page where all activities are shown');
        $this->AddWordCode('activities.nonefound', 'No activities found', 'Text shown when no activity was found that matches the criteria');
        $this->AddWordCode('dashboard.bwforum', 'BeWelcome Forum', 'Threads can be posted in groups or in the main BeWelcome forum.');


        // ###### BeWelcome news #########
        $this->AddWordCode('bewelcome_news.header', 'BeWelcome News', 'Header of the admin pages of the BeWelcome News');
        $this->AddWordCode('bewelcome_news.writtenby', 'Written by', 'Informs the reader who wrote the news item');
        $this->AddWordCode('bewelcome_news.lastupdater', 'last updated by', 'In case someone updated the news item, this text is shown followed by a username');
        $this->AddWordCode('bewelcome_news.nrcomments', '%commentsCount% comments', 'Number of reactions to the news item. Please leave the \'%commentsCount%\' code');
        $this->AddWordCode('bewelcome_news.addcomment', 'Add Comment', 'Text on the button to add a reaction to the news');
        $this->AddWordCode('bewelcome_news.header.all', 'All BeWelcome News', 'Header shown on the page with all news');
        $this->AddWordCode('bewelcome_news.readmore', 'Read more', 'Text on a button to read more of the news item');

        // translation section
        $this->AddWordCode('translation.mode', 'Translation mode is', 'Text that shows if the translation modus is toggled ON or OFF');
        $this->AddWordCode('translation.mode.on', 'On', 'Translation mode is ON, volunteer can translate the pages');
        $this->AddWordCode('translation.mode.off', 'Off', 'Translation mode is OFF, volunteer just browses the pages normally');
        $this->AddWordCode('translation.help', 'Help us Translate', 'Button shown when someone has no translation rights, leads to information on how to join the team');
        $this->AddWordCode('translation.information', 'Translation information', 'Header shown in translation modus to give more info');
        $this->AddWordCode('translation.existing', 'Existing translations', 'Header shown in translation mode to show existing translations');
        $this->AddWordCode('translation.translated', 'The list shows all translatable items that have an existing translation in the current locale.', 'The list shows all items already translated');
        $this->AddWordCode('translation.fallback', 'Fallback Translations', 'If the translation is not available in the language, this text is used instead');
        $this->AddWordCode('translation.match', 'The list shows all translations that match the English original text.', 'The list shows all translations that match the English original text.');
        $this->AddWordCode('translation.keywordnotexisting', 'Translations keyword doesn\'t exist', 'Translations keyword doesn\'t exist');
        $this->AddWordCode('translations.missing', 'There\'s no translation for this wordcode', 'Text to show that there\'s no translation for the wordcode');


        // messages, requests and notifications
        $this->AddWordCode('navbar.popup.newmessage', 'You have received new messages.', 'Message shown to users when they received new messages after last login');
        $this->AddWordCode('navbar.popup.newrequest', 'You have received new requests.', 'Message shown to users when they received new hospitality requests after last login');
        $this->AddWordCode('message.button.reply', 'Reply', 'Text shown on button to reply a message or request');
        $this->AddWordCode('messages.none', 'No messages in this folder.', 'Text shown if there are no messages in a specific folder (inbox / sent / spam)');
        $this->AddWordCode('message.sentdate', 'Sent on %sentDate%', 'Adds the data the message/request has been sent. Please include the \'%sentDate%\' code');
        $this->AddWordCode('message.conversation_with', 'Conversation with %s', 'Shows with whom the conversation has been. Please include the \'%s\', it will be replaced with the username');
        $this->AddWordCode('message.you', 'You', 'Conversation between username and YOU - 2nd person singular, informal if possible');
        $this->AddWordCode('message.write_to', 'Write a message to %s', 'Header on the page where you can write a message to another member. Please include the \'%s\' code, it will be replaced with the username');
        $this->AddWordCode('message.send', 'Send', 'Text shown on a button to send a message');
        $this->AddWordCode('request.sent_to', 'Sent to %receiver%', 'Text is shown after replying a hospitality request. Please include the code \'%receiver%\', it will be replaced with the username');
        $this->AddWordCode('request.sent_by', 'Sent by %sender%', 'Text is shown after receiving (an answer to) a hospitality request. Please include the code \'%sender%\', it will be replaced with the username');
        $this->AddWordCode('request.host.suggest.dates', 'These are the dates that are suggested for the hospitality request', 'Text next to the dates of a received hospitality request');

        // profile and comments
        $this->AddWordCode('member.comment.from', 'From:', 'Indicates who wrote the comment');
        $this->AddWordCode('member.comment.on', 'on', 'Indicates on which user the comment was written, followed by the username');
        $this->AddWordCode('comment.report.header', 'Report a comment', 'The header of the page where someone can report a (false) comment');
        $this->AddWordCode('comment.report.help', 'Please enter your feedback below. It will be forwarded to the Safety Team which will review it and contact you in case further information are needed.', 'Explanation of how to report properly, so that the safety team can pick it up. Maybe include a short note that if possible, English would be the preferred language for the report');

        // wiki
        $this->AddWordCode('wiki.page.edit', 'Edit page', 'Text on the button to edit the wiki page');
        $this->AddWordCode('Wiki', 'Wiki', 'Menu item and header of the wiki pages. Probably just call this \'Wiki\' in all languages, unless there\'s a common other word for it');

        // member search
        $this->AddWordCode('search.filter.host.yes', 'I like to host', 'The alternative text for an image that is shown on the search page. Filter all results where hosting status is \'YES\'');
        $this->AddWordCode('search.filter.host.maybe', 'I might host you', 'The alternative text for an image that is shown on the search page. Filter all results where hosting status is \'MAYBE\'');
        $this->AddWordCode('search.filter.host.no', 'I can\'t host', 'The alternative text for an image that is shown on the search page. Filter all results where hosting status is \'NO\'');
        $this->AddWordCode('search.filter.hosts_at_least', 'Hosts at least this many guests', 'Title of the filter on the member search page that filters out the minimum amount of guests');
        $this->AddWordCode('search.filter.radius', 'in a radius of', 'Title of the icon of the member search, where one can search in a radius of a certain place');

        // FAQ
        $this->AddWordCode('faqs.none', 'No faqs found in this category.', 'When a category of FAQs is made but no FAQs available for that category');
        $this->AddWordCode('faqs.none.create', 'Please create one.', 'If no FAQs are available, this text motivates to make a FAQ in the selected category');
        $this->AddWordCode('faqs.button.create', 'Create a FAQ', 'Admin button to create a FAQ');
        $this->AddWordCode('faqs.button.edit', 'Edit a FAQ', 'Admin button to edit a FAQ');

        // emails
        $this->AddWordCode('email.greeting', 'Dear %username%,', 'Greeting in emails. Make sure to include the %username% when translating.');
        $this->AddWordCode('email.password.request', 'Someone requested to reset you password. If that someone was you please click on the below to start the process to reset you password.', 'Text of an e-mail to tell the member someone requested a new password');
        $this->AddWordCode('email.password.request.ignore', 'Otherwise either just ignore this email or contact support at ', 'Text of an e-mail to say they may ignore this message if they were not the one requesting a new password, or contacting the support team with a link that\'s coming after this text');
        $this->AddWordCode('email.password.reset', 'Reset Password', 'Text for the link to reset the password');


        // admin
        $this->AddWordCode('admin.groups.create.info', 'Group Info', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.groups.create.description', 'Group Description', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.groups.create.creator', 'Creator', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.groups.create.approve', 'Approve group', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.groups.create.dismiss', 'Dismiss group', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.groups.create.discuss', 'Move group to discussion queue', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.groups.create.none', 'No groups need to be approved', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.all', 'All Comments', 'List of all comments for admins');
        $this->AddWordCode('admin.comments.none', 'No comments found for these parameters.', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.written', 'written', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.edit', 'Edit comment', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.hide', 'Hide', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.show', 'Show', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.to_safety', 'Assign to safety team', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.mark', 'Mark as checked', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.to_username', 'All comments to %username%', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.comments.from_username', 'All comments from %username%', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.logs.none', 'No logs found for these parameters.', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.messages.nospam', 'No messages have been reported as spam.', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.tools.country', 'Country', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.tools.membercount', '# members', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.tools.birthyear', 'Birth Year', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.tools.age_by_country.nothing', '', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.moderation.none', 'No feedback found for these parameters.', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.faqs.sort_categories', 'Sort FAQ Categories', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.faqs.create.category', 'Create a FAQ category', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.faqs.edit.faq', 'Edit a FAQ', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.faqs.create.faq', 'Create a FAQ', 'You may translate these, but admin wordcodes can remain in English');
        $this->AddWordCode('admin.faqs.edit.category', 'Edit a FAQ category', 'You may translate these, but admin wordcodes can remain in English');


        // misc
        $this->AddWordCode('login.password.getnew', 'Get a new one!', 'Text to link to the password recovery page, answering the question: forgot your password?');
        $this->AddWordCode('button.edit', 'Edit', 'Text for a general Edit-button');
        $this->AddWordCode('button.update', 'Update', 'Text for a general Update-button');

 
    }

    public function down()
    {

        // navigation
        $this->RemoveWordCode('MyRequests', 'My requests', 'Link to all the requests the member has received and sent');
        $this->RemoveWordCode('menu.faq', 'FAQ', 'Frequently Asked Question as a link in the navigational menu - keep it short');
        $this->RemoveWordCode('menu.about', 'About', 'Link to the About section in the navigational menu');
        $this->RemoveWordCode('login.password', 'Password', 'The label and placeholder for the password when logging in');
        $this->RemoveWordCode('navbar.button.signup', 'Signup', 'Either visitors can login or are shown a button to Sign up.');


        // home page and footer
        $this->RemoveWordCode('tour_link_travel', 'Travel', 'Header on the opening page to provide info on BeWelcome - Traveling');
        $this->RemoveWordCode('tour_link_host', 'Host', 'Header on the opening page to provide info on BeWelcome - Hosting');
        $this->RemoveWordCode('home.header.statistics', 'Some statistics', 'Header on the opening page which shows some statistics');
        $this->RemoveWordCode('home.stats.members', 'members', 'Statistics on the opening page: 123456 members');
        $this->RemoveWordCode('home.stats.countries', 'countries', 'Statistics on the opening page: 123456 countries');
        $this->RemoveWordCode('home.stats.languages', 'languages', 'Statistics on the opening page: 123456 languages');
        $this->RemoveWordCode('home.stats.positive_comments', 'positive comments', 'Statistics on the opening page: 123456 positive comments');
        $this->RemoveWordCode('home.stats.activities', 'activities', 'Statistics on the opening page: 123456 activities');
        $this->RemoveWordCode('home.title.onestep', 'The world is just one step from here', 'Header on the opening page just before entering a username or location to invite to signup');
        $this->RemoveWordCode('home.title.onestep.sub', 'We believe that sharing creates a better world. You can let people stay over or show them around. It\'s easy and it\'s called hospitality.', 'Sub-header on the opening page before chosing a username to signup');
        $this->RemoveWordCode('home.username', 'Choose your username!', 'Invitation to chose a username and start signing up');
        $this->RemoveWordCode('home.username.subheader', 'and start meeting people from everywhere.', 'Text after the invitation \'Choose your username!\'');
        $this->RemoveWordCode('home.username.signup', 'Submit', 'Button on the opening page after inviting visitors to chose a username to signup');
        $this->RemoveWordCode('home.searchplace', 'Find a place to stay', 'Header somewhere in the opening page');
        $this->RemoveWordCode('home.searchplace.subheader', 'and meet locals when traveling worldwide.', 'Subheader somewhere in the opening page');
        $this->RemoveWordCode('home.searchplace.question', 'Where do you want to go?', 'Header somewhere in the opening page');
        $this->RemoveWordCode('footer.imprint', 'Imprint', 'Link in the footer to the Imprint page');
        $this->RemoveWordCode('footer.privacy', 'Privacy', 'Link in the footer to the Privacy Policy');
        $this->RemoveWordCode('footer.contactus', 'Contact us', 'Link in the footer to contact BeWelcome');
        $this->RemoveWordCode('footer.reportbug', 'Report a bug', 'Link to a page where people can report bugs (errors in the website) so they can be fixed');


        // dashboard page
        $this->RemoveWordCode('dashboard.notifications.none', 'No notifications', 'This text is shown on the dashboard page in the notification widget when there are no notifications');
        $this->RemoveWordCode('dashboard.hosting.yes', 'hosting', 'Choice in the dropdown on the dashboard page for the hosting status - hosting');
        $this->RemoveWordCode('dashboard.hosting.maybe', 'maybe hosting', 'Choice in the dropdown on the dashboard page for the hosting status - maybe hosting');
        $this->RemoveWordCode('dashboard.hosting.no', 'not hosting', 'Choice in the dropdown on the dashboard page for the hosting status - not hosting');
        $this->RemoveWordCode('dashboard.messages.none', 'No messages', 'This text is shown on the dashboard page in the messages widget when there are no messages');
        $this->RemoveWordCode('dashboard.activities.none', 'No activities near you', 'This text is shown on the dashboard page in the activities widget when there are no activities');
        $this->RemoveWordCode('dashboard.load.messages', 'Please wait while we load the messages', 'Text shown while retrieving messages from the database.');
        $this->RemoveWordCode('dashboard.load.notifications', 'Please wait while we load the notifications', 'Text shown while retrieving notifications from the database.');
        $this->RemoveWordCode('dashboard.load.threads', 'Please wait while we load the forum threads', 'Text shown while retrieving forum threads from the database.');
        $this->RemoveWordCode('dashboard.load.activities', 'Please wait while we load the activities near you', 'Text shown while retrieving activities from the database.');
        $this->RemoveWordCode('dashboard.donation.received', 'received', 'Amount in donations RECEIVED');
        $this->RemoveWordCode('thread.lastpostby', 'last post by', 'shown on the dashboard page, under forum and group posts. Wordcode is followed by a username');

        // community pages
        $this->RemoveWordCode('community.newsletters', 'Newsletters', 'Link on the community page to the newsletter archive');
        $this->RemoveWordCode('group.create.check.double', 'We found a few existing groups that match (part) of your group\'s name. You might want to check if you really need a new group:', 'Text is shown when someone wants to create a group that might already exist.');
        $this->RemoveWordCode('group.create.warning', 'We\'re happy that you want to open a new group, but unfortunately spammers like them as well, so please note that the group will need to be activated by the BW Forum Moderators. Please also read the %link_start%Rules for Groups%link_end% when thinking about the new group\'s name and description.', 'A warning text that members should check if the group already exists and that the new groups will be checked and possibly deleted by the forum moderators');
        $this->RemoveWordCode('group.create.name.hint', 'For local groups, include the country (e.g. Place, Country) as this will help with searches.', 'Tips for naming the group logically');
        $this->RemoveWordCode('group.create.description.hint', 'Include the purpose of the group or the main features of the place covered by the group.', 'Tips for using a logic description of the group');
        $this->RemoveWordCode('activities.allactivities', 'All activities', 'Header for the page where all activities are shown');
        $this->RemoveWordCode('activities.nonefound', 'No activities found', 'Text shown when no activity was found that matches the criteria');
        $this->RemoveWordCode('dashboard.bwforum', 'BeWelcome Forum', 'Threads can be posted in groups or in the main BeWelcome forum.');


        // ###### BeWelcome news #########
        $this->RemoveWordCode('bewelcome_news.header', 'BeWelcome News', 'Header of the admin pages of the BeWelcome News');
        $this->RemoveWordCode('bewelcome_news.writtenby', 'Written by', 'Informs the reader who wrote the news item');
        $this->RemoveWordCode('bewelcome_news.lastupdater', 'last updated by', 'In case someone updated the news item, this text is shown followed by a username');
        $this->RemoveWordCode('bewelcome_news.nrcomments', '%commentsCount% comments', 'Number of reactions to the news item. Please leave the \'%commentsCount%\' code');
        $this->RemoveWordCode('bewelcome_news.addcomment', 'Add Comment', 'Text on the button to add a reaction to the news');
        $this->RemoveWordCode('bewelcome_news.header.all', 'All BeWelcome News', 'Header shown on the page with all news');
        $this->RemoveWordCode('bewelcome_news.readmore', 'Read more', 'Text on a button to read more of the news item');

        // translation section
        $this->RemoveWordCode('translation.mode', 'Translation mode is', 'Text that shows if the translation modus is toggled ON or OFF');
        $this->RemoveWordCode('translation.mode.on', 'On', 'Translation mode is ON, volunteer can translate the pages');
        $this->RemoveWordCode('translation.mode.off', 'Off', 'Translation mode is OFF, volunteer just browses the pages normally');
        $this->RemoveWordCode('translation.help', 'Help us Translate', 'Button shown when someone has no translation rights, leads to information on how to join the team');
        $this->RemoveWordCode('translation.information', 'Translation information', 'Header shown in translation modus to give more info');
        $this->RemoveWordCode('translation.existing', 'Existing translations', 'Header shown in translation mode to show existing translations');
        $this->RemoveWordCode('translation.translated', 'The list shows all translatable items that have an existing translation in the current locale.', 'The list shows all items already translated');
        $this->RemoveWordCode('translation.fallback', 'Fallback Translations', 'If the translation is not available in the language, this text is used instead');
        $this->RemoveWordCode('translation.match', 'The list shows all translations that match the English original text.', 'The list shows all translations that match the English original text.');
        $this->RemoveWordCode('translation.keywordnotexisting', 'Translations keyword doesn\'t exist', 'Translations keyword doesn\'t exist');
        $this->RemoveWordCode('translations.missing', 'There\'s no translation for this wordcode', 'Text to show that there\'s no translation for the wordcode');


        // messages, requests and notifications
        $this->RemoveWordCode('navbar.popup.newmessage', 'You have received new messages.', 'Message shown to users when they received new messages after last login');
        $this->RemoveWordCode('navbar.popup.newrequest', 'You have received new requests.', 'Message shown to users when they received new hospitality requests after last login');
        $this->RemoveWordCode('message.button.reply', 'Reply', 'Text shown on button to reply a message or request');
        $this->RemoveWordCode('messages.none', 'No messages in this folder.', 'Text shown if there are no messages in a specific folder (inbox / sent / spam)');
        $this->RemoveWordCode('message.sentdate', 'Sent on %sentDate%', 'Adds the data the message/request has been sent. Please include the \'%sentDate%\' code');
        $this->RemoveWordCode('message.conversation_with', 'Conversation with %s', 'Shows with whom the conversation has been. Please include the \'%s\', it will be replaced with the username');
        $this->RemoveWordCode('message.you', 'You', 'Conversation between username and YOU - 2nd person singular, informal if possible');
        $this->RemoveWordCode('message.write_to', 'Write a message to %s', 'Header on the page where you can write a message to another member. Please include the \'%s\' code, it will be replaced with the username');
        $this->RemoveWordCode('message.send', 'Send', 'Text shown on a button to send a message');
        $this->RemoveWordCode('request.sent_to', 'Sent to %receiver%', 'Text is shown after replying a hospitality request. Please include the code \'%receiver%\', it will be replaced with the username');
        $this->RemoveWordCode('request.sent_by', 'Sent by %sender%', 'Text is shown after receiving (an answer to) a hospitality request. Please include the code \'%sender%\', it will be replaced with the username');
        $this->RemoveWordCode('request.host.suggest.dates', 'These are the dates that are suggested for the hospitality request', 'Text next to the dates of a received hospitality request');

        // profile and comments
        $this->RemoveWordCode('member.comment.from', 'From:', 'Indicates who wrote the comment');
        $this->RemoveWordCode('member.comment.on', 'on', 'Indicates on which user the comment was written, followed by the username');
        $this->RemoveWordCode('comment.report.header', 'Report a comment', 'The header of the page where someone can report a (false) comment');
        $this->RemoveWordCode('comment.report.help', 'Please enter your feedback below. It will be forwarded to the Safety Team which will review it and contact you in case further information are needed.', 'Explanation of how to report properly, so that the safety team can pick it up. Maybe include a short note that if possible, English would be the preferred language for the report');

        // wiki
        $this->RemoveWordCode('wiki.page.edit', 'Edit page', 'Text on the button to edit the wiki page');
        $this->RemoveWordCode('Wiki', 'Wiki', 'Menu item and header of the wiki pages. Probably just call this \'Wiki\' in all languages, unless there\'s a common other word for it');

        // member search
        $this->RemoveWordCode('search.filter.host.yes', 'I like to host', 'The alternative text for an image that is shown on the search page. Filter all results where hosting status is \'YES\'');
        $this->RemoveWordCode('search.filter.host.maybe', 'I might host you', 'The alternative text for an image that is shown on the search page. Filter all results where hosting status is \'MAYBE\'');
        $this->RemoveWordCode('search.filter.host.no', 'I can\'t host', 'The alternative text for an image that is shown on the search page. Filter all results where hosting status is \'NO\'');
        $this->RemoveWordCode('search.filter.hosts_at_least', 'Hosts at least this many guests', 'Title of the filter on the member search page that filters out the minimum amount of guests');
        $this->RemoveWordCode('search.filter.radius', 'in a radius of', 'Title of the icon of the member search, where one can search in a radius of a certain place');

        // FAQ
        $this->RemoveWordCode('faqs.none', 'No faqs found in this category.', 'When a category of FAQs is made but no FAQs available for that category');
        $this->RemoveWordCode('faqs.none.create', 'Please create one.', 'If no FAQs are available, this text motivates to make a FAQ in the selected category');
        $this->RemoveWordCode('faqs.button.create', 'Create a FAQ', 'Admin button to create a FAQ');
        $this->RemoveWordCode('faqs.button.edit', 'Edit a FAQ', 'Admin button to edit a FAQ');

        // emails
        $this->RemoveWordCode('email.greeting', 'Dear %username%,', 'Greeting in emails. Make sure to include the %username% when translating.');
        $this->RemoveWordCode('email.password.request', 'Someone requested to reset you password. If that someone was you please click on the below to start the process to reset you password.', 'Text of an e-mail to tell the member someone requested a new password');
        $this->RemoveWordCode('email.password.request.ignore', 'Otherwise either just ignore this email or contact support at ', 'Text of an e-mail to say they may ignore this message if they were not the one requesting a new password, or contacting the support team with a link that\'s coming after this text');
        $this->RemoveWordCode('email.password.reset', 'Reset Password', 'Text for the link to reset the password');


        // admin
        $this->RemoveWordCode('admin.groups.create.info', 'Group Info', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.groups.create.description', 'Group Description', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.groups.create.creator', 'Creator', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.groups.create.approve', 'Approve group', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.groups.create.dismiss', 'Dismiss group', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.groups.create.discuss', 'Move group to discussion queue', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.groups.create.none', 'No groups need to be approved', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.all', 'All Comments', 'List of all comments for admins');
        $this->RemoveWordCode('admin.comments.none', 'No comments found for these parameters.', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.written', 'written', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.edit', 'Edit comment', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.hide', 'Hide', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.show', 'Show', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.to_safety', 'Assign to safety team', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.mark', 'Mark as checked', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.to_username', 'All comments to %username%', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.comments.from_username', 'All comments from %username%', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.logs.none', 'No logs found for these parameters.', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.messages.nospam', 'No messages have been reported as spam.', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.tools.country', 'Country', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.tools.membercount', '# members', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.tools.birthyear', 'Birth Year', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.tools.age_by_country.nothing', '', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.moderation.none', 'No feedback found for these parameters.', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.faqs.sort_categories', 'Sort FAQ Categories', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.faqs.create.category', 'Create a FAQ category', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.faqs.edit.faq', 'Edit a FAQ', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.faqs.create.faq', 'Create a FAQ', 'You may translate these, but admin wordcodes can remain in English');
        $this->RemoveWordCode('admin.faqs.edit.category', 'Edit a FAQ category', 'You may translate these, but admin wordcodes can remain in English');


        // misc
        $this->RemoveWordCode('login.password.getnew', 'Get a new one!', 'Text to link to the password recovery page, answering the question: forgot your password?');
        $this->RemoveWordCode('button.edit', 'Edit', 'Text for a general Edit-button');
        $this->RemoveWordCode('button.update', 'Update', 'Text for a general Update-button');


    }
}
