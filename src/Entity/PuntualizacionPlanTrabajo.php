<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 */
class PuntualizacionPlanTrabajo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $descripcion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechacreacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Plantrabajo", inversedBy="puntualizacionPlanTrabajos")
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
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

    public function getPlantrabajo(): ?Plantrabajo
    {
        return $this->plantrabajo;
    }

    public function setPlantrabajo(?Plantrabajo $plantrabajo): self
    {
        $this->plantrabajo = $plantrabajo;

        return $this;
    }

    public function getActividad(): ?string
    {
        return $this->actividad;
    }

    public function setActividad(string $actividad): self
    {
        $this->actividad = $actividad;

        return $this;
    }

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getTipoString()
    {
        $array=['Registrada','Actualizada','Eliminada'];
        return $array[$this->tipo];
    }

    /**
     * @Assert\Callback
     */
    public function validar(ExecutionContextInterface $context)
    {

        if (null==$this->getPlantrabajo()) {
            $context->setNode($context, 'plantrabajo', null, 'data.plantrabajo');
            $context->addViolation('Seleccione el plan de trabajo.');
        }else
            if ($this->getFechacreacion()->format('Y') != $this->getPlantrabajo()->getAnno() || $this->getFechacreacion()->format('m') != $this->getPlantrabajo()->getMes()) {
                $context->setNode($context, 'fechacreacion', null, 'data.fechacreacion');
                $context->addViolation('La fecha de creación debe pertenecer al mes del plan.');
            }
    }
}
