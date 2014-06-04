<?php

namespace dlaser\HcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\Medicamento
 *
 * @ORM\Table(name="medicamento")
 * @ORM\Entity
 * 
 * @ORM\Entity(repositoryClass="dlaser\HcBundle\Entity\Repository\MedicamentoRepository")
 */
class Medicamento
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
     * @var string $principioActivo
     * 
     * @ORM\Column(name="principio_activo", type="string", length=200, nullable=false)
     * @Assert\NotBlank()
     * @Assert\MaxLength(limit=200, message="El principioActivo ingresado no puede sobrepasar {{ limit }} caracteres.")
     */
    private $principioActivo;

    /**
     * @var string $concentracion
     * 
     * @ORM\Column(name="concentracion", type="string", length=10, nullable=false)
     * @Assert\NotBlank(message="El valor ingresado no puede estar vacio.")
     * @Assert\MaxLength(limit=10, message="La concentracion ingresado no puede sobrepasar {{ limit }} caracteres.")
     */
    private $concentracion;

    /**
     * @var string $presentacion
     * 
     * @ORM\Column(name="presentacion", type="string", length=30, nullable=false)
     * @Assert\NotBlank(message="El valor ingresado no puede estar vacio.")
     * @Assert\MaxLength(limit=30, message="La presentacion ingresado no puede sobrepasar {{ limit }} caracteres.")
     */
    private $presentacion;

    /**
     * @var integer $dosisDia
     * 
     * @ORM\Column(name="dosis_dia", type="string", length=255, nullable=true)
     * 
     */
    private $dosisDia;

    /**
     * @var integer $tiempo
     * 
     * @ORM\Column(name="tiempo", type="integer", nullable=true)
     */
    private $tiempo;
    
    /**
     * @var integer $diasTratamiento
     * 
     * @ORM\Column(name="dias_tratamiento", type="integer", nullable=true)
     */    
    private $diasTratamiento;
    
    /**
     * @var boolean $pos     
     * @ORM\Column(name="pos", type="boolean", nullable=true)
     */    
    private $pos;
    
    /**
     * @var integer $justificacion
     * 
     * @ORM\Column(name="justificacion", type="text", nullable=true)
     */    
    private $justificacion;
    
    /**
     * @var integer $efectos
     * 
     * @ORM\Column(name="efectos", type="text", nullable=true)
     */    
    private $efectos;
    
    /**
     * @var integer $invima
     * 
     * @ORM\Column(name="invima", type="string", length=150, nullable=true)
     * @Assert\MaxLength(limit=150, message="Invima ingresado no puede sobrepasar {{ limit }} caracteres.")
     */
    private $invima;
    
    /**
     * @var boolean $estado
     * @ORM\Column(name="estado", type="string", length=1, nullable=true)
     */
    private $estado;

    /**
     * @var Usuario
     *
     * @ORM\ManyToOne(targetEntity="dlaser\UsuarioBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;

    public function __construct()
    {
        
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
     * Set principioActivo
     *
     * @param string $principioActivo
     */
    public function setPrincipioActivo($principioActivo)
    {
        $this->principioActivo = $principioActivo;
    }

    /**
     * Get principioActivo
     *
     * @return string 
     */
    public function getPrincipioActivo()
    {
        return $this->principioActivo;
    }

    /**
     * Set concentracion
     *
     * @param string $concentracion
     */
    public function setConcentracion($concentracion)
    {
        $this->concentracion = $concentracion;
    }

    /**
     * Get concentracion
     *
     * @return string 
     */
    public function getConcentracion()
    {
        return $this->concentracion;
    }

    /**
     * Set presentacion
     *
     * @param string $presentacion
     */
    public function setPresentacion($presentacion)
    {
        $this->presentacion = $presentacion;
    }

    /**
     * Get presentacion
     *
     * @return string 
     */
    public function getPresentacion()
    {
        return $this->presentacion;
    }

    /**
     * Set dosisDia
     *
     * @param integer $dosisDia
     */
    public function setDosisDia($dosisDia)
    {
        $this->dosisDia = $dosisDia;
    }

    /**
     * Get dosisDia
     *
     * @return integer 
     */
    public function getDosisDia()
    {
        return $this->dosisDia;
    }

    /**
     * Set tiempo
     *
     * @param integer $tiempo
     */
    public function setTiempo($tiempo)
    {
        $this->tiempo = $tiempo;
    }

    /**
     * Get tiempo
     *
     * @return integer 
     */
    public function getTiempo()
    {
        return $this->tiempo;
    }

    /**
     * Set diasTratamiento
     *
     * @param integer $diasTratamiento
     */
    public function setDiasTratamiento($diasTratamiento)
    {
        $this->diasTratamiento = $diasTratamiento;
    }

    /**
     * Get diasTratamiento
     *
     * @return integer 
     */
    public function getDiasTratamiento()
    {
        return $this->diasTratamiento;
    }

    /**
     * Set pos
     *
     * @param boolean $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * Get pos
     *
     * @return boolean 
     */
    public function getPos()
    {
        return $this->pos;
    }
    
    /**
     * Set justificacion
     *
     * @param text $justificacion
     */
    public function setJustificacion($justificacion)
    {
        $this->justificacion = $justificacion;
    }

    /**
     * Get justificacion
     *
     * @return text 
     */
    public function getJustificacion()
    {
        return $this->justificacion;
    }
   /**
     * Set efectos
     *
     * @param text $efectos
     */
    public function setEfectos($efectos)
    {
        $this->efectos = $efectos;
    }

    /**
     * Get efectos
     *
     * @return text 
     */
    public function getEfectos()
    {
        return $this->efectos;
    }
   /**
     * Set invima
     *
     * @param string $invima
     */
    public function setInvima($invima)
    {
        $this->invima = $invima;
    }

    /**
     * Get invima
     *
     * @return string 
     */
    public function getInvima()
    {
        return $this->invima;
    }
    
    /**
     * Set estado
     *
     * @param boolean $estado
     */
    public function setEstado($estado)
    {
    	$this->estado = $estado;
    }
    
    /**
     * Get estado
     *
     * @return boolean
     */
    public function getEstado()
    {
    	return $this->estado;
    }
    
    /**
     * Set usuario
     *
     * @param dlaser\HcBundle\Entity\Usuario $usuario
     */
    public function setUsuario(\dlaser\UsuarioBundle\Entity\Usuario $usuario)
    {
        $this->usuario = $usuario;
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
