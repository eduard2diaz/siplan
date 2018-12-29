<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Area
 *
 * @ORM\Table(name="area")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","padre"})
 */
class Area
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="rol_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string")
     */
    private $nombre;

    /**
     * @var \Area
     *
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="padre", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $padre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ARC", inversedBy="areas")
     */
    private $areaconocimiento;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }



    /**
     * @return null|string
     */
    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    /**
     * @param null|string $nombre
     */
    public function setNombre(?string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @return \Area
     */
    public function getPadre(): ?Area
    {
        return $this->padre;
    }

    /**
     * @param \Area $area
     */
    public function setPadre(Area $area): void
    {
        $this->padre = $area;
    }

    public function getAreaconocimiento(): ?ARC
    {
        return $this->areaconocimiento;
    }

    public function setAreaconocimiento(?ARC $areaconocimiento): self
    {
        $this->areaconocimiento = $areaconocimiento;

        return $this;
    }

}
