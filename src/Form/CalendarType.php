<?php

namespace App\Form;

use App\Entity\Calendar;
use App\Entity\Client;
use App\Entity\Dossier;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('nbreJours',IntegerType::class,[
                'required' => false,
                'attr' => ['class' => 'quantite','step'=>1,'min'=>0],
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
                'attr' => ['class' => 'form-control select2', 'id' => 'validationCustom05']

            ])
            ->add('client', EntityType::class, [
                'required' => true,
                'class' => Client::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'DESC');
                },
               /* 'label' => 'Réceptionné par',*/
                'placeholder' => "Selectionner le client",
                'choice_label' => function ($client) {

                    if ($client->getRaisonSocial() == "") {
                        return $client->getNom() . ' ' . $client->getPrenom();
                    } else {

                        return $client->getRaisonSocial();
                    }

                },
                'attr' => ['class' => 'form-control has-select2', 'id' => 'validationCustom05']

            ])
            ->add('start', DateTimeType::class, [
                "required" => false,
                'label'=>'Date début',
                "widget" => 'single_text',
                "input_format" => 'Y-m-d',
                "by_reference" => true,
                "empty_data" => '',
            ])
            ->add('end', DateTimeType::class, [
                "required" => false,
                'label'=>'Date fin',
                "widget" => 'single_text',
                "input_format" => 'Y-m-d',
                "by_reference" => true,
                "empty_data" => '',
            ])
            ->add('description', TextType::class)
           /* ->add('all_day', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('background_color', ColorType::class)
            ->add('border_color', ColorType::class)*/
            /*->add('submit',SubmitType::class)*/;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Calendar::class,
        ]);
    }
}
