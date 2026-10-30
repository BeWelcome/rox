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

    // group routes
    $this->addRoute('group_start','group/:group_id:', 'GroupsController', 'showGroup');
    $this->addRoute('group_new_post', 'group/:group_id:/new', 'GroupsController', 'newPost');
    $this->addRoute('group_acceptinvitation','group/:group_id:/acceptinvitation/:member_id:', 'GroupsController', 'acceptInvitation');
    $this->addRoute('group_acceptmember','group/:group_id:/acceptmember/:member_id:', 'GroupsController', 'acceptMember');
    $this->addRoute('group_banmember','group/:group_id:/banmember/:member_id:', 'GroupsController', 'banMember');
    $this->addRoute('group_addadmin','group/:group_id:/addAdmin/:member_id:', 'GroupsController', 'addMemberAsAdmin');
    $this->addRoute('group_resignadmin','group/:group_id:/resignAdmin', 'GroupsController', 'resignAsAdmin');
    $this->addRoute('group_declineinvitation','group/:group_id:/declineinvitation/:member_id:', 'GroupsController', 'declineInvitation');
    $this->addRoute('group_declinemember','group/:group_id:/declinemember/:member_id:', 'GroupsController', 'declineMember');
    $this->addRoute('group_delete','group/:group_id:/delete', 'GroupsController', 'delete');
    $this->addRoute('group_deleted','group/:group_id:/delete/true', 'GroupsController', 'delete');
    $this->addRoute('group_forum','group/:group_id:/forum', 'GroupsController', 'forum');
    $this->addRoute('group_forum_pages','group/:group_id:/forum/page:page_id:', 'GroupsController', 'forum');
    $this->addRoute('group_forum_thread','group/:group_id:/forum/s:thread:', 'GroupsController', 'forum');
    $this->addRoute('group_forum_thread_pages','group/:group_id:/forum/s:thread:/page:page_id:', 'GroupsController', 'forum');
    $this->addRoute('group_forum_thread_slash','group/:group_id:/forum/:thread:/', 'GroupsController', 'forum');
    $this->addRoute('group_forum_thread_title','group/:group_id:/forum/s:thread:-:title:', 'GroupsController', 'forum');
    $this->addRoute('group_forum_thread_title_slash','group/:group_id:/forum/:thread:-:title:/', 'GroupsController', 'forum');
    $this->addRoute('group_forum_action','group/:group_id:/forum/:thread:/:action:', 'GroupsController', 'forum');
    $this->addRoute('group_groupsettings','group/:group_id:/groupsettings', 'GroupsController', 'groupSettings');
    $this->addRoute('group_kickmember','group/:group_id:/kickmember/:member_id:', 'GroupsController', 'kickMember');
    $this->addRoute('group_leave','group/:group_id:/leave', 'GroupsController', 'leave');
    $this->addRoute('group_left','group/:group_id:/leave/true', 'GroupsController', 'left');
    $this->addRoute('group_memberadministration','group/:group_id:/memberadministration', 'GroupsController', 'memberAdministration');
    $this->addRoute('group_memberadministration_paged','group/:group_id:/memberadministration/page/:page_number:', 'GroupsController', 'memberAdministration');
    $this->addRoute('group_members','group/:group_id:/members', 'GroupsController', 'members');
    $this->addRoute('group_members_paged','group/:group_id:/members/page/:page_number:', 'GroupsController', 'members');
    $this->addRoute('group_membersearch_ajax','group/:group_id:/membersearchajax/:search_term:', 'GroupsController', 'memberSearchAjax');
    $this->addRoute('group_membersettings','group/:group_id:/membersettings', 'GroupsController', 'memberSettings');
    $this->addRoute('group_search_discussions','group/:group_id:/search', 'GroupsController', 'searchDiscussions');
    $this->addRoute('group_wiki','group/:group_id:/wiki', 'GroupsController', 'wiki');
    $this->addRoute('group_realimg','group/realimg/:group_id:', 'GroupsController', 'realImg');
    $this->addRoute('group_thumbimg','group/thumbimg/:group_id:', 'GroupsController', 'thumbImg');
    $this->addRoute('groups_featured','groups/featured', 'GroupsController', 'featured');
    $this->addRoute('groups_forums_overview','groups/forums', 'GroupsController', 'groupForumsOverview');
    $this->addRoute('groups_forums_overview_paged','groups/forums/:page_number:', 'GroupsController', 'groupForumsOverview');
    $this->addRoute('groups_mygroups','groups/mygroups', 'GroupsController', 'myGroups');
    $this->addRoute('groups_search','groups/search', 'GroupsController', 'search');

    // related groups routes
    $this->addRoute('relatedgroup_select','group/:group_id:/selectrelatedgroup', 'RelatedGroupsController', 'selectRelatedGroup');
    $this->addRoute('relatedgroup_add','group/:group_id:/addrelatedgroup/:related_id:', 'RelatedGroupsController', 'addRelatedGroup');
    $this->addRoute('relatedgroup_selectdelete','group/:group_id:/selectdeleterelatedgroup', 'RelatedGroupsController', 'selectdeleteRelatedGroup');
    $this->addRoute('relatedgroup_delete','group/:group_id:/deleterelatedgroup/:related_id:', 'RelatedGroupsController', 'deleteRelatedGroup');
    $this->addRoute('relatedgroup_log','group/:group_id:/relatedgroupsettings', 'RelatedGroupsController', 'showRelatedGroupLog');

    // member app routes
    $this->addRoute('members_profile_old', '/members/:username:/old', 'MembersController', 'index');
    $this->addRoute('members_profile_notification', '/members/:username:/:message:', 'MembersController', 'index');
    $this->addRoute('members_profile_invite', '/members/:username:/leg/:id:', 'MembersController', 'index');
    $this->addRoute('edit_my_profile', '/editmyprofile', 'MembersController', 'index');

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

    // admin app routes: the tools themselves are Symfony pages (/admin/tools, /admin/comment, /admin/translations, ...)
    $this->addRoute('admin_norights', 'admin/norights', 'AdminBaseController', 'noRights');

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

    // places routes: all in App\Controller\PlacesController (countries, country, region, town)

    // searchmembers
    $this->addRoute('searchmembers', 'search', 'SearchController', 'searchMembers');
    $this->addRoute('searchmembers_map', 'search/members/map', 'SearchController', 'searchMembersOnMap');
    $this->addRoute('searchmembers_map_advanced', 'search/members/map/advanced', 'SearchController', 'searchMembersOnMap');
    $this->addRoute('searchmembers_text', 'search/members/text', 'SearchController', 'searchMembersText');
    $this->addRoute('searchmembers_text_advanced', 'search/members/text/advanced', 'SearchController', 'searchMembersText');
    $this->addRoute('searchmembers_advanced', 'search/members/advanced', 'SearchController', 'loadAdvancedOptions');
    $this->addRoute('search_places', 'search/locations/:type:', 'SearchController', 'searchSuggestLocations');

    $this->addRoute('searchmembers_username', 'search/members/username', 'SearchController', 'searchMemberUsernames');

    // New Members Be Welcome
    $this->addRoute('newmembers', 'admin/newmembers', 'AdminNewMembersController', 'listMembers');
    $this->addRoute('newmembers_pages', 'admin/newmembers/page/:pageno:', 'AdminNewMembersController', 'listMembers');
    $this->addRoute('newmembers_local_greeting', 'admin/newmembers/local/:username:', 'AdminNewMembersController', 'composeMessage');
    $this->addRoute('newmembers_global_greeting', 'admin/newmembers/global/:username:', 'AdminNewMembersController', 'composeMessage');

    // Data retention (#1885)
    $this->addRoute('dataretention', 'members/dataretention', 'MembersController', 'dataRetention');

    $this->addRoute('admin_subscriptions', 'admin/subscriptions', 'AdminSubscriptionsController', 'manage');
    $this->addRoute('login_message_close', 'close/:id:', 'LoginController', 'close');

    // Terms of use
    $this->addRoute('old_terms_french', 'terms/old/fr', 'AboutController', 'termsOfUse');
    $this->addRoute('old_terms_language', 'terms/old/:locale:', 'AboutController', 'termsOfUse');

    // Language switch (done in new code; dummy to have route available in members.ctrl.php when changing the language preference)
    $this->addRoute('rox_in_language', 'rox/in/:language:', '', '');
