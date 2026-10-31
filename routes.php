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

    // member app routes
    $this->addRoute('members_profile_retired', 'retired', 'MembersController', 'retired');

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

    // New Members Be Welcome
    $this->addRoute('newmembers', 'admin/newmembers', 'AdminNewMembersController', 'listMembers');
    $this->addRoute('newmembers_pages', 'admin/newmembers/page/:pageno:', 'AdminNewMembersController', 'listMembers');
    $this->addRoute('newmembers_local_greeting', 'admin/newmembers/local/:username:', 'AdminNewMembersController', 'composeMessage');
    $this->addRoute('newmembers_global_greeting', 'admin/newmembers/global/:username:', 'AdminNewMembersController', 'composeMessage');

    $this->addRoute('admin_subscriptions', 'admin/subscriptions', 'AdminSubscriptionsController', 'manage');
