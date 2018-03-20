<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mediatek
 *
 * @ORM\Table(name="mediatek")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediatekRepository")
 */
class Mediatek
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
     * @var int
     *
     * @ORM\Column(name="account_id", type="integer", unique=true)
     */
    private $accountId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean")
     */
    private $isPublic;

    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer", unique=true)
     */
    private $groupId;


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
     * Set accountId.
     *
     * @param int $accountId
     *
     * @return Mediatek
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Get accountId.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
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

    /**
     * Set groupId.
     *
     * @param int $groupId
     *
     * @return Mediatek
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId.
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}
