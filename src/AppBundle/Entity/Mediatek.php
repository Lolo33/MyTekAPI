<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mediatek
 *
 * @ORM\Table(name="mediatek")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediatekRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"grp" = "MediatekGroup", "user" = "MediatekUser"})
 */
abstract class Mediatek
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean")
     */
    private $isPublic;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isPublic.
     *
     * @param bool $isPublic
     *
     * @return Mediatek
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic.
     *
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

}
