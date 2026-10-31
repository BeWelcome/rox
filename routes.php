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
