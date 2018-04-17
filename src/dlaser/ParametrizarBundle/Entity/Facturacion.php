<?php

namespace dlaser\ParametrizarBundle\Entity; 

use Doctrine\ORM\Mapping as ORM;

/**
 * dlaser\ParametrizarBundle\Entity\Facturacion
 *
 * @ORM\Table(name="f_f")
 * @ORM\Entity
 */
class Facturacion
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
     * @var string $prefijo
     *
     * @ORM\Column(name="prefijo", type="string", length=3, nullable=true)
     */
    private $prefijo;
    
    /**
     * @var integer $consecutivo
     *
     * @ORM\Column(name="consecutivo", type="integer", nullable=false)
     */
    private $consecutivo;

    /**
     * @var datetime $fecha
     * 
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;
    
    /**
     * @var datetime $inicio
     *
     * @ORM\Column(name="inicio", type="datetime", nullable=false)
     */
    private $inicio;
    
    /**
     * @var datetime $fin
     *
     * @ORM\Column(name="fin", type="datetime", nullable=false)
     */
    private $fin;
    
    /**
     * @var integer $sedes
     *
     * @ORM\Column(name="sedes", type="integer", nullable=true)
     */
    private $sedes;

    /**
     * @var string $concepto
	 *
     * @ORM\Column(name="concepto", type="text", nullable=false)
     */
    private $concepto;

    /**
     * @var integer $subtotal
     * 
     * @ORM\Column(name="subtotal", type="integer", nullable=false)
     */
    private $subtotal;

    /**
     * @var integer $iva
     *
     * @ORM\Column(name="iva", type="integer", nullable=true)
     */
    private $iva;
    
    /**
     * @var integer $copago
     *
     * @ORM\Column(name="copago", type="integer", nullable=true)
     */
    private $copago;

    /**
     * @var integer $nota
     *
     * @ORM\Column(name="nota", type="string", length=255, nullable=true)
     */
    private $nota;

    /**
     * @var string $estado
     *
     * @ORM\Column(name="estado", type="string", length=2, nullable=false)
     */
    private $estado;
    
    /**
     * @var integer $final
     *
     * @ORM\Column(name="final", type="integer", nullable=true)
     */
    private $final;
    
    /**
     * @var Cliente
     *
     * @ORM\ManyToOne(targetEntity="dlaser\ParametrizarBundle\Entity\Cliente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     * })
     */
    private $cliente;

    /**
     * @var Sede
     *
     * @ORM\ManyToOne(targetEntity="dlaser\ParametrizarBundle\Entity\Sede")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sede_id", referencedColumnName="id")
     * })
     */
    private $sede;
    
    /**
     * @var Paciente
     *
     * @ORM\ManyToOne(targetEntity="dlaser\ParametrizarBundle\Entity\Paciente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="paciente_id", referencedColumnName="id")
     * })
     */
    private $paciente;
    
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
     * Set prefijo
     *
     * @param string $prefijo
     */
    public function setPrefijo($prefijo)
    {
    	$this->prefijo = $prefijo;
    }
    
    /**
     * Get prefijo
     *
     * @return string
     */
    public function getPrefijo()
    {
    	return $this->prefijo;
    }
    
    /**
     * Set consecutivo
     *
     * @param integer $consecutivo
     */
    public function setConsecutivo($consecutivo)
    {
    	$this->consecutivo = $consecutivo;
    }
    
    /**
     * Get consecutivo
     *
     * @return integer
     */
    public function getConsecutivo()
    {
    	return $this->consecutivo;
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
     * Set inicio
     *
     * @param datetime $inicio
     */
    public function setInicio($inicio)
    {
    	$this->inicio = $inicio;
    }
    
    /**
     * Get inicio
     *
     * @return datetime
     */
    public function getInicio()
    {
    	return $this->inicio;
    }
    
    /**
     * Set fin
     *
     * @param datetime $fin
     */
    public function setFin($fin)
    {
    	$this->fin = $fin;
    }
    
    /**
     * Get fin
     *
     * @return datetime
     */
    public function getFin()
    {
    	return $this->fin;
    }
    
    /**
     * Set sedes
     *
     * @param integer $sedes
     */
    public function setSedes($sedes)
    {
    	$this->sedes = $sedes;
    }
    
    /**
     * Get sedes
     *
     * @return integer
     */
    public function getSedes()
    {
    	return $this->sedes;
    }
    
    /**
     * Set concepto
     *
     * @param string $concepto
     */
    public function setConcepto($concepto)
    {
    	$this->concepto = $concepto;
    }
    
    /**
     * Get concepto
     *
     * @return string
     */
    public function getConcepto()
    {
    	return $this->concepto;
    }
    
    /**
     * Set subtotal
     *
     * @param integer $subtotal
     */
    public function setSubtotal($subtotal)
    {
    	$this->subtotal = $subtotal;
    }
    
    /**
     * Get subtotal
     *
     * @return integer
     */
    public function getSubtotal()
    {
    	return $this->subtotal;
    }
    
    /**
     * Set iva
     *
     * @param integer $iva
     */
    public function setIva($iva)
    {
    	$this->iva = $iva;
    }
    
    /**
     * Get iva
     *
     * @return integer
     */
    public function getIva()
    {
    	return $this->iva;
    }
    
    /**
     * Set copago
     *
     * @param integer $copago
     */
    public function setCopago($copago)
    {
    	$this->copago = $copago;
    }
    
    /**
     * Get copago
     *
     * @return integer
     */
    public function getCopago()
    {
    	return $this->copago;
    }
    
    /**
     * Set nota
     *
     * @param string $nota
     */
    public function setNota($nota)
    {
    	$this->nota = $nota;
    }
    
    /**
     * Get nota
     *
     * @return string
     */
    public function getNota()
    {
    	return $this->nota;
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
     * Set final
     *
     * @param string $final
     */
    public function setFinal($final)
    {
        $this->final = $final;
    }
    
    /**
     * Get final
     *
     * @return integer
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Set cliente
     *
     * @param dlaser\ParametrizarBundle\Entity\Cliente $cliente
     */
    public function setCliente(\dlaser\ParametrizarBundle\Entity\Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * Get cliente
     *
     * @return dlaser\ParametrizarBundle\Entity\Cliente 
     */
    public function getCliente()
    {
        return $this->cliente;
    }   

    /**
     * Set sede
     *
     * @param dlaser\ParametrizarBundle\Entity\Sede $sede
     */
    public function setSede(\dlaser\ParametrizarBundle\Entity\Sede $sede)
    {
        $this->sede = $sede;
    }

    /**
     * Get sede
     *
     * @return dlaser\ParametrizarBundle\Entity\Sede 
     */
    public function getSede()
    {
        return $this->sede;
    }
    
    /**
     * Set paciente
     *
     * @param dlaser\ParametrizarBundle\Entity\Paciente $paciente
     */
    public function setPaciente(\dlaser\ParametrizarBundle\Entity\Paciente $paciente)
    {
    	$this->paciente = $paciente;
    }
    
    /**
     * Get paciente
     *
     * @return dlaser\ParametrizarBundle\Entity\Paciente
     */
    public function getPaciente()
    {
    	return $this->paciente;
    }
}