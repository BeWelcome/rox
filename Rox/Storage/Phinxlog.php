<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Phinxlog
 *
 * @ORM\Table(name="phinxlog", uniqueConstraints={@ORM\UniqueConstraint(name="version_UNIQUE", columns={"version"})})
 * @ORM\Entity
 */
class Phinxlog
{
    /**
     * @var string
     *
     * @ORM\Column(name="migration_name", type="string", length=100, nullable=true)
     */
    private $migrationName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=false)
     */
    private $startTime = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=false)
     */
    private $endTime = '0000-00-00 00:00:00';

    /**
     * @var boolean
     *
     * @ORM\Column(name="breakpoint", type="boolean", nullable=false)
     */
    private $breakpoint = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $version;



    /**
     * Set migrationName
     *
     * @param string $migrationName
     *
     * @return Phinxlog
     */
    public function setMigrationName($migrationName)
    {
        $this->migrationName = $migrationName;

        return $this;
    }

    /**
     * Get migrationName
     *
     * @return string
     */
    public function getMigrationName()
    {
        return $this->migrationName;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return Phinxlog
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     *
     * @return Phinxlog
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set breakpoint
     *
     * @param boolean $breakpoint
     *
     * @return Phinxlog
     */
    public function setBreakpoint($breakpoint)
    {
        $this->breakpoint = $breakpoint;

        return $this;
    }

    /**
     * Get breakpoint
     *
     * @return boolean
     */
    public function getBreakpoint()
    {
        return $this->breakpoint;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }
}
