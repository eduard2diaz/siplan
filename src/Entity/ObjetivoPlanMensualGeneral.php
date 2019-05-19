<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"numero","plantrabajo"})
 * @UniqueEntity(fields={"descripcion","plantrabajo"})
 */
class ObjetivoPlanMensualGeneral
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
     * @var \PlanMensualGeneral
     *
     * @ORM\ManyToOne(targetEntity="PlanMensualGeneral", inversedBy="puntualizacionPlanMensualGenerals")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plantrabajo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $plantrabajo;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min=1)
     */
    private $numero;

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

    public function getPlantrabajo(): ?PlanMensualGeneral
    {
        return $this->plantrabajo;
    }

    public function setPlantrabajo(?PlanMensualGeneral $plantrabajo): self
    {
        $this->plantrabajo = $plantrabajo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero): void
    {
        $this->numero = $numero;
    }

    /**
     * @Assert\Callback
     */
    public function validar(ExecutionContextInterface $context)
    {

        if (null == $this->getPlantrabajo()) {
            $context->setNode($context, 'plantrabajo', null, 'data.plantrabajo');
            $context->addViolation('Seleccione el plan de trabajo.');
        }
    }
}
