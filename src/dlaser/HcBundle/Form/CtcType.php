<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class CtcType extends AbstractType
{
	private $options;
	
	public function __construct(array $options = null)
	{
		$this->options = $options;
	}
	
	public function buildForm(FormBuilder $builder, array $options)
	{
		$id = $this->options['cie'];
		
		$builder
		->add('fecha','datetime',			array('read_only'=>true))				
		->add('cie', 'entity', array(
				'class' => 'dlaser\\HcBundle\\Entity\\Cie',
				'query_builder' => function(EntityRepository $er) use ($id) {
				return $er->createQueryBuilder('c','hc')				
				->leftJoin("c.hc", "hc")
				->where("hc.id = :id")
				->setParameter('id', $id);
		}
		))						
		->add('resumen_hc','textarea',		array('label'=> 'Resumen HC'))
		->add('pos_utilizado','text',		array('required' => false,'label'=> 'Pos utilizado'))
		->add('pos_dosis','integer',		array('required' => false,'label'=> 'Pos dosis'))
		->add('pos_tiempo','integer',		array('required' => false,'label'=> 'Pos tiempo'))
		->add('pos_posologia','text',		array('required' => false,'label'=> 'Pos posologia'))
		->add('pos_respuesta','textarea',	array('required' => false,'label'=> 'Pos respuesta'))
		->add('nopos_nota','textarea',		array('label'=> 'Justificación No POS *'))
		->add('nopos_efectos','textarea',	array('label'=> 'Efectos Adversos del No POS *'))		
		;
	}

	public function getName()
	{
		return 'newCTC';
	}
	
	public function getDefaultOptions(array $options){
		return $options;
	}
}
