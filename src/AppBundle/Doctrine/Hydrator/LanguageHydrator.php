<?php
namespace AppBundle\Doctrine\Hydrator;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

class LanguageHydrator extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        $result = array();
        $cache  = array();
        foreach($this->_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row, array &$result)
    {
        if(count($row) == 0) {
            return false;
        }

        $keys = array_keys($row);

        // Assume first column is id field
        $id = $row[$keys[0]];

        $value = false;

        if(count($row) == 2) {
            // If only one more field assume that this is the value field
            $value = $row[$keys[1]];
        } else {
            // Remove ID field and add remaining fields as value array
            array_shift($row);
            $value = $row;
        }

        $result[$id] = $value;
    }
}