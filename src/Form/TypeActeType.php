<?php

namespace App\Form;

use App\Entity\Type;
use App\Form\DocumentTypeActeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TypeActeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', null, ['label' => 'Code'])
            ->add('titre', null, ['label' => 'Titre'])
            ->add('documentTypeActes', CollectionType::class, [
                'entry_type' => DocumentTypeActeType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__document__',

            ])
            ->add('workflows', CollectionType::class, [
                'entry_type' => WorkflowType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'prototype_name' => '__workflow__',
                'prototype' => true,

            ])
        ;


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();
        
            if (!array_key_exists('workflows', $submittedData)) {
                return;
            }
        
            //Re-index the array to ensure the forms stay in the submitted order.
            $submittedData['workflows'] = array_values($submittedData['workflows']);

            if (array_key_exists('documentTypeActes', $submittedData)) {
                $submittedData['documentTypeActes'] = array_values($submittedData['documentTypeActes']);
            }   
           
        
            $event->setData($submittedData);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
        ]);
    }
}
