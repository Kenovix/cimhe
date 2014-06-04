<?php

namespace dlaser\HcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\HcMedicamento
 *
 * @ORM\Table(name="hc_medicamento")
 * @ORM\Entity
 * 
 * @ORM\Entity(repositoryClass="dlaser\HcBundle\Entity\Repository\MedicamentoRepository")
 */
class HcMedicamento
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
     * @var Hc
     *
     * @ORM\ManyToOne(targetEntity="Hc")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="hc_id", referencedColumnName="id")
     * })
     */
    private $hc;

    /**
     * @var medicamento
     *
     * @ORM\ManyToOne(targetEntity="Medicamento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="medicamento_id", referencedColumnName="id")
     * })
     */
    private $medicamento;
     /**
     * @var boolean $estado
     *
     * @ORM\Column(name="estado", type="string", length=1, nullable=false)
     */
    private $estado;



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
     * @return dlaser\HcBundle\Entity\medicamento 
     */
    public function getMedicamento()
    {
        return $this->medicamento;
    }
}
