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
     * @return int the number of results
     */
    public function getNbResults()
    {
        return $this->results['countOfMembers'];
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
        return $this->results['members'];
    }
}
