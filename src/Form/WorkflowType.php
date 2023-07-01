<?php

namespace App\Form;

use App\Entity\Type;
use App\Entity\TypeClient;
use App\Entity\Workflow;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroEtape',IntegerType::class,[
                'label' => false,
                'attr' => ['class' => 'numero-etape']
            ])
            ->add('libelleEtape',TextType::class,[
                'label' => false,
            ])
            ->add('nombreJours',IntegerType::class,[
                'label' => false,
            ])
            //->add('propriete', null, ['label' => 'Propriété', 'empty_data' => ''])
            //->add('route', null, ['label' => 'Route', 'empty_data' => ''])
           /* ->add('typeActe',EntityType::class, [
                'required' => true,
                'class' => Type::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.active = :val')
                        ->setParameter('val', 1)
                        ->orderBy('u.titre', 'DESC');
                },
                'label' => false,
                'choice_label' => 'titre',

            ])*/
           /* ->add('gestionWorkflow')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workflow::class,
        ]);
    }
}
