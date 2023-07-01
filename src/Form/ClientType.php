<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Departement;
use App\Entity\TypeClient;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event)
            {
                //$departement = $event->getData()['departement'] ?? null;
                $user = $event->getData();
            //dd($user->getSituation());
              /*  if ($user->getSituation() == "CELIBATAIRE"){
                    $event->getForm()->remove('emailConjoint');
                }*/
               /* $event->getForm()->add('departement',EntityType::class,[
                    'class'=>Departement::class,
                    'choice_label'=>'libDepartement',
                    'choices'=>$departement,
                    'disabled'=>false,
                    'placeholder'=>'Selectionnez un village',
                    'constraints'=>new NotBlank(['message'=>'Selectionnez un village']),
                ])   ;*/
            })

            ->add('type_client',EntityType::class, [
                'required' => true,
                'class' => TypeClient::class,
                'choice_attr' => function ( TypeClient $typeClient) {
                    return ['data-code' => $typeClient->getCode()];
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.titre', 'DESC');
                },
                'label' => 'Type de client',
                'attr'=>['class' =>'form-control has-select2'],
                'choice_label' => 'titre',

            ])
            ->add('boitePostal',TextType::class,[
                'required'=>false,
                'label' => 'Boite Postale'
            ])
            ->add('local',TextareaType::class,[
                'required'=>false,
                'label' => 'Localisation'
            ])
            ->add('raisonSocial',TextType::class,[
                'required'=>false,
                'label' => 'Raison Sociale'
            ])
            ->add('registreCommercial',TextType::class,[
                'required'=>false,
                'label' => 'RCCM'
            ])

            ->add('siteWeb',UrlType::class,[
                'required'=>false,
                'label' => 'Site wEB'
            ])
            ->add('nom',TextType::class,[
                'required'=>false,
                'label' => 'Nom'
            ])

           
            ->add('emailConjoint',EmailType::class,[
                'required'=>false,
                'label' => 'Email Conjoint'
            ])
            ->add('emailEntreprise',EmailType::class,[
                'required'=>false,
                'label' => 'Email (Entreprise)'
            ])
            ->add('prenom',TextType::class,[
                'required'=>false,
                'label' => 'Prénoms'
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance'
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => date('d/m/Y')
            ])
            ->add('lieuNaissance',TextType::class,[
                'required'=>false,
                'label' => 'Lieu de naissance'
            ])
            ->add('profession',TextType::class,[
                'required'=>false,
                'label' => 'Profession'
            ])
            ->add('domicile',TextType::class,[
                'required'=>false,
                'label' => 'Domicile'
            ])
            ->add('photo', FichierType::class, ['label' => 'Photo',  'doc_options' => $options['photo']['doc_options'], 'required' => false])
            ->add('pere',TextType::class,[
                'required'=>false,
                'label' => 'Père'
            ])
            ->add('mere',TextType::class,[
                'required'=>false,
                'label' => 'Mère'
            ])

            ->add('adresse',TextType::class,[
                'required'=>false,
                'label' => 'Adresse'
            ])
            ->add('telDomicile',TextType::class,[
                'required'=>false,
                'label' => 'Tel Domicile'
            ])
            ->add('telBureau',TextType::class,[
                'required'=>false,
                'label' => 'Tel Bureau'
            ])
            ->add('telPortable',TextType::class,[
                'required'=>false,
                'label' => 'Tel Portable'
            ])
            ->add('email',EmailType::class,[
                'required'=>false,
                'label' => 'Adresse mail'
            ])
            ->add('nationalite',TextType::class,[
                'required'=>false,
                'label' => 'Nationalité'
            ])
            ->add('situation', ChoiceType::class,
                [
                    'label' => 'Situation',
                    'expanded' => false,
                   /* 'placeholder' => 'Situation',*/
                    'required' => true,
                    // 'attr' => ['class' => 'select2_multiple'],
                    'multiple' => false,
                    //'choices_as_values' => true,

                    'choices' => array_flip([
                        'CELIBATAIRE' => 'CELIBATAIRE',
                        'EPOUX' => 'EPOUX (SE)',
                        'VEUF' => 'VEUF (VE)',
                        'DIVORCE' => 'DIVORCE (E)',

                    ]),
                    'attr'=>['class' =>'form-control has-select2']
                ])

            ->add('etat', ChoiceType::class,
                [
                    'expanded' => false,
                    /* 'placeholder' => 'Situation',*/
                    'required' => true,
                    // 'attr' => ['class' => 'select2_multiple'],
                    'multiple' => false,
                    //'choices_as_values' => true,

                    'choices' => array_flip([
                        'DIVORCE' => 'EN CAS DE DIVORCE ',
                        'DECES' => 'EN CAS DE DECES DU CONJOINT'
                    ]),
                    'attr'=>['class' =>'form-control has-select2']
                ])
            ->add('nomConjoint',TextType::class,[
                'required'=>false,
                'label' => 'Nom du conjoint'
            ])
            ->add('prenomConjoint', null, ['label' => 'Prénoms Conjoint'])
            ->add('telConjoint', null, ['label' => 'Téléphone Conjoint'])
            ->add('portableConjoint', null, ['label' => 'Portable Conjoint'])
            ->add('dateNaissanceConjoint', DateType::class, [
                'label' => 'Date de naissance Conjoint'
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => date('d/m/Y')
            ])
            ->add('lieuNaissanceConjoint', null, ['label' => 'Nom/Prénom Epoux ou Epouse'])
            ->add('professionConjoint', null, ['label' => 'Profession du conjoint'])
            ->add('pereConjoint', null, ['label' => 'Père du conjoint'])
            ->add('mereConjoint', null, ['label' => 'Mère du conjoint'])
            ->add('adresseConjoint', null, ['label' => 'Adresse Conjoint'])
            ->add('nationaliteConjoint', null, ['label' => 'Nationalité Conjoint'])
            ->add('regimeMatrimonialConjoint')
            ->add('dateMariage', DateType::class, [
                'label' => 'Date de mariage'
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => date('d/m/Y')
            ])
            ->add('lieuMariageConjoint', null, ['label' => 'Lieu de mariage'])
            ->add('contratMariageConjoint', ChoiceType::class,
                [
                    'expanded' => false,
                    'placeholder' => '',
                    'required' => false,
                    // 'attr' => ['class' => 'select2_multiple'],
                    'multiple' => false,
                    //'choices_as_values' => true,
                    'choices' => [
                        'OUI' => 'OUI',
                        'NON' => 'NON',


                    ],
                ])
            ->add('affirmatif')
            ->add('precedentMariage', ChoiceType::class,
                [
                    'expanded' => false,
                    'label' => 'Mariage précédent',
                    'placeholder' => '',
                    'required' => false,
                    // 'attr' => ['class' => 'select2_multiple'],
                    'multiple' => false,
                    //'choices_as_values' => true,

                    'choices' => array_flip([
                        'OUI' => 'OUI',
                        'NON' => 'NON',


                    ]),
                ])
            ->add('nomPrenomEpoux', null, ['label' => 'Nom/Prénom Epoux ou Epouse'])
            ->add('datePrecedent', DateType::class, [
                'label' => 'Date mariage Précédent'
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => ''
            ])
            ->add('regime', null, ['label' => 'Régime Matrimonial'])
            ->add('numeroJugement', null, ['label' => 'Numéro Jugement'])
            ->add('dateJugement', DateType::class, [
                'label' => 'Date jugement'
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => ''
            ])
            ->add('jugementRendu', null, ['label' => 'Jugement rendu'])
            ->add('dateDeces', DateType::class, [
                'label' => 'Date décès'
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => ''
            ])
            ->add('lieuDeces', null, ['label' => 'Lieu de décès']);


            $builder->add('documents', CollectionType::class, [
                'entry_type' => DocumentClientType::class,
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
            'data_class' => Client::class,
            'doc_required' => false,
            'doc_options' => [],
        ]);

        $resolver->setRequired('doc_required');
        $resolver->setRequired('doc_options');
        $resolver->setRequired('photo');
    }
}
