<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use function Sodium\randombytes_buf;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * Usuario
 *
 * @ORM\Table(name="usuario", indexes={@ORM\Index(name="IDX_2265B05D35BC9846", columns={"jefe"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 * @UniqueEntity("usuario")
 * @UniqueEntity("correo")
 */
class Usuario implements AdvancedUserInterface
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
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="correo", type="string", nullable=true,unique=true)
     */
    private $correo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="usuario", type="string", nullable=true,unique=true)
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
     * Constructor
     */
    public function __construct()
    {
        $this->idrol = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setSalt(md5(time()));
        $this->setActivo(true);
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

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
       return $this->activo;
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

    public function getEstadoColor(){
        return $this->getActivo() ? 'success' : 'danger';
    }

    public function getEstadoIcono(){
        return $this->getActivo() ? 'play' : 'close';
    }

    /*
     *Funcionalidad que recibe un usuario como parametro y dice si ese usuario
     * es superior del actual usuario.
     */
    public function esJefe(Usuario $usuario){
        if($this->getJefe()==$usuario)
            return true;
        if(null!=$this->getJefe())
            return $this->getJefe()->esJefe($usuario);

        return false;
    }

    /**
     * @Assert\Callback
     */
    public function comprobarCargo(ExecutionContextInterface $context)
    {
        if ($this->getCargo()->getArea()->getId() != $this->getArea()->getId()) {
            $context->setNode($context, 'cargo', null, 'data.cargo');
            $context->addViolation('El cargo indicado no pertenece al área.');
        }
    }

}
