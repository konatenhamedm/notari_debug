<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\DocumentTypeActe;
use App\Entity\Piece;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PieceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', CheckboxType::class, ['required' => false, 'attr' => ['class' => 'ck-client']])
            ->add('document', EntityType::class, [
                'required' => false,
                'placeholder' => '---',
                'label' => false,
                'class' => DocumentTypeActe::class,
                'choice_label' => 'libelle',
                'attr'=>['class' =>'form-control has-select2']
            ])
            ->add('libDocument', null, ['label' => false, 'empty_data' => '', 'attr' => ['class' => 'lib-document']])
            ->add('fichier', FichierType::class, ['label' => 'Fichier', 'label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
            ->add('dateTransmission', DateType::class, [
                'label' => false
                , 'html5' => false
                , 'attr' => ['class' => 'has-datepicker no-auto skip-init', 'autocomplete' => 'off']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => date('d/m/Y')
            ])
            ->add('origine', ChoiceType::class, ['choices' => Piece::ORIGINES, 'attr' => ['class' => 'origine']])
            
        ;


        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Piece::class,
            'doc_required' => true
        ]);


        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
