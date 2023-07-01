<?php

namespace App\Form;

use App\Entity\Redaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RedactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numVersion', IntegerType::class, ['label' => false, 'attr' => ['class' => 'num-version']])
            ->add('date', DateType::class, [
                'label' => false
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init','autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => date('d/m/Y')
            ])
            ->add('fichier', FichierType::class, ['label' => 'Fichier', 'label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Redaction::class,
            'doc_required' => true
        ]);

        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
