<?php

namespace dlaser\HcBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * dlaser\HcBundle\Entity\Files
 *
 * @ORM\Table(name="files")
 * @ORM\Entity
 */
class Files
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $estado
     *
     * @ORM\Column(name="estado", type="string", length=1)
     * @Assert\Choice(choices = {"A", "D"}, message = "Selecciona una opción valida.")
     */
    private $estado;

    /**
     * @var string $file    
     * @ORM\Column(name="img", type="string", length=255)
     * @Assert\Image(maxSize="5M")
     *     
     */     
    private $file;

    /**
     * @var integer $hcEstetica
     *
     * @ORM\ManyToOne(targetEntity="dlaser\HcBundle\Entity\HcEstetica")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="hcEstetica_id", referencedColumnName="id" )
     * })
     */
    private $hcEstetica;
    
    /**
     * @ORM\Column(name="nota", type="text", nullable=true)
     */
    public $nota;

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
     * Set file
     *
     * @param string $img
     */
    public function setImg($file)
    {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getImg()
    {
        return $this->file;
    }

    /**
     * Set nota
     *
     * @param text $nota
     */
    public function setNota($nota)
    {
        $this->nota = $nota;
    }

    /**
     * Get nota
     *
     * @return text 
     */
    public function getNota()
    {
        return $this->nota;
    }

    /**
     * Set hcEstetica
     *
     * @param dlaser\HcBundle\Entity\HcEstetica $hcEstetica
     */
    public function setHcEstetica(\dlaser\HcBundle\Entity\HcEstetica $hcEstetica)
    {
        $this->hcEstetica = $hcEstetica;
    }

    /**
     * Get hcEstetica
     *
     * @return integer 
     */
    public function getHcEstetica()
    {
        return $this->hcEstetica;
    }
    
    function saveDataFile($folder)
    {
    	if (null === $this->file) {
    		return;
    	}
    	$nameFile = uniqid('dlaser-').'.'.$this->file->guessExtension();
    	$this->file->move($folder, $nameFile);
    	$this->setImg($nameFile);
    }    
}