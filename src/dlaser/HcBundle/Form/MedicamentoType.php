<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MedicamentoType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('principio_activo','text',	array('label'=>'Nombre:*','attr' => array('placeholder' => 'Nombre del medicamento')))
		->add('concentracion',	 'text',	array('label'=>'Concentración:*','attr' => array('placeholder' => 'Concentración')))
		->add('presentacion',	 'text',	array('label'=>'Presentación:*','attr' => array('placeholder' => 'Presentación')))		
		->add('dosis_dia',		 'textarea', array('label'=>'Posologia:','required' => false,'attr' => array('placeholder' => 'Posologia')))
		->add('estado',	 'choice', array('choices' => array('A' => 'Activo', 'I' => 'Inactivo')))
		;
	}

	public function getName()
	{
		return 'newMedicamento';
	}
}