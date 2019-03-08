<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Plantrabajo;
use App\Entity\Usuario;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Respuesta
 *
 * @ORM\Table(name="respuesta")
 * @ORM\Entity
 */
class Respuesta
{
    /**
     * @var \Actividad
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Actividad")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="id" ,onDelete="Cascade")
     * })
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Fichero", mappedBy="respuesta", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $ficheros;

    /**
     * Respuesta constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->ficheros= new ArrayCollection();
    }

    /**
     * @return \Actividad
     */
    public function getId(): ?Actividad
    {
        return $this->id;
    }

    /**
     * @param \Actividad $id
     */
    public function setId(Actividad $id): void
    {
        $this->id = $id;
    }

    /**
     * @return null|string
     */
    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    /**
     * @param null|string $descripcion
     */
    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    /**
     * @return Collection|Fichero[]
     */
    public function getFicheros(): Collection
    {
        return $this->ficheros;
    }

    public function addFichero(Fichero $fichero): self
    {
        if (!$this->ficheros->contains($fichero)) {
            $this->ficheros[] = $fichero;
            $fichero->setRespuesta($this);
        }

        return $this;
    }

    public function removeFichero(Fichero $fichero): self
    {
        if ($this->ficheros->contains($fichero)) {
            $this->ficheros->removeElement($fichero);
            // set the owning side to null (unless already changed)
            /*if ($fichero->getRespuesta() === $this) {
                $fichero->setRespuesta(null);
            }*/
        }

        return $this;
    }

    public function setFichero($ficheros)
    {
        $this->ficheros = $ficheros;
        foreach ($ficheros as $address) {
            $address->setRespuesta($this);
        }
    }

    /**
     * @Assert\Callback
     */
    public function comprobarFechas(ExecutionContextInterface $context)
    {
    }

}
