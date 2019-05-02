<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PlanMensualGeneral;
use App\Entity\Usuario;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\PeriodActividadGeneral as PeriodConstraint;
use App\Entity\Capitulo;
use App\Entity\Subcapitulo;

/**
 * ActividadGeneral
 *
 * @ORM\Table(name="actividadgeneral", indexes={@ORM\Index(name="IDX_8DF2BD06212B0BD47FB", columns={"planmensualgeneral"})})
 * @ORM\Entity
 * @PeriodConstraint(from="fecha",to="fechaF",place="lugar")
 */
class ActividadGeneral
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
     * @ORM\Column(name="nombre", type="string", nullable=false)
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lugar", type="string", nullable=false)
     */
    private $lugar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dirigen", type="string", nullable=false)
     */
    private $dirigen;

    /**
     * @var string|null
     *
     * @ORM\Column(name="participan", type="text", nullable=false)
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
     * @var \PlanMensualGeneral
     *
     * @ORM\ManyToOne(targetEntity="PlanMensualGeneral")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="planmensualgeneral", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $planmensualgeneral;

    /**
     * @var \Capitulo
     *
     * @ORM\ManyToOne(targetEntity="Capitulo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="capitulo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $capitulo;

    /**
     * @var \Subcapitulo
     *
     * @ORM\ManyToOne(targetEntity="Subcapitulo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subcapitulo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $subcapitulo;

    /**
     * @var \Capitulo
     *
     * @ORM\ManyToOne(targetEntity="ARC")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="areaconocimiento", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $areaconocimiento;

    /**
     * @var \Subcapitulo
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
     * @return \PlanMensualGeneral
     */
    public function getPlanMensualGeneral(): ?\App\Entity\PlanMensualGeneral
    {
        return $this->planmensualgeneral;
    }

    /**
     * @param \PlanMensualGeneral $planmensualgeneral
     */
    public function setPlanMensualGeneral(PlanMensualGeneral $planmensualgeneral): void
    {
        $this->planmensualgeneral = $planmensualgeneral;
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

    /**
     * @return \Capitulo
     */
    public function getCapitulo(): ?Capitulo
    {
        return $this->capitulo;
    }

    /**
     * @param \Capitulo $capitulo
     */
    public function setCapitulo(Capitulo $capitulo): void
    {
        $this->capitulo = $capitulo;
    }

    /**
     * @return \Subcapitulo
     */
    public function getSubcapitulo(): ?Subcapitulo
    {
        return $this->subcapitulo;
    }

    /**
     * @param \Subcapitulo $subcapitulo
     */
    public function setSubcapitulo(Subcapitulo $subcapitulo): void
    {
        $this->subcapitulo = $subcapitulo;
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

        if(null==$this->getPlanMensualGeneral()){
            $context->setNode($context, 'planmensualgeneral', null, 'data.planmensualgeneral');
            $context->addViolation('Seleccione un plan mensual');
        }elseif($this->getFecha()->format('Y')!=$this->getPlanMensualGeneral()->getAnno() || $this->getFecha()->format('m')!=$this->getPlanMensualGeneral()->getMes())
        {
            $context->setNode($context, 'fecha', null, 'data.fecha');
            $context->addViolation('La fecha debe pertenecer al mes del plan.');
        }
        elseif($this->getFechaF()->format('Y')!=$this->getPlanMensualGeneral()->getAnno() || $this->getFechaF()->format('m')!=$this->getPlanMensualGeneral()->getMes())
        {
            $context->setNode($context, 'fechaf', null, 'data.fechaf');
            $context->addViolation('La fecha debe pertenecer al mes del plan.');
        }

        if(null==$this->getUsuario()){
            $context->setNode($context, 'usuario', null, 'data.usuario');
            $context->addViolation('Seleccione un usuario');
        }

        if(null==$this->getCapitulo()){
            $context->setNode($context, 'capitulo', null, 'data.capitulo');
            $context->addViolation('Seleccione un capítulo');
        }
        elseif(null==$this->getSubcapitulo()){
            $context->setNode($context, 'subcapitulo', null, 'data.subcapitulo');
            $context->addViolation('Seleccione un subcapítulo');
        }




    }
}
