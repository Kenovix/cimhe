<?php
namespace dlaser\HcBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class HcEsteticaType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('fecha', 'date',array('read_only'=>true))
		->add('edad_crono', 'integer', array('label' => 'Edad cronológica','attr' => array('autofocus'=>'autofocus')))
		->add('edad_aparente', 'integer', array('label' => 'Edad aparente'))
		
		->add('piel_color', 'choice', array(
				'label' => 'Piel Color:',
				'choices' => array(''=>'--Seleccione--', 'N' => 'Normal', 'P' => 'Palida', 'R' => 'Rojiza')))
		->add('piel_cutis', 'choice', array(
				'label' => 'Piel Cutis:',
				'choices' => array(''=>'--Seleccione--','S' => 'Seco', 'G' => 'Graso', 'M' => 'Mixto')))
		->add('piel_tacto', 'choice', array(
				'label' => 'Piel Tacto:',
				'choices' => array(''=>'--Seleccione--','LF' => 'Lisa y Fina', 'GR' => 'Gruesa y Rugosa')))
		->add('dentadura', 'choice', array(
				'label' => 'Dentadura:',
				'choices' => array(''=>'--Seleccione--','B' => 'Buena', 'R' => 'Regular', 'M' => 'Mala', 'P' => 'Protesis')))
		
		->add('nutricion', 'choice', array(
				'choices' => array(''=>'--Seleccione--','N'=>'Normal','OB' => 'Obesidad', 'KG ' => 'KGS de exceso', 'DE' => 'Desnutricion'),
				'label' => 'Nutrición:'))
		
		->add('kgs', 'integer', array('label' => 'K.G.S'))
				
		->add('op', 'choice', array(
				'label' => 'Orificios pilocebaceos:',
				'choices' => array(
						'aspecto_normal' => 'Aspecto normal', 'orificios_poco_visible ' => 'OrificiosPocoVisible',
						'acne_conglobata' => 'Acne conglobata', 'comedones' => 'Comedones', 'orificios_manifiestos' => 'OrificiosManifiesto',
						'pustulas' => 'Pustulas', 'miliun' => 'Miliun', 'foliculitis' => 'Foliculitis', 'secuela_acne' => 'Secuela acne'
						),
				'multiple'=>true,
				'expanded' => true,				
		))
		->add('pigmentacion', 'choice', array(
				'label' => 'Pigmentación:',
				'choices' => array(
						'normal' => 'Normal', 'medicamentosa' => 'Medicamentosa', 'solar' => 'Solar',
						'malesma' => 'Malesma', 'cosmetica' => 'Cosmetica', 'maquillajes' => 'Maquillajes'
						),
				'multiple'=>true,
				'expanded' => true,				
		))
		->add('arrugas', 'choice', array(
				'label' => 'Arrugas:',
				'choices' => array(
						'expresion_normal' => 'Expresion Normal', 'preauriculares ' => 'Preauriculares',
						'nasogenianos' => 'Nasogenianos', 'pliegues_finos' => 'Pliegues finos', 'pata_gallo' => 'Pata de gallo',
						'frontales' => 'Frontales', 'glabelares' => 'Glabelares', 'pliegues_profundos' => 'Pliegues profundos',
						'peribucales' => 'Peribucales', 'cuello' => 'Cuello', 'nasales' => 'Nasales', 'pliegues_cicatrizales' => 'Pliegues cicatrizales',
						),
				'multiple'=>true,
				'expanded' => true,				
		))
		
		->add('flacidez', 'choice', array(
				'label' => 'Flacidez:',
				'choices' => array(
						'leve' => 'Leve', 'moderada' => 'Moderada', 'severa' => 'Severa',
						'cara' => 'Cara', 'cuello' => 'Cuello', 'torax' => 'Torax', 'senos' => 'Senos',	'brazos' => 'Brazos', 
						'abdomen' => 'Abdomen', 'genital' => 'Genital', 'gluteos' => 'Gluteos', 'muslos' => 'Muslos' 
				),
				'multiple'=>true,
				'expanded' => true,				
		))
		->add('parpado', 'choice', array(
				'label' => 'Parpado:',
				'choices' => array(
						'ptosis' => 'PTosis', 'edematizados ' => 'Edematizados', 'ojeras' => 'Ojeras', 'bolsas_superiores' => 'Bolsas superiores', 
						'xantelasma' => 'Xantelasma', 'bolsas_inferiores' => 'Bolsas inferiores', 'queratosis' => 'Queratosis'
				),
				'multiple'=>true,
				'expanded' => true,				
		))		
		->add('lesiones_cut', 'choice', array(
				'label' => 'Lesiones Cutaneas:',
				'choices' => array(
						'querato_seborreica' => 'Qt. seborreica', 'querato_acantoma ' => 'Qt. acantoma', 'nevus ' => 'Nevus', 'quiste_sebaceo' => 'Quiste sebaceo', 
						'cicatrices' => 'Cicatrices', 'rosacea' => 'Rosacea', 'melanoma' => 'Melanoma', 'epit_basocelular' => 'Epit. Basocelular', 'epit_espinocelular' => 'Epit. Espinocelular', 
						'discronia' => 'Discronia', 'papiloma' => 'Papiloma', 'fibromas' => 'Fibromas', 'acrocordones' => 'Acrocordones'						
				),
				'multiple'=>true,
				'expanded' => true,				
		))		
		->add('lipodistrofia', 'choice', array(
				'label' => 'Lipodistrofia:',
				'choices' => array(
						'leve' => 'Leve', 'moderada' => 'Moderada', 'severa' => 'Severa',
						'cara' => 'Cara', 'cuello' => 'Cuello', 'torax' => 'Torax', 'senos' => 'Senos',	'brazos' => 'Brazos', 
						'abdomen' => 'Abdomen', 'genital' => 'Genital', 'gluteos' => 'Gluteos', 'muslos' => 'Muslos'
				),
				'multiple'=>true,
				'expanded' => true,				
		))		
		->add('tatuaje', 'choice', array(
				'label' => 'Tatuaje:',
				'choices' => array(
						'leve' => 'Leve', 'moderada' => 'Moderada', 'severa' => 'Severa',
						'cara' => 'Cara', 'cuello' => 'Cuello', 'torax' => 'Torax', 'senos' => 'Senos',	'brazos' => 'Brazos', 
						'abdomen' => 'Abdomen', 'genital' => 'Genital', 'gluteos' => 'Gluteos', 'muslos' => 'Muslos',
						'piernas' => 'Piernas', 'pies' => 'Pies'
				),
				'multiple'=>true,
				'expanded' => true,				
		))
		->add('cicatrizes', 'choice', array(
				'label' => 'Cicatrizes:',
				'choices' => array(
						'normal' => 'Normal', 'hipertroficas' => 'Hipertroficas', 'queloide' => 'Queloide', 'hipotrofica' => 'Hipotrofica',
						'hipopigmentadas' => 'Hipopigmentadas', 'hiperpigmentadas' => 'Hiperpigmentadas',
						'cara' => 'Cara', 'oreja' => 'Oreja', 'cuello' => 'Cuello', 'torax' => 'Torax', 'senos' => 'Senos',	'brazos' => 'Brazos',
						'abdomen' => 'Abdomen', 'genital' => 'Genital', 'gluteos' => 'Gluteos', 'muslos' => 'Muslos'
				),
				'multiple'=>true,
				'expanded' => true,				
		))
		->add('estrias', 'choice', array(
				'label' => 'Estrias:',
				'choices' => array(
						'rojas' => 'Rojas', 'grises' => 'Grises', 'blancas' => 'Blancas', 'hipotroficas' => 'Hipotroficas',
						'elevadas' => 'Elevadas', 'planas' => 'Planas'
				),
				'multiple'=>true,
				'expanded' => true,				
		))		
		->add('medicacion', 	'textarea', array('label' => 'Observacion:'))
		->add('dx_cut', 		'textarea', array('required' => false,'label' => 'Diagnostico cutaneo:'))
		
		->add('fitzpatrick', 'choice', array(
				'label' => 'Fitzpatrick:',
				'required' => true,
				'expanded' => true,
				'choices' => array('uno' => 'Uno', 'dos' => 'Dos', 'tres' => 'Tres',
								   'cuatro' => 'Cuatro', 'cinco' => 'Cinco', 'Seis' => 'Seis'),
				'data' => '1',
		))
		->add('infoFitzpatrick', 	'textarea', array('required' => false, 'label' => 'Nota:'))		
		->add('grafico', 			'textarea', array('required' => false, 'label' => 'Gráfico:'))
		
		;		
	}
	
	public function getName()
	{
		return 'newHcEstetica';
	}
}