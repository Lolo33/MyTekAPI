<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mediatekmedia
 *
 * @ORM\Table(name="mediatekmedia")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediatekmediaRepository")
 */
class Mediatekmedia
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
     * @ORM\Column(name="media_id", type="integer", unique=true)
     */
    private $mediaId;

    /**
     * @var int
     *
     * @ORM\Column(name="mediatek_id", type="integer", unique=true)
     */
    private $mediatekId;

    /**
     * @var int
     *
     * @ORM\Column(name="state_id", type="integer")
     */
    private $stateId;


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
     * Set mediaId.
     *
     * @param int $mediaId
     *
     * @return Mediatekmedia
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * Get mediaId.
     *
     * @return int
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * Set mediatekId.
     *
     * @param int $mediatekId
     *
     * @return Mediatekmedia
     */
    public function setMediatekId($mediatekId)
    {
        $this->mediatekId = $mediatekId;

        return $this;
    }

    /**
     * Get mediatekId.
     *
     * @return int
     */
    public function getMediatekId()
    {
        return $this->mediatekId;
    }

    /**
     * Set stateId.
     *
     * @param int $stateId
     *
     * @return Mediatekmedia
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;

        return $this;
    }

    /**
     * Get stateId.
     *
     * @return int
     */
    public function getStateId()
    {
        return $this->stateId;
    }
}
