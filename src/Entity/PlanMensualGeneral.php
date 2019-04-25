<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * PlanMensualGeneral
 *
 * @ORM\Table(name="planmensualgeneral")
 * @ORM\Entity
 * @UniqueEntity(fields={"mes","anno"})
 */
class PlanMensualGeneral
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="planmensualgeneral_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mes", type="integer", nullable=false)
     * @Assert\Range(
     *      min = 1,
     *      max = 12,
     * )
     */
    private $mes;

    /**
     * @var int|null
     *
     * @ORM\Column(name="anno", type="integer", nullable=false)
     */
    private $anno;

    /**
     * @ORM\Column(type="date")
     */
    private $edicionfechainicio;

    /**
     * @ORM\Column(type="date")
     */
    private $edicionfechafin;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return int|null
     */
    public function getMes(): ?int
    {
        return $this->mes;
    }

    public function getMesToString(): ?string
    {
        if ($this->mes != null) {
            $array = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $mes = $this->mes;
            return $array[--$mes];
        }
        return null;
    }

    /**
     * @param int|null $mes
     */
    public function setMes(?int $mes): void
    {
        $this->mes = $mes;
    }

    /**
     * @return int|null
     */
    public function getAnno(): ?int
    {
        return $this->anno;
    }

    /**
     * @param int|null $anno
     */
    public function setAnno(?int $anno): void
    {
        $this->anno = $anno;
    }

    public function __toString(): string
    {
        return (String)$this->getId();
    }

    public function getEdicionfechainicio(): ?\DateTimeInterface
    {
        return $this->edicionfechainicio;
    }

    public function setEdicionfechainicio(\DateTimeInterface $edicionfechainicio): self
    {
        $this->edicionfechainicio = $edicionfechainicio;

        return $this;
    }

    public function getEdicionfechafin(): ?\DateTimeInterface
    {
        return $this->edicionfechafin;
    }

    public function setEdicionfechafin(\DateTimeInterface $edicionfechafin): self
    {
        $this->edicionfechafin = $edicionfechafin;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validar(ExecutionContextInterface $context)
    {
        if ($this->getEdicionfechafin() < $this->getEdicionfechainicio()) {
            $context->setNode($context, 'edicionfechafin', null, 'data.edicionfechafin');
            $context->addViolation('Seleccione la fecha de fin mayor o igual que la fecha de inicio');
        }
    }
}
