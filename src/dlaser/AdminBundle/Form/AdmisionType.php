<?php

namespace dlaser\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AdmisionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder 
        ->add('fecha', 'datetime',  array('label' => 'Fecha:', 'required' => true))
        ->add('autorizacion', 'text', array('required' => true, 'attr' => array('placeholder' => 'Número de autorización', 'autofocus' => 'autofocus')))
        ->add('subtotal', 'integer', array('label' => 'Valor', 'required' => true))
        ->add('copago', 'integer', array('required' => true))
        ->add('cliente', 'entity', array('required' => true,'class' => 'dlaser\ParametrizarBundle\Entity\Cliente', 'empty_value' => 'Elige una aseguradora'))
        ->add('cargo', 'entity', array('required' => true, 'class' => 'dlaser\ParametrizarBundle\Entity\Cargo', 'empty_value' => 'Elige un cargo'))
        ->add('grupo', 'choice', array('required' => true, 'choices' => array('RX' => 'Rayos X', 'EC' => 'Ecografia')))
        ->add('sede', 'entity', array('required' => true, 'class' => 'dlaser\ParametrizarBundle\Entity\Sede', 'empty_value' => 'Elige una sede'))
        ->add('estado', 'choice', array('choices' => array('G' => 'Generada', 'I' => 'Informado', 'P' => 'Pendiente', 'X' => 'Anulado'), 'required' => true))
        ;
    }

    public function getName()
    {
        return 'editAdmision';
    }
}