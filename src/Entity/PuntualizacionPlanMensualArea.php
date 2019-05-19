<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 */
class PuntualizacionPlanMensualArea
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechacreacion;

    /**
     * @ORM\Column(type="text")
     */
    private $descripcion;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $usuario;

    /**
     * @var \PlanMensualArea
     *
     * @ORM\ManyToOne(targetEntity="PlanMensualArea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plantrabajo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $plantrabajo;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max=250)
     */
    private $actividad;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min=0, max=2)
     */
    private $tipo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechacreacion(): ?\DateTimeInterface
    {
        return $this->fechacreacion;
    }

    public function setFechacreacion(\DateTimeInterface $fechacreacion): self
    {
        $this->fechacreacion = $fechacreacion;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getPlantrabajo(): ?PlanMensualArea
    {
        return $this->plantrabajo;
    }

    public function setPlantrabajo(?PlanMensualArea $plantrabajo): self
    {
        $this->plantrabajo = $plantrabajo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActividad()
    {
        return $this->actividad;
    }

    /**
     * @param mixed $actividad
     */
    public function setActividad($actividad): void
    {
        $this->actividad = $actividad;
    }

    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getTipoString()
    {
        $array = ['Registrada', 'Actualizada', 'Eliminada'];
        return $array[$this->tipo];
    }

    /**
     * @Assert\Callback
     */
    public function validar(ExecutionContextInterface $context)
    {

        if (null == $this->getPlantrabajo()) {
            $context->setNode($context, 'plantrabajo', null, 'data.plantrabajo');
            $context->addViolation('Seleccione el plan de trabajo.');
        } else
            if ($this->getFechacreacion()->format('Y') != $this->getPlantrabajo()->getAnno() || $this->getFechacreacion()->format('m') != $this->getPlantrabajo()->getMes()) {
                $context->setNode($context, 'fechacreacion', null, 'data.fechacreacion');
                $context->addViolation('La fecha de creación debe pertenecer al mes del plan.');
            }
        if (null == $this->getUsuario()) {
            $context->setNode($context, 'usuario', null, 'data.usuario');
            $context->addViolation('Seleccione el usuario.');
        }
    }
}
