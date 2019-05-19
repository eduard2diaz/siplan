<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Subcapitulo
 *
 * @ORM\Table(name="subcapitulo")
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre","capitulo"})
 * @UniqueEntity(fields={"numero","capitulo"})
 */
class Subcapitulo
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
     * @ORM\Column(name="nombre", type="string", length=250)
     * @Assert\Length(max=250)
     */
    private $nombre;

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
     * @ORM\Column(type="integer")
     * @Assert\Range(min=1)
     */
    private $numero;

    /**
     * @return int
     */
    public function getId(): ? int
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

    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {

        if (null == $this->getCapitulo())
            $context->buildViolation('Seleccione el capítulo')
                ->atPath('capitulo')
                ->addViolation();
    }
}
