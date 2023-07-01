<?php

namespace App\Form;

use App\Entity\InfoClassification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InfoClassificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', null, ['label' => 'Numéro de classification'])
            ->add('date', DateType::class, [
                'label' => false
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => date('d/m/Y')
                , 'label' => 'Date de classification'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InfoClassification::class,
        ]);
    }
}
