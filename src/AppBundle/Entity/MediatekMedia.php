<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediatekMedia
 *
 * @ORM\Table(name="mediatek_media")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediatekMediaRepository")
 */
class MediatekMedia
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
     * @ORM\ManyToOne(targetEntity="Media")
     */
    private $media;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Mediatek")
     */
    private $mediatek;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="State")
     */
    private $state;


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
     * Set media
     *
     * @param \AppBundle\Entity\Media $media
     *
     * @return MediatekMedia
     */
    public function setMedia(\AppBundle\Entity\Media $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \AppBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set mediatek
     *
     * @param \AppBundle\Entity\Mediatek $mediatek
     *
     * @return MediatekMedia
     */
    public function setMediatek(\AppBundle\Entity\Mediatek $mediatek = null)
    {
        $this->mediatek = $mediatek;

        return $this;
    }

    /**
     * Get mediatek
     *
     * @return \AppBundle\Entity\Mediatek
     */
    public function getMediatek()
    {
        return $this->mediatek;
    }

    /**
     * Set state
     *
     * @param \AppBundle\Entity\State $state
     *
     * @return MediatekMedia
     */
    public function setState(\AppBundle\Entity\State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \AppBundle\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }
}
