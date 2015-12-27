<?php

namespace Rox\Search\Members;

use Rox\Framework\TwigView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class SearchMembersPage extends TwigView
{
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->setTemplate('searchmembers.html.twig', 'search');
        $this->addLateJavascriptFile('/jquery-ui-1.11.2/jquery-ui.js');
        $this->addLateJavascriptFile('leaflet/0.7.2/leaflet.js');
        $this->addLateJavascriptFile('leaflet/plugins/Leaflet.markercluster/0.4.0/leaflet.markercluster.js');
        $this->addLateJavascriptFile('search/createmap.js');
        $this->addLateJavascriptFile('search/searchpicker.js');
        $this->addStylesheet('/script/leaflet/0.7.2/leaflet.css');
        $this->addStylesheet('/script/leaflet/plugins/Leaflet.markercluster/0.4.0/MarkerCluster.css');
        $this->addStylesheet('/script/leaflet/plugins/Leaflet.markercluster/0.4.0/MarkerCluster.Default.css');
        $this->addStylesheet('/script/jquery-ui-1.11.2/jquery-ui.css');

        $this->addParameters([
            'location' => [
                'name' => null,
                'geoname-id' => 0,
                'latitude' => null,
                'longitude' => null,
                ],
            'accommodation' => [
                'yes' => 'checked',
                'maybe' => '',
                'no' => 'checked'
            ],
            'canhost' => [
                'values' => [
                    0 => '0',
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                    10 => '10',
                    20 => '20'
                ],
                'value' => 4
            ],
            'radius' => [
                'values' => [
                    5 => '5km / 3mi',
                    10 => '10km / 6mi',
                    25 => '20km / 15mi',
                    50 => '50km / 31mi',
                    100 => '100km / 63mi',
                ],
                'value' => 25
            ]
        ]);
    }
}
