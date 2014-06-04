<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ExamenType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('codigo','text',	 array('label'=> 'Codigo'))
		->add('nombre','text',	 array('label'=> 'Nombre'))
		->add('tipo',  'integer',array('label'=> 'Tipo'))
		;	
	}
	
	public function getName()
	{
		return 'newExamen';
	}

}
