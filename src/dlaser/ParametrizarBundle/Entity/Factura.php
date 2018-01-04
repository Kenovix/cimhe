<?php

namespace dlaser\ParametrizarBundle\Entity; 

use Doctrine\ORM\Mapping as ORM;

/**
 * dlaser\ParametrizarBundle\Entity\Factura
 *
 * @ORM\Table(name="factura")
 * @ORM\Entity
 * 
 * @ORM\Entity(repositoryClass="dlaser\ParametrizarBundle\Entity\Repository\FacturaRepository")
 */
class Factura
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
     */
    private $fecha;
    
    /**
     * @var datetime $inicio
     *
     * @ORM\Column(name="inicio", type="datetime", nullable=true)
     */
    private $inicio;
    
    /**
     * @var datetime $fin
     *
     * @ORM\Column(name="fin", type="datetime", nullable=true)
     */
    private $fin;
    
    /**
     * @var integer $sedes
     *
     * @ORM\Column(name="sedes", type="integer", nullable=true)
     */
    private $sedes;

    /**
     * @var datetime $fR
     * 
     * @ORM\Column(name="f_r", type="datetime", nullable=true)
     */
    private $fR;

    /**
     * @var string $autorizacion

     * @ORM\Column(name="autorizacion", type="string", length=30, nullable=true)
     */
    private $autorizacion;
    
    /**
     * @var string $concepto
     *
     * @ORM\Column(name="concepto", type="text", nullable=true)
     */
    private $concepto;
    
    /**
     * @var integer $iva
     *
     * @ORM\Column(name="iva", type="integer", nullable=true)
     */
    private $iva;
    
    /**
     * @var string $tipo
    
     * @ORM\Column(name="tipo", type="string", length=1, nullable=true)
     */
    private $tipo;

    /**
     * @var integer $valor
     * 
     * @ORM\Column(name="valor", type="integer", nullable=false)
     */
    private $valor;

    /**
     * @var integer $copago
     *
     * @ORM\Column(name="copago", type="integer", nullable=false)
     */
    private $copago;

    /**
     * @var integer $descuento
     *
     * @ORM\Column(name="descuento", type="integer", nullable=true)
     */
    private $descuento;

    /**
     * @var string $estado
     *
     * @ORM\Column(name="estado", type="string", length=1, nullable=true)
     */
    private $estado;
    
    /**
     * @var string $grupo
     *
     * @ORM\Column(name="grupo", type="string", length=2, nullable=true)
     */
    private $grupo;

    /**
     * @var string $observacion
     *
     * @ORM\Column(name="observacion", type="string", length=255, nullable=true)
     */
    private $observacion;

    /**
     * @var Cupo
     *
     * @ORM\ManyToOne(targetEntity="dlaser\AgendaBundle\Entity\Cupo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cupo_id", referencedColumnName="id", nullable="true")
     * })
     */
    private $cupo;

    /**
     * @var Cargo
     *
     * @ORM\ManyToOne(targetEntity="dlaser\ParametrizarBundle\Entity\Cargo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cargo_id", referencedColumnName="id", nullable="true")
     * })
     */
    private $cargo;

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
     * @var Paciente
     *
     * @ORM\ManyToOne(targetEntity="dlaser\ParametrizarBundle\Entity\Paciente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="paciente_id", referencedColumnName="id", nullable="true")
     * })
     */
    private $paciente;

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
     * Set fR
     *
     * @param datetime $fR
     */
    public function setFR($fR)
    {
        $this->fR = $fR;
    }

    /**
     * Get fR
     *
     * @return datetime 
     */
    public function getFR()
    {
        return $this->fR;
    }

    /**
     * Set autorizacion
     *
     * @param string $autorizacion
     */
    public function setAutorizacion($autorizacion)
    {
        $this->autorizacion = $autorizacion;
    }

    /**
     * Get autorizacion
     *
     * @return string 
     */
    public function getAutorizacion()
    {
        return $this->autorizacion;
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
     * Set tipo
     *
     * @param string $tipo
     */
    public function setTipo($tipo)
    {
    	$this->tipo = $tipo;
    }
    
    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
    	return $this->tipo;
    }

    /**
     * Set valor
     *
     * @param integer $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * Get valor
     *
     * @return integer 
     */
    public function getValor()
    {
        return $this->valor;
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
     * Set descuento
     *
     * @param integer $descuento
     */
    public function setDescuento($descuento)
    {
        $this->descuento = $descuento;
    }

    /**
     * Get descuento
     *
     * @return integer 
     */
    public function getDescuento()
    {
        return $this->descuento;
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
     * Set grupo
     *
     * @param string $grupo
     */
    public function setGrupo($grupo)
    {
    	$this->grupo = $grupo;
    }
    
    /**
     * Get grupo
     *
     * @return string
     */
    public function getGrupo()
    {
    	return $this->grupo;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set cupo
     *
     * @param dlaser\ParametrizarBundle\Entity\Cupo $cupo
     */
    public function setCupo(\dlaser\AgendaBundle\Entity\Cupo $cupo)
    {
        $this->cupo = $cupo;
    }

    /**
     * Get cupo
     *
     * @return dlaser\AgendaBundle\Entity\Cupo 
     */
    public function getCupo()
    {
        return $this->cupo;
    }

    /**
     * Set cargo
     *
     * @param dlaser\ParametrizarBundle\Entity\Cargo $cargo
     */
    public function setCargo(\dlaser\ParametrizarBundle\Entity\Cargo $cargo)
    {
        $this->cargo = $cargo;
    }

    /**
     * Get cargo
     *
     * @return dlaser\ParametrizarBundle\Entity\Cargo 
     */
    public function getCargo()
    {
        return $this->cargo;
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
}