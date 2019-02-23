<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Usuario;

/**
 * Plantrabajo
 *
 * @ORM\Table(name="plantrabajo", indexes={@ORM\Index(name="IDX_B0BD47FB2265B05D", columns={"usuario"})})
 * @ORM\Entity
 * @UniqueEntity(fields={"mes","anno","usuario"})
 */
class Plantrabajo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="plantrabajo_id_seq", allocationSize=1, initialValue=1)
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
     * @return \Usuario
     */
    public function getUsuario(): ?\App\Entity\Usuario
    {
        return $this->usuario;
    }

    /**
     * @param \Usuario $usuario
     */
    public function setUsuario(Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }

    public function __toString(): string
    {
        return (String)$this->getId();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (null == $this->getUsuario())
            $context->buildViolation('Seleccione un usuario')
                ->atPath('padre')
                ->addViolation();
    }

}
