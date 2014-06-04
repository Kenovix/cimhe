<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class HcExamenType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		//->add('fecha', 'date', array('read_only'=>true,'required' => false))
		->add('fecha_r', 'date', array('label' => 'Fecha realizado', 'read_only'=>false,'required' => false))
		->add('resultado','textarea', array('required' => false,'label'=> 'Resultado'))
		;		
	}
	
	public function getName()
	{
		return 'newHcExamen';
	}

}
