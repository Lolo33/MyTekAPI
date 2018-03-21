<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediatekUser
 *
 * @ORM\Table(name="mediatek_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediatekUserRepository")
 */
class MediatekUser extends Mediatek
{

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Account")
     */
    private $account;

    /**
     * Set account
     *
     * @param \AppBundle\Entity\Account $account
     *
     * @return MediatekUser
     */
    public function setAccount(\AppBundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \AppBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }


}
