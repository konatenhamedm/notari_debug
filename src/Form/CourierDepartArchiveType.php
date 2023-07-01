<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\CourierArrive;
use App\Entity\Dossier;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CourierDepartArchiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('numero',IntegerType::class,[
                "required" => false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs numero")
                )
            ])

            ->add('objet', TextType::class,[
                'required'=>false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs objet")
                )
            ])


            ;
        $builder->add('documentCourriers', CollectionType::class, [
            'entry_type' => DocumentCourrierType::class,
            'entry_options' => [
                'label' => false,
                'doc_options' => $options['doc_options'],
                'doc_required' => $options['doc_required']
            ],
            'allow_add' => true,
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'prototype' => true,

        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $submittedData = $event->getData();

            if($event->getData()->getDossier() == null){
                $event->getForm()->add('affaire', TextType::class,[
                    'mapped'=>false,
                    'attr' => ['readonly' => true]
                ]);

            }else{
                $event->getForm()->add('dossier', EntityType::class, [
                    'required' => false,

                    'class' => Dossier::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.numeroOuverture', 'DESC');
                    },

                    'placeholder' => "Selectionner un dossier",
                    'choice_label' => function ($dossier) {
                        return $dossier->getNumeroOuverture() . ' | ' . $dossier->getObjet();
                    },
                    'attr' => ['class' => 'form-control has-select2','readonly' => true]

                ]);
            }
//dd($event->getData()->getDossier());



            //$event->setData($submittedData);
        });


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CourierArrive::class,
            'doc_required' => false,
            'doc_options' => [],
        ]);
        $resolver->setRequired('doc_required');
        $resolver->setRequired('doc_options');
    }
}
