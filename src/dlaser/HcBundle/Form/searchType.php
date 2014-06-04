<?php

namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class searchType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('tipoid', 'choice', array(
				'label'=> 'Tipo ID:',
				'property_path' => false,
				 'choices' => array(
				 		'cc'=> 'CC',
				 		'ti'=> 'TI',
				 		'ce'=> 'CE',),
				'multiple'=>false,
				))
		->add('paciente','integer',array('label'=> 'Busqueda rapida:','property_path' => false,
						 'attr' => array('placeholder' => 'Ingrese # de identificacion del paciente','autofocus'=>'autofocus')))
		;
	}
	
	public function getName()
	{
		return 'newSearch';
	}
}
