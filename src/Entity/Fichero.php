<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=250)
     * @Assert\Length(max=250)
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

    /**
     * @var \Respuesta
     *
     * @ORM\ManyToOne(targetEntity="Respuesta", inversedBy="fichero")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="respuesta", referencedColumnName="id",onDelete="Cascade")
     * })
     */
    private $respuesta;

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
    public function setActividad($actividad=null): void
    {
        $this->actividad = $actividad;
    }

    /**
     * @return mixed
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * @param mixed $respuesta
     */
    public function setRespuesta($respuesta=null): void
    {
        $this->respuesta = $respuesta;
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
        $this->setRuta($nombreArchivoFoto);
        $this->setNombre($this->file->getClientOriginalName());
    }

    //Funcionalidad qie permite reemplazar un archivo en el sistema
    public function reemplazarArchivo($directorioDestino)
    {
        if (null !== $this->getFile()) {
            $this->removeUpload($directorioDestino);
            $this->subirArchivo($directorioDestino);
        }
    }

    //Funcionalidad que permite eliminar un archivo del sistema
    public function removeUpload($directorioDestino)
    {
        $fs=new Filesystem();
        $rutaPc = $directorioDestino.DIRECTORY_SEPARATOR.$this->getRuta();
        if (null!=$this->getRuta()  && $fs->exists($rutaPc)) {
            $fs->remove($rutaPc);
        }
    }
}
