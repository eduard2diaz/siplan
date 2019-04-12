<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Capitulo;
use App\Entity\Subcapitulo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"nombre"})
 */
class ARC
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
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
     * @var \Subcapitulo
     *
     * @ORM\ManyToOne(targetEntity="Subcapitulo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subcapitulo", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $subcapitulo;

    /**
     * @ORM\Column(type="text")
     */
    private $objetivos;


    public function getId()
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getObjetivos(): ?string
    {
        return $this->objetivos;
    }

    public function setObjetivos(string $objetivos): self
    {
        $this->objetivos = $objetivos;

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
            $context->buildViolation('Seleccione un capítulo')
                ->atPath('capitulo')
                ->addViolation();
        elseif (null == $this->getSubcapitulo())
            $context->buildViolation('Seleccione un subcapítulo')
                ->atPath('subcapitulo')
                ->addViolation();
    }

}
