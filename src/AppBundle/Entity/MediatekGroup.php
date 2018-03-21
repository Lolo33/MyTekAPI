<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediatekGroup
 *
 * @ORM\Table(name="mediatek_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediatekGroupRepository")
 */
class MediatekGroup extends Mediatek
{

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group")
     */
    private $group;



    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     *
     * @return MediatekGroup
     */
    public function setGroup(\AppBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
