<?php

namespace App\Form;

use App\Entity\Identification;
use App\Entity\Client;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdentificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          
            ->add('acheteur', EntityType::class, [
                'required' => false,
                'class' => Client::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.id', 'DESC');
                },
                'label' => false,
                'placeholder' => "Veuillez selectionner un acheteur",
                'choice_label' => function ($client) {
                    if ($client->getRaisonSocial() == "") {
                        return $client->getNom() . ' ' . $client->getPrenom();
                    } else {

                        return $client->getRaisonSocial();
                    }
                },
                'attr'=>['class' =>'form-control has-select2']

            ])
            ->add('vendeur', EntityType::class, [
                'required' => false,
                'class' => Client::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.id', 'DESC');
                },
                'label' => false,
                'placeholder' => 'Veuillez selectionner un vendeur',
                'choice_label' => function ($client) {
                    if ($client->getRaisonSocial() == "") {
                        return $client->getNom() . ' ' . $client->getPrenom();
                    } else {

                        return $client->getRaisonSocial();
                    }
                },
                'attr'=>['class' =>'form-control has-select2']

            ])
        ;


       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Identification::class,
        ]);
    }
}
