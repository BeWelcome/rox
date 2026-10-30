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
    $this->addRoute('group_wiki','group/:group_id:/wiki', 'GroupsController', 'wiki');

    // related groups routes

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

    // places routes (the list of countries and the country page are in App\Controller\PlacesController)
    $this->addRoute('places_region', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:', 'PlacesController', 'region');
    $this->addRoute('places_region_page', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:/page/:page:', 'PlacesController', 'region');
    $this->addRoute('places_city', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:/:cityname:/:citycode:', 'PlacesController', 'city');
    $this->addRoute('places_city_page', 'places/:countryname:/:countrycode:/:regionname:/:regioncode:/:cityname:/:citycode:/page/:page:', 'PlacesController', 'city');

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
