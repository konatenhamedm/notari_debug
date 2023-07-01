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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CourierInterneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('finalise', CheckboxType::class, [

                'label' => 'Finaliser le courrier',
                'required' => false,
            ])
            ->add('numero',IntegerType::class,[
                "required" => false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs numero")
                )
            ])

            ->add('dateEnvoi', DateType::Class, [
                "label" => false,
                "required" => false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs date")
                ),
                "widget" => 'single_text',
                "input_format" => 'Y-m-d',
                "by_reference" => true,
                "empty_data" => '',
            ])
            ->add('objet', TextType::class,[
                'required'=>false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs objet")
                )
            ])
            ->add('dossier', EntityType::class, [
                'required' => false,
                'class' => Dossier::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.numeroOuverture', 'DESC');
                },
                /* 'label' => 'Réceptionné par',*/
                'placeholder' => "Selectionner un dossier",
                'choice_label' => function ($client) {
                    return $client->getNumeroOuverture() . ' | ' . $client->getObjet();
                },
                'attr' => ['class' => 'form-control has-select2']

            ])
            ->add('courrier', TextareaType::class, ['empty_data' => '', 'attr' => ['class' => 'summernote', 'rows' => 15, 'data-height' => 300]])

            ->add('expediteur', TextType::class, [
                'required' => false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs expediteur")
                )
            ])
            ->add('user', EntityType::class, [
                'required' => false,
                "constraints"=>array(
                    new NotNull(null,"S'il vous veillez renseigner le champs destinataire")
                ),
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.id', 'DESC');
                },
                'label' => 'Destinataire',
                'placeholder' => 'Selectionner un destinataire',
                'choice_label' => function ($user) {
                    return $user->getNom() . ' ' . $user->getPrenoms();
                },
                'attr' => ['class' => 'form-control has-select2', 'id' => 'validationCustom05']

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
        $builder->add('documentReceptions', CollectionType::class, [
            'entry_type' => DocumentReceptionType::class,
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
