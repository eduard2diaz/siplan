<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * PlanMensualArea
 *
 * @ORM\Table(name="planmensualarea")
 * @ORM\Entity
 * @UniqueEntity(fields={"mes","anno","area"})
 */
class PlanMensualArea
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="planmensualarea_id_seq", allocationSize=1, initialValue=1)
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
     * @var \Area
     *
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="area", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $area;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gestionadopor", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $gestor;

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

    /**
     * @return \Area
     */
    public function getArea(): ?Area
    {
        return $this->area;
    }

    /**
     * @param \Area $area
     */
    public function setArea(\App\Entity\Area $area): void
    {
        $this->area = $area;
    }

    /**
     * @return \Usuario
     */
    public function getGestor(): ?Usuario
    {
        return $this->gestor;
    }

    /**
     * @param \Usuario $gestor
     */
    public function setGestor(\App\Entity\Usuario $gestor): void
    {
        $this->gestor = $gestor;
    }




    public function __toString(): string
    {
        return (String)$this->getId();
    }

    /**
     * @Assert\Callback
     */
    public function validar(ExecutionContextInterface $context)
    {
        if (null===$this->getArea()) {
            $context->setNode($context, 'area', null, 'data.area');
            $context->addViolation('Seleccione el área');
        }
        if (null===$this->getGestor()) {
            $context->setNode($context, 'gestor', null, 'data.gestor');
            $context->addViolation('Seleccione el gestor');
        }
    }
}
