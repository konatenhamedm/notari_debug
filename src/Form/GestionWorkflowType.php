<?php

namespace App\Form;

use App\Entity\GestionWorkflow;
use App\Entity\Icons;
use App\Entity\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestionWorkflowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('workflow', CollectionType::class, [
                    'entry_type' => WorkflowType::class,
                    'entry_options' => [
                        'label' => false
                    ],
                    'allow_add' => true,
                    'label' => false,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'prototype' => true,

                ])
           /* ->add('type',EntityType::class,[
                'class' => Type::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.id', 'DESC');
                },
                'label'=>false,
                'choice_label' => 'code',

            ])*/
            ->add('titre',TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GestionWorkflow::class,
        ]);
    }
}
