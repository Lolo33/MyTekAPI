<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserKey
 *
 * @ORM\Table(name="user_token")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserKeyRepository")
 */
class UserKey
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
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="ApiUser")
     * @var ApiUser
     */
    private $apiUser;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return UserKey
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return UserKey
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return String
     */
    public function getCreatedAt()
    {
        return $this->createdAt->format("d-m-Y H:i:s");
    }

    /**
     * Set ApiUserRepository
     *
     * @param ApiUser $apiUser
     *
     * @return UserKey
     */
    public function setApiUser($apiUser)
    {
        $this->apiUser = $apiUser;

        return $this;
    }

    /**
     * Get ApiUserRepository
     *
     * @return ApiUser
     */
    public function getApiUser()
    {
        return $this->apiUser;
    }

}
