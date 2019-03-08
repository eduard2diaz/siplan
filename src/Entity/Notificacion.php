<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuario;

/**
 * @ORM\Entity
 */
class Notificacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="destinatario", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $destinatario;

    /**
     * @var \Grupo
     *
     * @ORM\ManyToOne(targetEntity="Grupo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grupo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $grupo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="text", nullable=false)
     */
    private $descripcion;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Usuario
     */
    public function getDestinatario(): Usuario
    {
        return $this->destinatario;
    }

    /**
     * @param \Usuario $destinatario
     */
    public function setDestinatario(Usuario $destinatario): void
    {
        $this->destinatario = $destinatario;
    }

    /**
     * @return \Grupo
     */
    public function getGrupo(): Grupo
    {
        return $this->grupo;
    }

    /**
     * @param \Grupo $grupo
     */
    public function setGrupo(Grupo $grupo): void
    {
        $this->grupo = $grupo;
    }



    /**
     * @return mixed
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param mixed $fecha
     */
    public function setFecha($fecha): void
    {
        $this->fecha = $fecha;
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
}
