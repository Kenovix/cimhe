<?php

namespace dlaser\HcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\HcExamen
 *
 * @ORM\Table(name="hc_examen")
 * @ORM\Entity
 * 
 * @ORM\Entity(repositoryClass="dlaser\HcBundle\Entity\Repository\ExamenRepository")
 */
class HcExamen
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
     * @var datetime $fecha
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     * @Assert\NotBlank()
     * @Assert\DateTime
     */
    private $fecha;
    
    /**
     * @var datetime $fecha_r
     *
     * @ORM\Column(name="fecha_r", type="date", nullable=true)
     * @Assert\Date
     */
    private $fecha_r;

    /**
     * @var string $resultado
     *
     * @ORM\Column(name="resultado", type="string", length=255, nullable=true)
     * @Assert\MaxLength(limit=255, message="El valor ingresado debe tener máximo {{ limit }} caracteres.")
     */
    private $resultado;

    /**
     * @var boolean $estado
     *
     * @ORM\Column(name="estado", type="boolean", nullable=false)
     */
    private $estado;

    /**
     * @var Hc
     *
     * @ORM\ManyToOne(targetEntity="Hc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="hc_id", referencedColumnName="id")
     * })
     */
    private $hc;

    /**
     * @var Examen
     *
     * @ORM\ManyToOne(targetEntity="Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen_id", referencedColumnName="id")
     * })
     */
    private $examen;



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
     * Set fecha
     *
     * @param datetime $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * Get fecha
     *
     * @return datetime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }
    
    /**
     * Set fecha_r
     *
     * @param datetime $fecha_r
     */
    public function setFechaR($fecha_r)
    {
    	$this->fecha_r = $fecha_r;
    }
    
    /**
     * Get fecha_r
     *
     * @return datetime
     */
    public function getFechaR()
    {
    	return $this->fecha_r;
    }

    /**
     * Set resultado
     *
     * @param string $resultado
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;
    }

    /**
     * Get resultado
     *
     * @return string 
     */
    public function getResultado()
    {
        return $this->resultado;
    }

    /**
     * Set estado
     *
     * @param string $estado
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set hc
     *
     * @param dlaser\HcBundle\Entity\Hc $hc
     */
    public function setHc(\dlaser\HcBundle\Entity\Hc $hc)
    {
        $this->hc = $hc;
    }

    /**
     * Get hc
     *
     * @return dlaser\HcBundle\Entity\Hc 
     */
    public function getHc()
    {
        return $this->hc;
    }

    /**
     * Set examen
     *
     * @param dlaser\HcBundle\Entity\Examen $examen
     */
    public function setExamen(\dlaser\HcBundle\Entity\Examen $examen)
    {
        $this->examen = $examen;
    }

    /**
     * Get examen
     *
     * @return dlaser\HcBundle\Entity\Examen 
     */
    public function getExamen()
    {
        return $this->examen;
    }
}
