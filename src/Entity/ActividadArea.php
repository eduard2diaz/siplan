<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PlanMensualArea;
use App\Entity\Usuario;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\PeriodActividadArea as PeriodConstraint;

/**
 * ActividadArea
 *
 * @ORM\Table(name="actividadarea", indexes={@ORM\Index(name="IDX_8DF2BD06212B0BD47F1121B", columns={"planmensualarea"})})
 * @ORM\Entity
 */
class ActividadArea
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="actividad_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fechaF", type="datetime", nullable=false)
     */
    private $fechaF;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string",length=250, nullable=false)
     * @Assert\Length(max=250)
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lugar", type="string",length=250, nullable=false)
     * @Assert\Length(max=250)
     */
    private $lugar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dirigen", type="string",length=250, nullable=false)
     * @Assert\Length(max=250)
     */
    private $dirigen;

    /**
     * @var string|null
     *
     * @ORM\Column(name="participan", type="text",length=250, nullable=false)
     * @Assert\Length(max=250)
     */
    private $participan;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="aseguramiento", type="text", nullable=true)
     */
    private $aseguramiento;

    /**
     * @var \PlanMensualArea
     *
     * @ORM\ManyToOne(targetEntity="PlanMensualArea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="planmensualarea", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $planmensualarea;

    /**
     * @var \ARC
     *
     * @ORM\ManyToOne(targetEntity="ARC")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="areaconocimiento", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $areaconocimiento;

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
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTime|null
     */
    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    /**
     * @param \DateTime|null $fecha
     */
    public function setFecha(?\DateTime $fecha): void
    {
        $this->fecha = $fecha;
    }

    /**
     * @return \DateTime|null
     */
    public function getFechaF(): ?\DateTime
    {
        return $this->fechaF;
    }

    /**
     * @param \DateTime|null $fechaF
     */
    public function setFechaF(?\DateTime $fechaF): void
    {
        $this->fechaF = $fechaF;
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
     * @return \PlanMensualArea
     */
    public function getPlanMensualArea(): ?\App\Entity\PlanMensualArea
    {
        return $this->planmensualarea;
    }

    /**
     * @param \PlanMensualArea $planmensualarea
     */
    public function setPlanMensualArea(PlanMensualArea $planmensualarea): void
    {
        $this->planmensualarea = $planmensualarea;
    }

    /**
     * @return null|string
     */
    public function getLugar(): ?string
    {
        return $this->lugar;
    }

    /**
     * @param null|string $lugar
     */
    public function setLugar(?string $lugar): void
    {
        $this->lugar = $lugar;
    }

    /**
     * @return null|string
     */
    public function getDirigen(): ?string
    {
        return $this->dirigen;
    }

    /**
     * @param null|string $dirigen
     */
    public function setDirigen(?string $dirigen): void
    {
        $this->dirigen = $dirigen;
    }

    /**
     * @return null|string
     */
    public function getParticipan(): ?string
    {
        return $this->participan;
    }

    /**
     * @param null|string $participan
     */
    public function setParticipan(?string $participan): void
    {
        $this->participan = $participan;
    }

    /**
     * @return null|string
     */
    public function getAseguramiento(): ?string
    {
        return $this->aseguramiento;
    }

    /**
     * @param null|string $aseguramiento
     */
    public function setAseguramiento(?string $aseguramiento): void
    {
        $this->aseguramiento = $aseguramiento;
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

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function comprobarFechas(ExecutionContextInterface $context)
    {

        if ($this->getFechaF() < $this->getFecha()) {
            $context->setNode($context, 'fechaf', null, 'data.fechaf');
            $context->addViolation('La fecha de fin debe ser mayor o igual que la fecha de inicio.');
        }

        if(null==$this->getPlanMensualArea()){
            $context->setNode($context, 'planmensualarea', null, 'data.planmensualarea');
            $context->addViolation('Seleccione un plan mensual');
        }elseif($this->getFecha()->format('Y')!=$this->getPlanMensualArea()->getAnno() || $this->getFecha()->format('m')!=$this->getPlanMensualArea()->getMes())
        {
            $context->setNode($context, 'fecha', null, 'data.fecha');
            $context->addViolation('La fecha debe pertenecer al mes del plan.');
        }
        elseif($this->getFechaF()->format('Y')!=$this->getPlanMensualArea()->getAnno() || $this->getFechaF()->format('m')!=$this->getPlanMensualArea()->getMes())
        {
            $context->setNode($context, 'fechaf', null, 'data.fechaf');
            $context->addViolation('La fecha debe pertenecer al mes del plan.');
        }

        if(null==$this->getUsuario()){
            $context->setNode($context, 'usuario', null, 'data.usuario');
            $context->addViolation('Seleccione un usuario');
        }
    }
}
