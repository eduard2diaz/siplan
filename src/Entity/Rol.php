<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
/**
 * Rol
 *
 * @ORM\Table(name="rol")
 * @ORM\Entity
 */
class Rol extends Role
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="rol_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    private $nombre;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Usuario", mappedBy="idrol")
     */
    private $idusuario;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idusuario = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
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

    public function __toString()
    {
        switch($this->getNombre()){
            case 'ROLE_ADMIN':
               return 'Administrador';
            break;
            case 'ROLE_DIRECTIVO':
                return 'Directivo';
            break;
            case 'ROLE_USER':
                return 'Trabajador';
            break;
            case 'ROLE_COORDINADOR':
                return 'Coordinador';
            break;
        }
        return $this->getNombre();
    }


}
