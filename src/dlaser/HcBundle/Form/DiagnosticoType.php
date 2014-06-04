<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DiagnosticoType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('codigo','text',array(
				'label'=>'Codigo','attr' => array(
						'placeholder' => 'Ingrese el codigo','autofocus'=>'autofocus')))
		->add('nombre','text',array(
				'label'=>'Nombre','attr' => array(
						'placeholder' => 'Ingrese el codigo',)))
		;
		
	}
	
	public function getName()
	{
		return 'newDiagnostico';
	}

}
