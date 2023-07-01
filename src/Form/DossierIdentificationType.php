<?php

namespace App\Form;

use App\Entity\Dossier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierIdentificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('identifications', CollectionType::class, [
            'entry_type' => IdentificationType::class,
            'entry_options' => [
                'label' => false
            ],
            'allow_add' => true,
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'prototype' => true,

        ]);

        $builder->add('next', SubmitType::class, ['label' => 'Etape Suivante', 'attr' => ['class' => 'btn btn-primary btn-ajax']]);
        $builder->add('save', SubmitType::class, ['label' => 'Sauvegarder', 'attr' => ['class' => 'btn btn-success btn-ajax']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dossier::class,
            'method' => 'POST'
        ]);
    }
}
