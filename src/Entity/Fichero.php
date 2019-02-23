<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FicheroRepository")
 */
class Fichero
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
     * @ORM\Column(type="string", length=255)
     */
    private $ruta;

    /**
     * @Assert\File(
     * maxSize="10240M",
     * notReadableMessage = "No se puede leer el archivo",
     * uploadIniSizeErrorMessage = "El archivo es demasiado grande. El tamaño máximo permitido es 10Gb",
     * uploadFormSizeErrorMessage = "El archivo es demasiado grande. El tamaño máximo permitido es 10Gb",
     * uploadErrorMessage = "No se puede subir el archivo")
     */
    protected $file;

    /**
     * @var \Actividad
     *
     * @ORM\ManyToOne(targetEntity="Actividad", inversedBy="fichero")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actividad", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $actividad;

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

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): self
    {
        $this->ruta = $ruta;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActividad()
    {
        return $this->actividad;
    }

    /**
     * @param mixed $actividad
     */
    public function setActividad($actividad): void
    {
        $this->actividad = $actividad;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null) {
        $this->file = $file;
    }

    /**
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    //Funcionalidad destinada a la subida de archivos al servidor
    public function subirArchivo($ruta) {

        if (null === $this->file) {
            return;
        }
        $fs = new Filesystem();
        $camino = $fs->makePathRelative($ruta, __DIR__);

        $directorioDestino = __DIR__ . DIRECTORY_SEPARATOR . $camino;
        $nombreArchivoFoto = uniqid('siplan-') . '-' . $this->file->getClientOriginalName();
        $this->file->move($directorioDestino.DIRECTORY_SEPARATOR, $nombreArchivoFoto);
        return $nombreArchivoFoto;
    }

}
