<?php

namespace dlaser\HcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\Cie
 *
 * @ORM\Table(name="cie")
 * @ORM\Entity
 * 
 * @ORM\Entity(repositoryClass="dlaser\HcBundle\Entity\Repository\CieRepository")
 */
class Cie
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
     * @ORM\Column(name="codigo", type="string", length=5, nullable=false)
     * @Assert\NotBlank(message="El valor ingresado no puede estar vacio.")
     * @Assert\MaxLength(limit=5, message="El valor ingresado debe tener al menos {{ limit }} caracteres.")
     */
    private $codigo;

    /**
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="El valor ingresado no puede estar vacio.")
     * @Assert\MaxLength(limit=255, message="El valor ingresado debe tener al menos {{ limit }} caracteres.")
     */
    private $nombre;

    /**
     * @var Hc
     *
     * @ORM\ManyToMany(targetEntity="dlaser\HcBundle\Entity\Hc")
     */
    private $hc;

    /**
     * @var Usuario
     *
     * @ORM\ManyToMany(targetEntity="dlaser\UsuarioBundle\Entity\Usuario")
     */
    private $usuario;

    public function __construct()
    {
        $this->hc = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * Get codigo
     *
     * @return string 
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
     * Add hc
     *
     * @param dlaser\HcBundle\Entity\Hc $hc
     */
    public function addHc(\dlaser\HcBundle\Entity\Hc $hc)
    {
        if (!$this->hasHc($hc)) {
        	$this->hc[] = $hc;
        	return true;
        }
        return false;
    }
    public function hasHc(\dlaser\HcBundle\Entity\Hc $hc)
    {
    	foreach ($this->hc as $value) {
    		if ($value->getId() == $hc->getId()) {
    			return true;
    		}
    	}
    	return false;
    }

    /**
     * Get hc
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getHc()
    {
        return $this->hc;
    }

    /**
     * Add usuario
     *
     * @param dlaser\HcBundle\Entity\Usuario $usuario
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

    public function __toString()
    {
        return $this->getNombre();
    }

}
