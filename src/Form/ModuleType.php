<?php

namespace App\Form;

use App\Entity\{Icons, Module, ModuleParent};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre')
            ->add('icon',EntityType::class,[
                'class' => Icons::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.id', 'DESC');
                },
                'choice_label' => 'code',

            ])
            ->add('ordre',ChoiceType::class,
                [
                    'expanded'     => false,
                    'placeholder' => 'Choisir un ordre',
                    'required'     => true,
                    'label'=>false,
                    /*   'attr' => ['class' => 'select2_multiple'],
                       'multiple' => true,*/
                    //'choices_as_values' => true,

                    'choices'  =>[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20],

                ])
            ->add('role', ChoiceType::class,
                [
                    'placeholder' => 'Choisir un role',
                    /*'label' => 'Privilèges Supplémentaires',*/
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2'],
                    'multiple' => false,
                    'choices'  => array_flip([
                        'ROLE_ADMIN' => 'Administrateur',
                        'ROLE_USER' => 'Collaborateurs'
                    ]),
                ])
            ->add('parent', EntityType::class, [
                'class' => ModuleParent::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.id', 'DESC');
                },
                'choice_label' => 'titre',

            ])
            ->add('groupes', CollectionType::class, [
                'entry_type' => GroupeType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype_name' => '__workflow__',
                'prototype' => true,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();

            if (!array_key_exists('groupes', $submittedData)) {
                return;
            }

            //Re-index the array to ensure the forms stay in the submitted order.
            $submittedData['groupes'] = array_values($submittedData['groupes']);

            /*if (array_key_exists('documentTypeActes', $submittedData)) {
                $submittedData['documentTypeActes'] = array_values($submittedData['documentTypeActes']);
            }*/


            $event->setData($submittedData);
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}
