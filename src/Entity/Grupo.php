<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GrupoRepository")
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
     * @ORM\Column(type="boolean")
     */
    private $activo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario", inversedBy="grupos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creador;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grupo", inversedBy="grupos")
     */
    private $grupopadre;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tributaplantrabajo;

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

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

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

    public function getGrupopadre(): ?self
    {
        return $this->grupopadre;
    }

    public function setGrupopadre(?self $grupopadre): self
    {
        $this->grupopadre = $grupopadre;

        return $this;
    }

    public function getTributaplantrabajo(): ?bool
    {
        return $this->tributaplantrabajo;
    }

    public function setTributaplantrabajo(bool $tributaplantrabajo): self
    {
        $this->tributaplantrabajo = $tributaplantrabajo;

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
}
