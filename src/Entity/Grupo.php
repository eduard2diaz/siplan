<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * @ORM\Entity
 */
class Grupo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="grupos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creador", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $creador;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Usuario", inversedBy="grupospertenece")
     */
    private $idmiembro;

    public function __construct()
    {
        $this->idmiembro = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCreador(): ?Usuario
    {
        return $this->creador;
    }

    public function setCreador(?Usuario $creador): self
    {
        $this->creador = $creador;

        return $this;
    }

    /**
     * @return Collection|Usuario[]
     */
    public function getIdmiembro(): Collection
    {
        return $this->idmiembro;
    }

    public function addIdmiembro(Usuario $idmiembro): self
    {
        if (!$this->idmiembro->contains($idmiembro)) {
            $this->idmiembro[] = $idmiembro;
        }

        return $this;
    }

    public function removeIdmiembro(Usuario $idmiembro): self
    {
        if ($this->idmiembro->contains($idmiembro)) {
            $this->idmiembro->removeElement($idmiembro);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {

        if (null == $this->getCreador()) {
            $context->setNode($context, 'creador', null, 'data.creador');
            $context->addViolation('Seleccione un creador');
        }
        elseif ($this->getIdmiembro()->contains($this->getCreador())) {
            $context->setNode($context, 'idmiembro', null, 'data.idmiembro');
            $context->addViolation('El creador del grupo no debe ser miembro del mismo.');
        }
    }
}
