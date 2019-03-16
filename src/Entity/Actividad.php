<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Plantrabajo;
use App\Entity\Usuario;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\Period as PeriodConstraint;

/**
 * Actividad
 *
 * @ORM\Table(name="actividad", indexes={@ORM\Index(name="IDX_8DF2BD0652520D07", columns={"responsable"}), @ORM\Index(name="IDX_8DF2BD0632DBFD56", columns={"asignadapor"}), @ORM\Index(name="IDX_8DF2BD06B0BD47FB", columns={"plantrabajo"})})
 * @ORM\Entity
 * @PeriodConstraint(from="fecha",to="fechaF",foreign="plantrabajo")
 */
class Actividad
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
     * @ORM\Column(name="lugar", type="string", nullable=true)
     */
    private $lugar;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dirigen", type="string", nullable=true)
     */
    private $dirigen;

    /**
     * @var string|null
     *
     * @ORM\Column(name="participan", type="text", nullable=true)
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
     * @var int|null
     *
     * @ORM\Column(name="estado", type="integer", nullable=false)
     */
    private $estado;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $responsable;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="asignadapor", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $asignadapor;

    /**
     * @var \Plantrabajo
     *
     * @ORM\ManyToOne(targetEntity="Plantrabajo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plantrabajo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $plantrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ARC")
     */
    private $areaconocimiento;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Fichero", mappedBy="actividad", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $ficheros;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ActividadGeneral")
     */
    private $actividadGeneral;

    /**
     * Actividad constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->estado = 1;
        $this->ficheros= new ArrayCollection();
    }


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
     * @return int|null
     */
    public function getEstado(): ?int
    {
        return $this->estado;
    }

    /**
     * @return string|null
     */
    public function getEstadoString(): ?string
    {
        if ($this->estado != null) {
            $array = ['Registrada', 'En proceso', 'Culminada', 'Cumplida', 'Incumplida'];
            $estado = $this->estado;
            return $array[--$estado];
        }
        return null;
    }


    public function getEstadoColor(): ?string
    {
        if ($this->estado != null) {
            $array = ['default', 'info', 'success', 'warning', 'danger'];
            $estado = $this->estado;
            return $array[--$estado];
        }
        return null;
    }


    /**
     * @param int|null $estado
     */
    public function setEstado(?int $estado): void
    {
        $this->estado = $estado;
    }

    /**
     * @return \Usuario
     */
    public function getResponsable(): ?\App\Entity\Usuario
    {
        return $this->responsable;
    }

    /**
     * @param \Usuario $responsable
     */
    public function setResponsable(Usuario $responsable): void
    {
        $this->responsable = $responsable;
    }

    /**
     * @return \Usuario
     */
    public function getAsignadapor(): ?\App\Entity\Usuario
    {
        return $this->asignadapor;
    }

    /**
     * @param \Usuario $asignadapor
     */
    public function setAsignadapor(Usuario $asignadapor): void
    {
        $this->asignadapor = $asignadapor;
    }

    /**
     * @return \Plantrabajo
     */
    public function getPlantrabajo(): ?\App\Entity\Plantrabajo
    {
        return $this->plantrabajo;
    }

    /**
     * @param \Plantrabajo $plantrabajo
     */
    public function setPlantrabajo(Plantrabajo $plantrabajo): void
    {
        $this->plantrabajo = $plantrabajo;
    }

    /**
     * @return Collection|Fichero[]
     */
    public function getFicheros(): Collection
    {
        return $this->ficheros;
    }

    public function addFichero(Fichero $fichero): self
    {
        if (!$this->ficheros->contains($fichero)) {
            $this->ficheros[] = $fichero;
            $fichero->setActividad($this);
        }

        return $this;
    }

    public function removeFichero(Fichero $fichero): self
    {
        if ($this->ficheros->contains($fichero)) {
            $this->ficheros->removeElement($fichero);
        }

        return $this;
    }

    public function setFichero($ficheros)
    {
        $this->ficheros = $ficheros;
        foreach ($ficheros as $address) {
            $address->setActividad($this);
        }
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

    public function getActividadGeneral(): ?ActividadGeneral
    {
        return $this->actividadGeneral;
    }

    public function setActividadGeneral(?ActividadGeneral $actividadGeneral): self
    {
        $this->actividadGeneral = $actividadGeneral;

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

        if($this->getFecha()->format('Y')!=$this->getPlantrabajo()->getAnno() || $this->getFecha()->format('m')!=$this->getPlantrabajo()->getMes())
        {
            $context->setNode($context, 'fecha', null, 'data.fecha');
            $context->addViolation('La fecha debe pertenecer al mes del plan.');
        }

        if($this->getFechaF()->format('Y')!=$this->getPlantrabajo()->getAnno() || $this->getFechaF()->format('m')!=$this->getPlantrabajo()->getMes())
        {
            $context->setNode($context, 'fechaf', null, 'data.fechaf');
            $context->addViolation('La fecha debe pertenecer al mes del plan.');
        }

    }

}
