<?php
    /**
     * this file contains the routes used in RequestRouter
     * it is require_once()'d in that class
     *
     * to add a route, just add a line as follows:
     *     $this->addRoute(route_name, route_url, controller, method, callback);
     *         |               |            |         |           |
     * RequestRouter method    |            |         |           |
     *       unique name of your route       |         |           |
     *                      the url you want to map   |           |
     *                              the controller to map to      |
     *                                   the method in the controller to map to
     *                                              if the method to call is a post callback
     */

    // Start page only used after logout
    $this->addRoute('start', '', 'Rox\Main\StartController', 'showAction');
    $this->addRoute('main_page', 'home', 'Rox\Main\HomeController', 'showAction');

    // login routes
    $this->addRoute('login_helper','login/:url:#login-widget', 'LoginController', 'login_helper', true);
    $this->addRoute('logout', 'logout', 'LoginController', 'logOut');
    $this->addRoute('login', 'login*', 'LoginController', 'logIn');

    // group routes
    $this->addRoute('group_acceptinvitation','groups/:group_id:/acceptinvitation/:member_id:', 'GroupsController', 'acceptInvitation');
    $this->addRoute('group_acceptmember','groups/:group_id:/acceptmember/:member_id:', 'GroupsController', 'acceptMember');
    $this->addRoute('group_banmember','groups/:group_id:/banmember/:member_id:', 'GroupsController', 'banMember');
    $this->addRoute('group_addadmin','groups/:group_id:/addAdmin/:member_id:', 'GroupsController', 'addMemberAsAdmin');
    $this->addRoute('group_resignadmin','groups/:group_id:/resignAdmin', 'GroupsController', 'resignAsAdmin');
    $this->addRoute('group_declineinvitation','groups/:group_id:/declineinvitation/:member_id:', 'GroupsController', 'declineInvitation');
    $this->addRoute('group_declinemember','groups/:group_id:/declinemember/:member_id:', 'GroupsController', 'declineMember');
    $this->addRoute('group_delete','groups/:group_id:/delete', 'GroupsController', 'delete');
    $this->addRoute('group_deleted','groups/:group_id:/delete/true', 'GroupsController', 'delete');
    $this->addRoute('group_forum','groups/:group_id:/forum', 'GroupsController', 'forum');
    $this->addRoute('group_forum_thread','groups/:group_id:/forum/:thread:', 'GroupsController', 'forum');
    $this->addRoute('group_forum_action','groups/:group_id:/forum/:thread:/:action:', 'GroupsController', 'forum');
    $this->addRoute('group_groupsettings','groups/:group_id:/groupsettings', 'GroupsController', 'groupSettings');
    $this->addRoute('group_invitemember','groups/:group_id:/invitemember/:member_id:', 'GroupsController', 'inviteMember');
    $this->addRoute('group_invitemember_ajax','groups/:group_id:/invitememberajax/:member_id:', 'GroupsController', 'inviteMemberAjax');
    $this->addRoute('group_invitepage','groups/:group_id:/invitemember', 'GroupsController', 'inviteMembers');
    $this->addRoute('group_join','groups/:group_id:/join', 'GroupsController', 'join');
    $this->addRoute('group_joined','groups/:group_id:/join/true', 'GroupsController', 'joined', true);
    $this->addRoute('group_kickmember','groups/:group_id:/kickmember/:member_id:', 'GroupsController', 'kickMember');
    $this->addRoute('group_leave','groups/:group_id:/leave', 'GroupsController', 'leave');
    $this->addRoute('group_left','groups/:group_id:/leave/true', 'GroupsController', 'left');
    $this->addRoute('group_memberadministration','groups/:group_id:/memberadministration', 'GroupsController', 'memberAdministration');
    $this->addRoute('group_memberadministration_paged','groups/:group_id:/memberadministration/page/:page_number:', 'GroupsController', 'memberAdministration');
    $this->addRoute('group_members','groups/:group_id:/members', 'GroupsController', 'members');
    $this->addRoute('group_members_paged','groups/:group_id:/members/page/:page_number:', 'GroupsController', 'members');
    $this->addRoute('group_membersearch_ajax','groups/:group_id:/membersearchajax/:search_term:', 'GroupsController', 'memberSearchAjax');
    $this->addRoute('group_membersettings','groups/:group_id:/membersettings', 'GroupsController', 'memberSettings');
    $this->addRoute('group_start','groups/:group_id:', 'GroupsController', 'showGroup');
    $this->addRoute('group_wiki','groups/:group_id:/wiki', 'GroupsController', 'wiki');
    $this->addRoute('groups_featured','groups/featured', 'GroupsController', 'featured');
    $this->addRoute('groups_forums_overview','groups/forums', 'GroupsController', 'groupForumsOverview');
    $this->addRoute('groups_forums_overview_paged','groups/forums/:page_number:', 'GroupsController', 'groupForumsOverview');
    $this->addRoute('groups_mygroups','groups/mygroups', 'GroupsController', 'myGroups');
    $this->addRoute('groups_new','groups/new', 'GroupsController', 'create');
    $this->addRoute('groups_overview','groups/search', 'GroupsController', 'search');
    $this->addRoute('groups_realimg','groups/realimg/:group_id:', 'GroupsController', 'realImg');
    $this->addRoute('groups_search','groups/search', 'GroupsController', 'search');
    $this->addRoute('groups_thumbimg','groups/thumbimg/:group_id:', 'GroupsController', 'thumbImg');

    // related groups routes
    $this->addRoute('relatedgroup_select','groups/:group_id:/selectrelatedgroup', 'RelatedGroupsController', 'selectRelatedGroup');
    $this->addRoute('relatedgroup_add','groups/:group_id:/addrelatedgroup/:related_id:', 'RelatedGroupsController', 'addRelatedGroup');
    $this->addRoute('relatedgroup_selectdelete','groups/:group_id:/selectdeleterelatedgroup', 'RelatedGroupsController', 'selectdeleteRelatedGroup');
    $this->addRoute('relatedgroup_delete','groups/:group_id:/deleterelatedgroup/:related_id:', 'RelatedGroupsController', 'deleteRelatedGroup');
    $this->addRoute('relatedgroup_log','groups/:group_id:/relatedgroupsettings', 'RelatedGroupsController', 'showRelatedGroupLog');

    // member app routes
    $this->addRoute('members_profile', '/members/:username:', 'MembersController', 'index');

    $this->addRoute('members_profile_retired', 'retired', 'MembersController', 'retired');
    $this->addRoute('members_profile_set_active', 'setprofileactive', 'MembersController', 'setactive');
    $this->addRoute('members_profile_set_inactive', 'setprofileinactive', 'MembersController', 'setinactive');
    $this->addRoute('members_reset_password', 'resetpassword' , 'MembersController', 'resetPassword');
    $this->addRoute('members_reset_password_finish', 'login/mypreferences%23password#login-widget' , 'MembersController', 'resetPasswordFinish');

    $this->addRoute('members_show_all_notes', 'mynotes', 'MembersController', 'myNotes');
    $this->addRoute('members_add_note', 'members/:username:/note/add', 'MembersController', 'addNote');
    $this->addRoute('members_update_note', 'members/:username:/note/edit', 'MembersController', 'addNote');
    $this->addRoute('members_delete_note', 'members/:username:/note/delete', 'MembersController', 'deleteNote');

    $this->addRoute('members_edit_flags', 'members/:username:/flags', 'MembersController', 'editFlags');

    // admin temporary vol page route
    $this->addRoute('admin_tempvolstart', 'volunteer', 'AdminGeneralController', 'tempVolStart');

    // admin app routes
    $this->addRoute('admin_main', 'admin', 'AdminGeneralController', 'index');
    $this->addRoute('admin_norights', 'admin/norights', 'AdminGeneralController', 'noRights');
    $this->addRoute('admin_debug_logs', 'admin/debug/:log_type:', 'AdminGeneralController', 'debugLogs');
    $this->addRoute('admin_spam_overview', 'admin/spam', 'AdminGeneralController', 'spamOverview');
    $this->addRoute('admin_activity_overview', 'admin/activitylogs', 'AdminGeneralController', 'activityLogs');
    
    // admin comments
    $this->addRoute('admin_comments_list_from', 'admin/comments/list/from/:id:', 'AdminCommentsController', 'from');
    $this->addRoute('admin_comments_list_to', 'admin/comments/list/to/:id:', 'AdminCommentsController', 'to');
    $this->addRoute('admin_comments_list_single', 'admin/comments/list/single/:id:', 'AdminCommentsController', 'single');
    $this->addRoute('admin_comments_list_subset', 'admin/comments/list/:subset:', 'AdminCommentsController', 'subset');
    
    // admin rights
    $this->addRoute('admin_rights', 'admin/rights', 'AdminRightsController', 'assign');
    $this->addRoute('admin_rights_overview', 'admin/rights/overview', 'AdminRightsController', 'overview');
    $this->addRoute('admin_rights_members', 'admin/rights/list/members', 'AdminRightsController', 'listMembers');
    $this->addRoute('admin_rights_member', 'admin/rights/list/members/:username:', 'AdminRightsController', 'listMembers');
    $this->addRoute('admin_rights_rights', 'admin/rights/list/rights', 'AdminRightsController', 'listRights');
    $this->addRoute('admin_rights_right', 'admin/rights/list/rights/:id:', 'AdminRightsController', 'listRights');
    $this->addRoute('admin_rights_create', 'admin/rights/create', 'AdminRightsController', 'create');
    $this->addRoute('admin_rights_assign', 'admin/rights/assign/:username:', 'AdminRightsController', 'assign');
    $this->addRoute('admin_rights_edit', 'admin/rights/edit/:id:/:username:', 'AdminRightsController', 'edit');
    $this->addRoute('admin_rights_remove', 'admin/rights/remove/:id:/:username:', 'AdminRightsController', 'remove');
    $this->addRoute('admin_rights_tooltip', 'admin/rights/tooltip', 'AdminRightsController', 'tooltip');

    // admin flags
    $this->addRoute('admin_flags', 'admin/flags', 'AdminFlagsController', 'listMembers');
    $this->addRoute('admin_flags_overview', 'admin/flags/overview', 'AdminFlagsController', 'overview');
    $this->addRoute('admin_flags_members', 'admin/flags/list/members', 'AdminFlagsController', 'listMembers');
    $this->addRoute('admin_flags_member', 'admin/flags/list/members/:username:', 'AdminFlagsController', 'listMembers');
    $this->addRoute('admin_flags_flags', 'admin/flags/list/flags', 'AdminFlagsController', 'listFlags');
    $this->addRoute('admin_flags_flag', 'admin/flags/list/flags/:id:', 'AdminFlagsController', 'listFlags');
    $this->addRoute('admin_flags_create', 'admin/flags/create', 'AdminFlagsController', 'create');
    $this->addRoute('admin_flags_assign', 'admin/flags/assign', 'AdminFlagsController', 'assign');
    $this->addRoute('admin_flags_assign_user', 'admin/flags/assign/:username:', 'AdminFlagsController', 'assign');
    $this->addRoute('admin_flags_edit', 'admin/flags/edit/:id:/:username:', 'AdminFlagsController', 'edit');
    $this->addRoute('admin_flags_remove', 'admin/flags/remove/:id:/:username:', 'AdminFlagsController', 'remove');
    $this->addRoute('admin_flags_tooltip', 'admin/flags/tooltip', 'AdminFlagsController', 'tooltip');

    // admin words routes
    // the overview route redirects to an empty edit screen,
    // ideally this would become a real overview screen later on
    $this->addRoute('admin_word_overview', 'admin/word', 'AdminWordController', 'editTranslation');    
    $this->addRoute('admin_word_editempty', 'admin/word/edit', 'AdminWordController', 'editTranslation');
    $this->addRoute('admin_word_editone', 'admin/word/edit/:wordcode:', 'AdminWordController', 'editTranslation');
    $this->addRoute('admin_word_editlang', 'admin/word/edit/:wordcode:/:shortcode:', 'AdminWordController', 'editTranslation');
    $this->addRoute('admin_word_create', 'admin/word/createcode', 'AdminWordController', 'createCode');
    $this->addRoute('admin_word_createone', 'admin/word/createcode/:wordcode:', 'AdminWordController', 'createCode');
    $this->addRoute('admin_word_code', 'admin/word/editcode','AdminWordController','editCode');
    $this->addRoute('admin_word_listal', 'admin/word/list/all/long', 'AdminWordController', 'showList');
    $this->addRoute('admin_word_listas', 'admin/word/list/all/short', 'AdminWordController', 'showList');
    $this->addRoute('admin_word_listml', 'admin/word/list/missing/long', 'AdminWordController', 'showList');
    $this->addRoute('admin_word_listms', 'admin/word/list/missing/short', 'AdminWordController', 'showList');
    $this->addRoute('admin_word_listul', 'admin/word/list/update/long', 'AdminWordController', 'showList');
    $this->addRoute('admin_word_listus', 'admin/word/list/update/short', 'AdminWordController', 'showList');
    $this->addRoute('admin_word_stats', 'admin/word/stats', 'AdminWordController', 'showStatistics');
    $this->addRoute('admin_word_find', 'admin/word/find', 'AdminWordController','findTranslations');
    $this->addRoute('admin_word_noupdate', 'admin/word/noupdate/:id:', 'AdminWordController','noUpdateNeeded');

    // admin massmailing
    $this->addRoute('admin_massmail', 'admin/massmail', 'AdminMassmailController', 'massmail');
    $this->addRoute('admin_massmail_create', 'admin/massmail/create', 'AdminMassmailController', 'massmailCreate');
    $this->addRoute('admin_massmail_details', 'admin/massmail/details/:id:', 'AdminMassmailController',
        'massmailDetails');
    $this->addRoute('admin_massmail_details_mailing', 'admin/massmail/details/:id:/:type:',
        'AdminMassmailController', 'massmailDetailsMailing');
    $this->addRoute('admin_massmail_details_mailing_pages', 'admin/massmail/details/:id:/:type:/page/:page:',
        'AdminMassmailController', 'massmailDetailsMailing');
    $this->addRoute('admin_massmail_edit', 'admin/massmail/edit/:id:', 'AdminMassmailController', 'massmailEdit');
    $this->addRoute('admin_massmail_enqueue', 'admin/massmail/enqueue/:id:', 'AdminMassmailController',
        'massmailEnqueue');
    $this->addRoute('admin_massmail_unqueue', 'admin/massmail/unqueue/:id:', 'AdminMassmailController',
        'massmailUnqueue');
    $this->addRoute('admin_massmail_getadminunits', 'admin/massmail/getadminunits/:countrycode:',
        'AdminMassmailController', 'getAdminUnits');
    $this->addRoute('admin_massmail_getplaces', 'admin/massmail/getplaces/:countrycode:/:adminunit:',
        'AdminMassmailController', 'getPlaces');
    $this->addRoute('admin_massmail_trigger', 'admin/massmail/trigger/:id:', 'AdminMassmailController',
        'massmailTrigger');
    $this->addRoute('admin_massmail_untrigger', 'admin/massmail/untrigger/:id:', 'AdminMassmailController',
        'massmailUntrigger');

    // admin treasurer routes
    $this->addRoute('admin_treasurer_overview', 'admin/treasurer', 'AdminTreasurerController', 'treasurerOverview');
    $this->addRoute('admin_treasurer_add_donation', 'admin/treasurer/add', 'AdminTreasurerController', 'treasurerEditCreateDonation');
    $this->addRoute('admin_treasurer_edit_donation', 'admin/treasurer/edit/:id:', 'AdminTreasurerController', 'treasurerEditCreateDonation');
    $this->addRoute('admin_treasurer_campaign_start', 'admin/treasurer/campaign/start', 'AdminTreasurerController', 'treasurerStartDonationCampaign');
    $this->addRoute('admin_treasurer_campaign_stop', 'admin/treasurer/campaign/stop', 'AdminTreasurerController', 'treasurerStopDonationCampaign');
    $this->addRoute('admin_treasurer_overview', 'admin/treasurer', 'AdminTreasurerController', 'treasurerOverview');

    // Simple newsletter page
    $this->addRoute('newsletter', 'newsletter', 'NewsletterController', 'index');
    $this->addRoute('api_member','api/v1/member/:username:\.:format:', 'ApiController', 'memberAction');

    // places routes
    $this->addRoute('places', 'places', 'PlacesController', 'countries');
    $this->addRoute('places_country', 'places/:countryname:/:countrycode:', 'PlacesController', 'country');
    $this->addRoute('places_country_page', 'places/:countryname:/:countrycode:/page/:page:', 'PlacesController', 'country');
    $this->addRoute('places_region', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:', 'PlacesController', 'region');
    $this->addRoute('places_region_page', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:/page/:page:', 'PlacesController', 'region');
    $this->addRoute('places_city', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:/:cityname:/:citycode:', 'PlacesController', 'city');
    $this->addRoute('places_city_page', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:/:cityname:/:citycode:/page/:page:', 'PlacesController', 'city');

    // activities feature
    $this->addRoute('activities', 'activities', 'ActivitiesController', 'activities');
    $this->addRoute('activities_my_activities', 'activities/myactivities', 'ActivitiesController', 'myActivities');
    $this->addRoute('activities_my_activities_pages', 'activities/myactivities/page/:pageno:', 'ActivitiesController', 'myActivities');
    $this->addRoute('activities_search', 'activities/search', 'ActivitiesController', 'search');
    $this->addRoute('activities_search_results', 'activities/search/:keyword:', 'ActivitiesController', 'search');
    $this->addRoute('activities_search_results_pages', 'activities/search/:keyword:/page/:pageno:', 'ActivitiesController', 'search');
    $this->addRoute('activities_create', 'activities/create', 'ActivitiesController', 'editcreate');
    $this->addRoute('activities_upcoming_activities', 'activities/upcoming', 'ActivitiesController', 'upcomingActivities');
    $this->addRoute('activities_upcoming_activities_pages', 'activities/upcoming/page/:pageno:', 'ActivitiesController', 'upcomingActivities');
    $this->addRoute('activities_past_activities', 'activities/past', 'ActivitiesController', 'pastActivities');
    $this->addRoute('activities_past_activities_pages', 'activities/past/page/:pageno:', 'ActivitiesController', 'pastActivities');
    $this->addRoute('activities_near_me', 'activities/nearme', 'ActivitiesController', 'activitiesNearMe');
    $this->addRoute('activities_near_me_pages', 'activities/nearme/page/:pageno:', 'ActivitiesController', 'activitiesNearMe');
    $this->addRoute('activities_upcoming_activities', 'activities/upcoming', 'ActivitiesController', 'upcomingActivities');
    $this->addRoute('activities_upcoming_activities_pages', 'activities/upcoming/page/:pageno:', 'ActivitiesController', 'upcomingActivities');
    $this->addRoute('activities_edit', 'activities/:id:/edit', 'ActivitiesController', 'editcreate');
    $this->addRoute('activities_show', 'activities/:id:', 'ActivitiesController', 'show');
    $this->addRoute('activities_show_attendees', 'activities/:id:/attendees/page/:page:', 'ActivitiesController', 'show');

    // suggestions feature
    $this->addRoute('suggestions_about', 'suggestions/about', 'SuggestionsController', 'about');
    $this->addRoute('suggestions', 'suggestions', 'SuggestionsController', 'suggestions');
    $this->addRoute('suggestions_show', 'suggestions/:id:', 'SuggestionsController', 'show');
    $this->addRoute('suggestions_view', 'suggestions/:id:/view', 'SuggestionsController', 'view');
    $this->addRoute('suggestions_create', 'suggestions/create', 'SuggestionsController', 'editCreate');
    $this->addRoute('suggestions_edit', 'suggestions/:id:/edit', 'SuggestionsController', 'editCreate');
    $this->addRoute('suggestions_approvelist', 'suggestions/approve', 'SuggestionsController', 'approveList');
    $this->addRoute('suggestions_approvelist_pages', 'suggestions/approve/page/:pageno:', 'SuggestionsController', 'approveList');
    $this->addRoute('suggestions_approve', 'suggestions/:id:/approve', 'SuggestionsController', 'approve');
    $this->addRoute('suggestions_discusslist', 'suggestions/discuss', 'SuggestionsController', 'discussList');
    $this->addRoute('suggestions_discusslist_pages', 'suggestions/discuss/page/:pageno:', 'SuggestionsController', 'discussList');
    $this->addRoute('suggestions_discuss', 'suggestions/:id:/discuss', 'SuggestionsController', 'discuss');
    $this->addRoute('suggestions_discuss_reply', 'suggestions/:id:/discuss/reply', 'SuggestionsController', 'discussReply');
    $this->addRoute('suggestions_optionslist', 'suggestions/addoptions', 'SuggestionsController', 'addOptionsList');
    $this->addRoute('suggestions_optionslist_pages', 'suggestions/addoptions/page/:pageno:', 'SuggestionsController', 'addOptionsList');
    $this->addRoute('suggestions_add_options', 'suggestions/:id:/addoptions', 'SuggestionsController', 'addOptions');
    $this->addRoute('suggestions_add_options_reply', 'suggestions/:id:/addoptions/reply', 'SuggestionsController', 'addOptionsReply');
    $this->addRoute('suggestions_add_options_edit', 'suggestions/:id:/addoptions/:optid:/edit', 'SuggestionsController', 'editOption');
    $this->addRoute('suggestions_add_options_delete', 'suggestions/:id:/addoptions/:optid:/delete', 'SuggestionsController', 'deleteOption');
    $this->addRoute('suggestions_add_options_restore', 'suggestions/:id:/addoptions/:optid:/restore', 'SuggestionsController', 'restoreOption');
    $this->addRoute('suggestions_votelist', 'suggestions/vote', 'SuggestionsController', 'voteList');
    $this->addRoute('suggestions_votelist_pages', 'suggestions/vote/page/:pageno:', 'SuggestionsController', 'voteList');
    $this->addRoute('suggestions_vote', 'suggestions/:id:/vote', 'SuggestionsController', 'vote');
    $this->addRoute('suggestions_exclude', 'suggestions/:id:/exclude', 'SuggestionsController', 'exclude');
    $this->addRoute('suggestions_ranklist', 'suggestions/rank', 'SuggestionsController', 'rankList');
    $this->addRoute('suggestions_ranklist_pages', 'suggestions/rank/page/:pageno:', 'SuggestionsController', 'rankList');
    $this->addRoute('suggestions_rank', 'suggestions/:id:/rank', 'SuggestionsController', 'rank');
    $this->addRoute('suggestions_options_implementing', 'suggestions/:id:/implementing/:optionid:', 'SuggestionsController', 'moveOptionToImplementing');
    $this->addRoute('suggestions_options_implemented', 'suggestions/:id:/implemented/:optionid:', 'SuggestionsController', 'moveOptionToImplemented');
    $this->addRoute('suggestions_implemented', 'suggestions/:id:/implemented', 'SuggestionsController', 'moveSuggestionToImplemented');
    $this->addRoute('suggestions_upvote', 'suggestions/:optionid:/upvote', 'SuggestionsController', 'voteRanking');
    $this->addRoute('suggestions_downvote', 'suggestions/:optionid:/downvote', 'SuggestionsController', 'voteRanking');
    $this->addRoute('suggestions_rank_ajax', 'suggestions/ajax/:optionid:/:direction:', 'SuggestionsController', 'voteAjaxRanking');
    $this->addRoute('suggestions_rejectedlist', 'suggestions/rejected', 'SuggestionsController', 'rejectedList');
    $this->addRoute('suggestions_rejectedlist_pages', 'suggestions/rejected/page/:pageno:', 'SuggestionsController', 'rejectedList');
    $this->addRoute('suggestions_rejected', 'suggestions/:id:/rejected', 'SuggestionsController', 'rejected');
    $this->addRoute('suggestions_devlist', 'suggestions/dev', 'SuggestionsController', 'devList');
    $this->addRoute('suggestions_devlist_pages', 'suggestions/dev/page/:pageno:', 'SuggestionsController', 'devList');
    $this->addRoute('suggestions_dev', 'suggestions/:id:/dev', 'SuggestionsController', 'dev');
    $this->addRoute('suggestions_resultslist', 'suggestions/results', 'SuggestionsController', 'resultsList');
    $this->addRoute('suggestions_resultslist_pages', 'suggestions/results/page/:pageno:', 'SuggestionsController', 'resultsList');
    $this->addRoute('suggestions_results', 'suggestions/:id:/results', 'SuggestionsController', 'results');
    $this->addRoute('suggestions_team', 'suggestions/team', 'SuggestionsController', 'team');
    $this->addRoute('suggestions_search_results', 'suggestions/search/:keyword:', 'SuggestionsController', 'search');
    $this->addRoute('suggestions_search_results_page', 'suggestions/search/:keyword:/page/:pageno:', 'SuggestionsController', 'search');

    // searchmembers
    $this->addRoute('searchmembers', 'search', 'SearchController', 'searchMembers');
    $this->addRoute('searchmembers_map', 'search/members/map', 'SearchController', 'searchMembersOnMap');
    $this->addRoute('searchmembers_map_advanced', 'search/members/map/advanced', 'SearchController', 'searchMembersOnMap');
    $this->addRoute('searchmembers_text', 'search/members/text', 'SearchController', 'searchMembersText');
    $this->addRoute('searchmembers_text_advanced', 'search/members/text/advanced', 'SearchController', 'searchMembersText');
    $this->addRoute('searchmembers_advanced', 'search/members/advanced', 'SearchController', 'loadAdvancedOptions');
    $this->addRoute('search_places', 'search/locations/:type:', 'SearchController', 'searchSuggestLocations');

    $this->addRoute('searchmembers_username', 'search/members/username', 'SearchController', 'searchMemberUsernames');

    // safety pages
    $this->addRoute('safety', 'safety', 'SafetyController', 'safety');
    $this->addRoute('safety_basics', 'safety/basics', 'SafetyController', 'safetyBasics');
    $this->addRoute('safety_whattodo', 'safety/whattodo', 'SafetyController', 'safetyWhatToDo');
    $this->addRoute('safety_tips', 'safety/tips', 'SafetyController', 'safetyTips');
    $this->addRoute('safety_female', 'safety/female', 'SafetyController', 'safetyFemale');
    $this->addRoute('safety_faq', 'safety/faq', 'SafetyController', 'safetyFaq');
    $this->addRoute('safety_team', 'safety/team', 'SafetyController', 'safetyTeam');
    $this->addRoute('safety_contact', 'feedback?IdCategory=2', 'SafetyController', 'safetyContact');

    // New Members Be Welcome
    $this->addRoute('newmembers', 'admin/newmembers', 'AdminNewMembersController', 'listMembers');
    $this->addRoute('newmembers_pages', 'admin/newmembers/page/:pageno:', 'AdminNewMembersController', 'listMembers');
    $this->addRoute('newmembers_local_greeting', 'admin/newmembers/local/:username:', 'AdminNewMembersController', 'composeMessage');
    $this->addRoute('newmembers_global_greeting', 'admin/newmembers/global/:username:', 'AdminNewMembersController', 'composeMessage');

    // Trips
    $this->addRoute('trips_all', 'trips/:type:/all', 'TripsController', 'tripsAll');
    $this->addRoute('trips', 'trips', 'TripsController', 'trips');
    $this->addRoute('trips_my_trips', 'trips/mytrips', 'TripsController', 'myTrips');
    $this->addRoute('trips_my_trips_pages', 'trips/mytrips/page/:pageno:', 'TripsController', 'myTrips');
    $this->addRoute('trips_show', 'trips/:id:/show', 'TripsController', 'showTrip');
    $this->addRoute('trip_show_username', 'trip/show/:username:', 'TripController', 'showTripsForUsername');
    $this->addRoute('trip_show_usernamepages', 'trip/show/:username:/page/:pageno:', 'TripController', 'showTripsForUsername');
    $this->addRoute('trips_create', 'trips/create', 'TripsController', 'createTrip');
    $this->addRoute('trips_upcoming', 'trips/upcoming', 'TripsController', 'upcomingTrips');
    $this->addRoute('trips_upcoming_trips_pages', 'trips/upcoming/page/:pageno:', 'TripsController', 'upcomingTrips');
    $this->addRoute('trips_past_trips', 'trips/past', 'TripsController', 'pastTrips');
    $this->addRoute('trips_past_trips_pages', 'trips/past/page/:pageno:', 'TripsController', 'pastTrips');
    $this->addRoute('trips_near_me', 'trips/nearme', 'TripsController', 'tripsNearMe');
    $this->addRoute('trips_near_me_pages', 'trips/nearme/page/:pageno:', 'TripsController', 'tripsNearMe');
    $this->addRoute('trips_show', 'trips/:id:', 'TripsController', 'show');
    $this->addRoute('trips_edit', 'trips/:id:/edit', 'TripsController', 'editTrip');
    $this->addRoute('trip_delete', 'trip/:id:/delete', 'TripController', 'deleteTrip');
    $this->addRoute('trips_search', 'trips/search', 'TripsController', 'search');
    $this->addRoute('trips_search_results', 'trips/search/:keyword:', 'TripsController', 'search');
    $this->addRoute('trips_search_results_pages', 'trips/search/:keyword:/page/:pageno:', 'TripsController', 'search');
    $this->addRoute('trips_add_location', 'trips/addlocation/:number:', 'TripsController', 'addLocation');
    $this->addRoute('trips_empty', 'trips/empty', 'TripsController', 'emptyPage');

// $this->addRoute('trip_numerical', 'trip/:tripid:', 'TripController', 'showSingleTrip');
// $this->addRoute('trip_create', 'trip/create', 'TripController', 'createTrip');
// $this->addRoute('trip_edit', 'trip/edit/:tripid:', 'TripController', 'editTrip');

    // Update statistics
    $this->addRoute('updatestats', 'about/updatestats', 'AboutController', 'updateStatistics');

    // Data retention (#1885)
    $this->addRoute('dataretention', 'members/dataretention', 'MembersController', 'dataRetention');

    $this->addRoute('admin_subscriptions', 'admin/subscriptions', 'AdminSubscriptionsController', 'manage');
    $this->addRoute('login_message_close', 'close/:id:', 'LoginController', 'close');

    // Terms of use
    $this->addRoute('terms_french', 'terms', 'AboutController', 'termsOfUse');
    $this->addRoute('terms_language', 'terms/:language:', 'AboutController', 'termsOfUse');
$this->addRoute('m_dashboard', 'mockups/dashboard', 'MockupsController', 'dashboard');
$this->addRoute('m_select2', 'mockups/select2', 'MockupsController', 'select2');