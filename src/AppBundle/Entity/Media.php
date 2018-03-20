<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 *
 * @ORM\Table(name="media")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediaRepository")
 */
class Media
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
     * @ORM\Column(name="id_imdb", type="string", length=255, unique=true)
     */
    private $idImdb;


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
     * Set idImdb.
     *
     * @param string $idImdb
     *
     * @return Media
     */
    public function setIdImdb($idImdb)
    {
        $this->idImdb = $idImdb;

        return $this;
    }

    /**
     * Get idImdb.
     *
     * @return string
     */
    public function getIdImdb()
    {
        return $this->idImdb;
    }
}
