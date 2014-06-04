<?php

namespace dlaser\HcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\Ctc
 *
 * @ORM\Table(name="ctc")
 * @ORM\Entity
 */
class Ctc
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
     * @Assert\DateTime
     */
    private $fecha;

    /**
     * @var text $resumenHc
     *
     * @ORM\Column(name="resumen_hc", type="text", nullable=false)
     * @Assert\NotBlank(message="El resumenHc ingresado no puede estar vacio.")
     */
    private $resumenHc;

    /**
     * @var string $posUtilizado
     *
     * @ORM\Column(name="pos_utilizado", type="string", length=150, nullable=true)
     * @Assert\MaxLength(limit=150, message="El posUtilizado  debe tener al menos {{ limit }} caracteres.")
     */
    private $posUtilizado;

    /**
     * @var integer $posDosis
     *
     * @ORM\Column(name="pos_dosis", type="integer", nullable=true)
     * @Assert\Max(limit = "99", message = "El posDosis ingresado no puede ser mayor de {{ limit }}",invalidMessage = "El valor ingresado debe ser un número válido")
     */
    private $posDosis;

    /**
     * @var integer $posTiempo
     *
     * @ORM\Column(name="pos_tiempo", type="integer", nullable=true)
     * @Assert\Max(limit = "999", message = "El posTiempo ingresado no puede ser mayor de {{ limit }}",invalidMessage = "El valor ingresado debe ser un número válido")
     */
    private $posTiempo;

    /**
     * @var string $posPosologia
     *
     * @ORM\Column(name="pos_posologia", type="string", length=180, nullable=true)
     * @Assert\MaxLength(limit=180, message="El posTiempo ingresado debe tener al menos {{ limit }} caracteres.")
     */
    private $posPosologia;

    /**
     * @var text $posRespuesta
     *
     * @ORM\Column(name="pos_respuesta", type="text", nullable=true)
     */
    private $posRespuesta;

    /**
     * @var text $noposNota
     *
     * @ORM\Column(name="nopos_nota", type="text", nullable=false)
     * @Assert\NotBlank(message="El noposNota ingresado no puede estar vacio.")
     */
    private $noposNota;

    /**
     * @var text $noposEfectos
     *
     * @ORM\Column(name="nopos_efectos", type="text", nullable=false)
     * @Assert\NotBlank(message="El noposEfectos ingresado no puede estar vacio.")
     */
    private $noposEfectos;

    /**
     * @var Cie
     *
     * @ORM\ManyToOne(targetEntity="dlaser\HcBundle\Entity\Cie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cie_id", referencedColumnName="id")
     * })
     */
    private $cie;

    /**
     * @var Medicamento
     *
     * @ORM\ManyToOne(targetEntity="dlaser\HcBundle\Entity\Medicamento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="medicamento_id", referencedColumnName="id")
     * })
     */
    private $medicamento;

    /**
     * @var Hc
     *
     * @ORM\ManyToOne(targetEntity="dlaser\HcBundle\Entity\Hc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="hc_id", referencedColumnName="id")
     * })
     */
    private $hc;



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
     * Set resumenHc
     *
     * @param text $resumenHc
     */
    public function setResumenHc($resumenHc)
    {
        $this->resumenHc = $resumenHc;
    }

    /**
     * Get resumenHc
     *
     * @return text 
     */
    public function getResumenHc()
    {
        return $this->resumenHc;
    }

    /**
     * Set posUtilizado
     *
     * @param string $posUtilizado
     */
    public function setPosUtilizado($posUtilizado)
    {
        $this->posUtilizado = $posUtilizado;
    }

    /**
     * Get posUtilizado
     *
     * @return string 
     */
    public function getPosUtilizado()
    {
        return $this->posUtilizado;
    }

    /**
     * Set posDosis
     *
     * @param integer $posDosis
     */
    public function setPosDosis($posDosis)
    {
        $this->posDosis = $posDosis;
    }

    /**
     * Get posDosis
     *
     * @return integer 
     */
    public function getPosDosis()
    {
        return $this->posDosis;
    }

    /**
     * Set posTiempo
     *
     * @param integer $posTiempo
     */
    public function setPosTiempo($posTiempo)
    {
        $this->posTiempo = $posTiempo;
    }

    /**
     * Get posTiempo
     *
     * @return integer 
     */
    public function getPosTiempo()
    {
        return $this->posTiempo;
    }

    /**
     * Set posPosologia
     *
     * @param string $posPosologia
     */
    public function setPosPosologia($posPosologia)
    {
        $this->posPosologia = $posPosologia;
    }

    /**
     * Get posPosologia
     *
     * @return string 
     */
    public function getPosPosologia()
    {
        return $this->posPosologia;
    }

    /**
     * Set posRespuesta
     *
     * @param text $posRespuesta
     */
    public function setPosRespuesta($posRespuesta)
    {
        $this->posRespuesta = $posRespuesta;
    }

    /**
     * Get posRespuesta
     *
     * @return text 
     */
    public function getPosRespuesta()
    {
        return $this->posRespuesta;
    }

    /**
     * Set noposNota
     *
     * @param text $noposNota
     */
    public function setNoposNota($noposNota)
    {
        $this->noposNota = $noposNota;
    }

    /**
     * Get noposNota
     *
     * @return text 
     */
    public function getNoposNota()
    {
        return $this->noposNota;
    }

    /**
     * Set noposEfectos
     *
     * @param text $noposEfectos
     */
    public function setNoposEfectos($noposEfectos)
    {
        $this->noposEfectos = $noposEfectos;
    }

    /**
     * Get noposEfectos
     *
     * @return text 
     */
    public function getNoposEfectos()
    {
        return $this->noposEfectos;
    }

    /**
     * Set cie
     *
     * @param dlaser\HcBundle\Entity\Cie $cie
     */
    public function setCie(\dlaser\HcBundle\Entity\Cie $cie)
    {
        $this->cie = $cie;
    }

    /**
     * Get cie
     *
     * @return dlaser\HcBundle\Entity\Cie 
     */
    public function getCie()
    {
        return $this->cie;
    }

    /**
     * Set medicamento
     *
     * @param dlaser\HcBundle\Entity\Medicamento $medicamento
     */
    public function setMedicamento(\dlaser\HcBundle\Entity\Medicamento $medicamento)
    {
        $this->medicamento = $medicamento;
    }

    /**
     * Get medicamento
     *
     * @return dlaser\HcBundle\Entity\Medicamento 
     */
    public function getMedicamento()
    {
        return $this->medicamento;
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
}
