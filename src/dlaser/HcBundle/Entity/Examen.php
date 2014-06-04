<?php

namespace dlaser\HcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\Examen
 *
 * @ORM\Table(name="examen")
 * @ORM\Entity
 * 
 * @ORM\Entity(repositoryClass="dlaser\HcBundle\Entity\Repository\ExamenRepository")
 */
class Examen
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $codigo
     *
     * @ORM\Column(name="codigo", type="string", length=7, nullable=false)
     * @Assert\NotBlank()
     * @Assert\MaxLength(limit=7, message="El codigo ingresado debe tener máximo {{ limit }} caracteres.")
     */
    private $codigo;

    /**
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=60, nullable=false)
     * @Assert\NotBlank()
     * @Assert\MaxLength(limit=60, message="El nombre ingresado debe tener máximo {{ limit }} caracteres.")
     */
    private $nombre;

    /**
     * @var integer $tipo
     *
     * @ORM\Column(name="tipo", type="integer", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Max(limit = "999", message = "El tipo ingresado no puede ser mayor de {{ limit }}",invalidMessage = "El valor ingresado debe ser un número válido")
     */
    private $tipo;

    /**
     * @var Usuario
     *
     * @ORM\ManyToMany(targetEntity="dlaser\UsuarioBundle\Entity\Usuario", mappedBy="examen")
     */
    private $usuario;

    public function __construct()
    {
        $this->usuario = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set codigo
     *
     * @param integer $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set tipo
     *
     * @param integer $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * Get tipo
     *
     * @return integer 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Add usuario
     *
     * @param dlaser\UsuarioBundle\Entity\Usuario $usuario
     */
    public function addUsuario(\dlaser\UsuarioBundle\Entity\Usuario $usuario)
    {
        if (!$this->hasUsuario($usuario)) {
        	$this->usuario[] = $usuario;
        	return true;
        }
        return false;
    }
    
    public function hasUsuario(\dlaser\UsuarioBundle\Entity\Usuario $usuario)
    {
    	foreach ($this->usuario as $value) {
    		if ($value->getId() == $usuario->getId()) {
    			return true;
    		}
    	}
    	return false;
    }

    /**
     * Get usuario
     *
     * @return dlaser\UsuarioBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
