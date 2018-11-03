<?php

namespace App\Pagerfanta;

use App\Entity\Member;
use App\Form\CustomDataClass\SearchFormRequest;
use EnvironmentExplorer;
use Pagerfanta\Adapter\AdapterInterface;
use Rox\Framework\SessionSingleton;
use SearchModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SearchAdapter implements AdapterInterface
{
    private $modelData;

    /* @var SearchModel */
    private $model;

    /**
     * SearchAdapter constructor.
     *
     * @param ContainerInterface $container Needed for the time being to allow to use the old search model
     * @param SearchFormRequest  $data      The query parameters for the search
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct($container, $data)
    {
        // Kick-start the Symfony session. This replaces session_start() in the
        // old code, which is now turned off.
        $session = $container->get('session');
        $session->start();

        if (!$session->has('IdMember')) {
            $rememberMeToken = unserialize($session->get('_security_default'));
            if (null === $rememberMeToken) {
                throw new AccessDeniedException();
            }
            if (false !== $rememberMeToken) {
                /** @var Member $user */
                $user = $rememberMeToken->getUser();
                if (null !== $user) {
                    $session->set('IdMember', $user->getId());
                    $session->set('MemberStatus', $user->getStatus());
                    $session->set('APP_User_id', $user->getId());
                }
            }
        }

        // Make sure the Rox classes find this session
        SessionSingleton::createInstance($session);

        // make sure everything's setup for the old code used below
        $environmentExplorer = new EnvironmentExplorer();
        $environmentExplorer->initializeGlobalState(
            $container->getParameter('database_host'),
            $container->getParameter('database_name'),
            $container->getParameter('database_user'),
            $container->getParameter('database_password')
        );
        $this->model = new \SearchModel();
        $this->modelData = $this->prepareModelData($data);
        $this->model->prepareQuery($this->modelData);
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults()
    {
        return $this->model->getMembersCount(false);
    }

    /**
     * Returns map data.
     *
     * @return array|\Traversable the slice
     */
    public function getFullResults()
    {
        $results = $this->model->getResultsForLocation($this->modelData);

        return $results;
    }

    /**
     * Returns map data.
     *
     * @return array|\Traversable the slice
     */
    public function getMapResults()
    {
        $results = $this->model->getResultsForLocation($this->modelData);

        $results['members'] = null;
        $results['map'] = array_map(function ($value) {
            $value->Username = '';

            return $value;
        }, $results['map']);

        return $results;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|\Traversable the slice
     */
    public function getSlice($offset, $length)
    {
        $this->modelData['search-number-items'] = $length;
        $this->modelData['search-page'] = ($offset / $length) + 1;
        $results = $this->model->getResultsForLocation($this->modelData);

        return $results['members'];
    }

    /**
     * @param SearchFormRequest $data
     *
     * @return array|string
     */
    private function prepareModelData($data)
    {
        $vars = [];
        $vars['search-location'] = $data->location;
        $vars['location-geoname-id'] = $data->location_geoname_id;
        $vars['location-latitude'] = $data->location_latitude;
        $vars['location-longitude'] = $data->location_longitude;
        $vars['ne-latitude'] = $data->ne_latitude;
        $vars['ne-longitude'] = $data->ne_longitude;
        $vars['sw-latitude'] = $data->sw_latitude;
        $vars['sw-longitude'] = $data->sw_longitude;
        $vars['search-accommodation'] = [];

        if ($data->accommodation_anytime) {
            $vars['search-accommodation'][] = 'anytime';
        }

        if ($data->accommodation_dependonrequest) {
            $vars['search-accommodation'][] = 'dependonrequest';
        }

        if ($data->accommodation_neverask) {
            $vars['search-accommodation'][] = 'neverask';
        }

        if ($data->offerdinner) {
            $vars['search-typical-offers'][] = 'dinner';
        }

        if ($data->offertour) {
            $vars['search-typical-offers'][] = 'guidedtour';
        }

        if ($data->accessible) {
            $vars['search-typical-offers'][] = 'CanHostWeelChair';
        }

        $vars['search-distance'] = $data->distance;
        $vars['search-can-host'] = $data->can_host;
        $vars['search-gender'] = $data->gender;
        $vars['search-age-minimum'] = $data->min_age;
        $vars['search-age-maximum'] = $data->max_age;
        $vars['search-groups'] = $data->groups;
        $vars['search-languages'] = $data->languages;
        $vars['search-text'] = $data->keywords;
        $vars['search-number-items'] = 20;
        $vars['search-sort-order'] = 6;
        if ($data->inactive) {
            $vars['search-membership'] = 1;
        }
        $vars['search-sort-order'] = $data->order;
        $vars['search-number-items'] = $data->items;

        return $vars;
    }
}
