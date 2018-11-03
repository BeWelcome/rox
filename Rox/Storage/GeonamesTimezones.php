<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeonamesTimezones
 *
 * @ORM\Table(name="geonames_timezones")
 * @ORM\Entity
 */
class GeonamesTimezones
{
    /**
     * @var string
     *
     * @ORM\Column(name="OffsetJanuary", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $offsetjanuary;

    /**
     * @var string
     *
     * @ORM\Column(name="OffsetJuly", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $offsetjuly;

    /**
     * @var integer
     *
     * @ORM\Column(name="TimeZoneId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $timezoneid;



    /**
     * Set offsetjanuary
     *
     * @param string $offsetjanuary
     *
     * @return GeonamesTimezones
     */
    public function setOffsetjanuary($offsetjanuary)
    {
        $this->offsetjanuary = $offsetjanuary;

        return $this;
    }

    /**
     * Get offsetjanuary
     *
     * @return string
     */
    public function getOffsetjanuary()
    {
        return $this->offsetjanuary;
    }

    /**
     * Set offsetjuly
     *
     * @param string $offsetjuly
     *
     * @return GeonamesTimezones
     */
    public function setOffsetjuly($offsetjuly)
    {
        $this->offsetjuly = $offsetjuly;

        return $this;
    }

    /**
     * Get offsetjuly
     *
     * @return string
     */
    public function getOffsetjuly()
    {
        return $this->offsetjuly;
    }

    /**
     * Get timezoneid
     *
     * @return integer
     */
    public function getTimezoneid()
    {
        return $this->timezoneid;
    }
}
