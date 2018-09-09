<?php

namespace AppBundle\Pagerfanta;

use AppBundle\Entity\Member;
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
     * @param array              $data      The query parameters for the search
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
    public function getMapResults()
    {
        $results = $this->model->getResultsForLocation($this->modelData);

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
     * @param array $data
     *
     * @return array|string
     */
    private function prepareModelData($data)
    {
        $vars = [];
        $vars['search-location'] = $data['search'];
        $vars['location-geoname-id'] = $data['search_geoname_id'];
        $vars['location-latitude'] = $data['search_latitude'];
        $vars['location-longitude'] = $data['search_longitude'];
        $vars['search-accommodation'] = [];

        if (isset($data['search_accommodation_anytime']) && ($data['search_accommodation_anytime'])) {
            $vars['search-accommodation'][] = 'anytime';
        }

        if (isset($data['search_accommodation_dependonrequest']) && ($data['search_accommodation_dependonrequest'])) {
            $vars['search-accommodation'][] = 'dependonrequest';
        }

        if (isset($data['search_accommodation_neverask']) && ($data['search_accommodation_neverask'])) {
            $vars['search-accommodation'][] = 'neverask';
        }

        $vars['search-distance'] = $data['search_distance'];
        $vars['search-can-host'] = $data['search_can_host'];
        $vars['search-number-items'] = 10;
        $vars['search-sort-order'] = 6;

        return $vars;
    }
}
