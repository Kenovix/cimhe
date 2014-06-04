<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MedicamentoSearchType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('nombre','text',array('label'=> 'Busqueda rapida:','property_path' => false,
				'attr' => array('placeholder' => 'Ingrese el nombre del examen',
						'autofocus'=>'autofocus')))
		;
	}
	
	public function getName()
	{
		return 'searchNombre';
	}

}
