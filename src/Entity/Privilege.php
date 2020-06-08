<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Privileges.
 *
 * @ORM\Table(name="privileges", uniqueConstraints={@ORM\UniqueConstraint(name="controller", columns={"controller", "method", "type"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Privilege
{
    public const ALL_CONTROLLERS = '*';
    public const GROUP_CONTROLLER = 'GroupsController';
    public const RIGHTS_CONTROLLER = 'RightsController';

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=64, nullable=false)
     */
    private $controller;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=64, nullable=false)
     */
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set controller.
     *
     * @param string $controller
     *
     * @return Privilege
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get controller.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set method.
     *
     * @param string $method
     *
     * @return Privilege
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Privilege
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
