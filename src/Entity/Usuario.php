<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario", indexes={@ORM\Index(name="IDX_2265B05D35BC9846", columns={"jefe"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 * @UniqueEntity("usuario")
 * @UniqueEntity("correo")
 */
class Usuario implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="usuario_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string")
     * @Assert\Regex("/^[A-Za-záéíóúñ]{2,}([\s][A-Za-záéíóúñ]{2,})*$/")
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="correo", type="string", nullable=true,unique=false)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     strict = true
     * )
     */
    private $correo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="usuario", type="string", nullable=true,unique=true)
     * @Assert\Regex("/^([a-zA-Z]((\.|_|-)?[a-zA-Z0-9]+){3})*$/")
     */
    private $usuario;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="string", nullable=true)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="salt", type="string", nullable=true)
     */
    private $salt;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jefe", referencedColumnName="id",onDelete="Cascade", nullable=true)
     * })
     */
    private $jefe;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Rol", inversedBy="idusuario")
     * @ORM\JoinTable(name="usuario_rol",
     *   joinColumns={
     *     @ORM\JoinColumn(name="idusuario", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="idrol", referencedColumnName="id")
     *   }
     * )
     */
    private $idrol;

    /**
     * @var \Area
     *
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="area", referencedColumnName="id",onDelete="Cascade", nullable=false)
     * })
     */
    private $area;

    /**
     * @var \Cargo
     *
     * @ORM\ManyToOne(targetEntity="Cargo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cargo", referencedColumnName="id",onDelete="Cascade", nullable=false)
     * })
     */
    private $cargo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Grupo", mappedBy="creador")
     */
    private $grupos;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Grupo", mappedBy="idmiembro")
     */
    private $grupospertenece;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SolicitudGrupo", mappedBy="usuario")
     */
    private $solicitudGrupos;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Mensaje", mappedBy="iddestinatario")
     */
    private $idmensaje;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ultimologin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ultimologout;

    /**
     * @Assert\Image()
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rutaFoto;

     /**
     * Constructor
     */
    public function __construct()
    {
        $this->idrol = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setSalt(md5(time()));
        $this->setActivo(true);
        $this->grupos = new ArrayCollection();
        $this->grupospertenece = new ArrayCollection();
        $this->solicitudGrupos = new ArrayCollection();
        $this->idmensaje = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    /**
     * @param null|string $correo
     */
    public function setCorreo(?string $correo): void
    {
        $this->correo = $correo;
    }

    /**
     * @return null|string
     */
    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    /**
     * @param null|string $usuario
     */
    public function setUsuario(?string $usuario): void
    {
        $this->usuario = $usuario;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param null|string $salt
     */
    public function setSalt(?string $salt): void
    {
        $this->salt = $salt;
    }

    /**
     * @return bool|null
     */
    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    /**
     * @param bool|null $activo
     */
    public function setActivo(?bool $activo): void
    {
        $this->activo = $activo;
    }

    /**
     * @return \Usuario
     */
    public function getJefe(): ?Usuario
    {
        return $this->jefe;
    }

    /**
     * @param \Usuario $jefe
     */
    public function setJefe(Usuario $jefe=null): void
    {
        $this->jefe = $jefe;
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
    public function setArea(Area $area): void
    {
        $this->area = $area;
    }

    /**
     * @return \Cargo
     */
    public function getCargo(): ?Cargo
    {
        return $this->cargo;
    }

    /**
     * @param \Cargo $cargo
     */
    public function setCargo(Cargo $cargo): void
    {
        $this->cargo = $cargo;
    }

    /**
     * @return mixed
     */
    public function getRutaFoto()
    {
        return $this->rutaFoto;
    }

    /**
     * @param mixed $rutaFoto
     */
    public function setRutaFoto($rutaFoto): void
    {
        $this->rutaFoto = $rutaFoto;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdrol(): \Doctrine\Common\Collections\Collection
    {
        return $this->idrol;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $idrol
     */
    public function setIdrol(\Doctrine\Common\Collections\Collection $idrol): void
    {
        $this->idrol = $idrol;
    }

    public function getRoles()
    {
        $array=array();
        foreach ($this->getIdrol()->toArray() as $value)
            $array[]=$value->getNombre();
        return $array;
    }

    public function getUsername()
    {
       return $this->getUsuario();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function __toString()
    {
     return $this->getNombre();
    }

    /**
     * @return Collection|Grupo[]
     */
    public function getGrupos(): Collection
    {
        return $this->grupos;
    }

    public function addGrupo(Grupo $grupo): self
    {
        if (!$this->grupos->contains($grupo)) {
            $this->grupos[] = $grupo;
            $grupo->setCreador($this);
        }

        return $this;
    }

    public function removeGrupo(Grupo $grupo): self
    {
        if ($this->grupos->contains($grupo)) {
            $this->grupos->removeElement($grupo);
            // set the owning side to null (unless already changed)
            if ($grupo->getCreador() === $this) {
                $grupo->setCreador(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Grupo[]
     */
    public function getGrupospertenece(): Collection
    {
        return $this->grupospertenece;
    }

    public function addGrupospertenece(Grupo $grupospertenece): self
    {
        if (!$this->grupospertenece->contains($grupospertenece)) {
            $this->grupospertenece[] = $grupospertenece;
            $grupospertenece->addIdmiembro($this);
        }

        return $this;
    }

    public function removeGrupospertenece(Grupo $grupospertenece): self
    {
        if ($this->grupospertenece->contains($grupospertenece)) {
            $this->grupospertenece->removeElement($grupospertenece);
            $grupospertenece->removeIdmiembro($this);
        }

        return $this;
    }

    /**
     * @return Collection|SolicitudGrupo[]
     */
    public function getSolicitudGrupos(): Collection
    {
        return $this->solicitudGrupos;
    }

    public function addSolicitudGrupo(SolicitudGrupo $solicitudGrupo): self
    {
        if (!$this->solicitudGrupos->contains($solicitudGrupo)) {
            $this->solicitudGrupos[] = $solicitudGrupo;
            $solicitudGrupo->setUsuario($this);
        }

        return $this;
    }

    public function removeSolicitudGrupo(SolicitudGrupo $solicitudGrupo): self
    {
        if ($this->solicitudGrupos->contains($solicitudGrupo)) {
            $this->solicitudGrupos->removeElement($solicitudGrupo);
            // set the owning side to null (unless already changed)
            if ($solicitudGrupo->getUsuario() === $this) {
                $solicitudGrupo->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * Add idmensaje
     *
     * @param \App\Entity\Mensaje $idmensaje
     *
     * @return Usuario
     */
    public function addIdmensaje(\App\Entity\Mensaje $idmensaje)
    {
        $this->idmensaje[] = $idmensaje;

        return $this;
    }

    /**
     * Remove idmensaje
     *
     * @param \App\Entity\Mensaje $idmensaje
     */
    public function removeIdmensaje(\App\Entity\Mensaje $idmensaje)
    {
        $this->idmensaje->removeElement($idmensaje);
    }

    /**
     * Get idmensaje
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdmensaje()
    {
        return $this->idmensaje;
    }

    public function getEstadoColor(){
        return $this->getActivo() ? 'success' : 'danger';
    }

    public function getEstadoIcono(){
        return $this->getActivo() ? 'play' : 'close';
    }

    public function getUltimologin(): ?\DateTimeInterface
    {
        return $this->ultimologin;
    }

    public function setUltimologin(?\DateTimeInterface $ultimologin): self
    {
        $this->ultimologin = $ultimologin;

        return $this;
    }

    public function getUltimologout(): ?\DateTimeInterface
    {
        return $this->ultimologout;
    }

    public function setUltimologout(?\DateTimeInterface $ultimologout): self
    {
        $this->ultimologout = $ultimologout;

        return $this;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile($file) {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    public function Upload($ruta) {
        if (null === $this->file) {
            return;
        }
        $fs = new Filesystem();
        $camino = $fs->makePathRelative($ruta, __DIR__);
        $directorioDestino = __DIR__ . DIRECTORY_SEPARATOR . $camino;
        $nombreArchivoFoto = uniqid('siplan-') . '-' . $this->file->getClientOriginalName();
        $this->file->move($directorioDestino.DIRECTORY_SEPARATOR, $nombreArchivoFoto);
        $this->setRutaFoto($nombreArchivoFoto);
    }

    public function actualizarFoto($directorioDestino) {

        if (null !== $this->getFile()) {
            $this->removeUpload($directorioDestino);
            $this->Upload($directorioDestino);
        }
    }

    public function removeUpload($directorioDestino) {
        $fs=new Filesystem();
        $rutaPc = $directorioDestino.DIRECTORY_SEPARATOR.$this->getRutaFoto();
        if (null!=$this->getRutaFoto()  && $fs->exists($rutaPc)) {
            $fs->remove($rutaPc);
        }
    }

     public function cicloInfinito($current, Usuario $usuario)
    {
        if ($usuario->getJefe() != null) {
            if ($usuario->getJefe()->getId() == $current)
                return true;
            else
                return $this->cicloInfinito($current, $usuario->getJefe());
        }
        return false;
    }

    /*
     *Funcionalidad que recibe un usuario como parametro y dice si ese usuario
     * es superior del actual usuario.
     */
    public function esSubordinado(Usuario $usuario):bool {
        if($this->getJefe()==$usuario)
            return true;
        if(null!=$this->getJefe())
            return $this->getJefe()->esSubordinado($usuario);

        return false;
    }

    /**
     * @Assert\Callback
     */
    public function comprobarCargo(ExecutionContextInterface $context)
    {
        $roles=$this->getRoles();
        if (null==$this->getArea()) {
            $context->setNode($context, 'area', null, 'data.area');
            $context->addViolation('Seleccione un área');
        }
        if (null==$this->getCargo()) {
            $context->setNode($context, 'cargo', null, 'data.cargo');
            $context->addViolation('Seleccione un cargo');
        }elseif ($this->getCargo()->getArea()->getId() != $this->getArea()->getId()) {
            $context->setNode($context, 'cargo', null, 'data.cargo');
            $context->addViolation('El cargo indicado no pertenece al área.');
        }

        if(true==$this->cicloInfinito($this->getId(),$this)){
            $context->setNode($context, 'nombre', null, 'data.nombre');
            $context->addViolation('Compruebe el jefe seleccionado.');
        }

         if(in_array('ROLE_ADMIN',$roles)) {
            if ($this->getJefe() != null)
                $context->buildViolation('Un administrador no puede tener jefe')
                    ->atPath('idrol')
                    ->addViolation();
        }elseif(in_array('ROLE_USER',$roles)) {
            if ($this->getJefe() == null)
                $context->buildViolation('Seleccione el jefe')
                    ->atPath('idrol')
                    ->addViolation();
        }
    }
}
