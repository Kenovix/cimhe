<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DxSearchType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('nombre','text',array('label'=> 'Busqueda rapida:','property_path' => false,
				'attr' => array('placeholder' => 'Ingrese el nombre del examen',
						'autofocus'=>'autofocus')))
				
		->add('option', 'choice', array(
				'label'=> 'Opcion de busqueda:',
				'property_path' => false,
				 'choices' => array(
				 		'nombre'=> 'Nombre',
				 		'codigo'=> 'Codigo',),
				'multiple'=>false,
				))
						;
	}

	public function getName()
	{
		return 'searchNombre';
	}
}
