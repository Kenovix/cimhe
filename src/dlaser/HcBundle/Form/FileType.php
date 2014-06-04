<?php

namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FileType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('estado', 'choice', array('required' => true,
				'choices' => array('A' => 'Antes', 'D' => 'Despues')))		
		->add('img', 'file' , array ('label'=> 'Imagen:' , 'required' => false))
		->add('nota', 'textarea', array('label'=>'Nota:','required' => true,  'attr' => array('placeholder' => 'Ingrese conclusion')))
		;	
	}

	public function getName()
	{
		return 'newFile';
	}
}