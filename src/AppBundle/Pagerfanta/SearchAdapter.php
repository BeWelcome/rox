<?php

namespace AppBundle\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;

class SearchAdapter implements AdapterInterface
{
    private $results;
    private $data;

    public function __construct($results, $data)
    {
        $this->results = $results;
        $this->data = $data;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        return $this->results['countOfMembers'];
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        return $this->results['members'];
    }
}