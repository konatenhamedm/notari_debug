<?php

namespace App\Form;

use App\Entity\Enregistrement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EnregistrementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero',TextType::class,[
                'label'=> false,
                'empty_data' => ''
            ])
            ->add('fichier', FichierType::class, ['label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
            ->add('date', DateType::class, [
                'label' => false
                , 'html5' => false
                , 'attr' => ['class' => 'no-auto skip-init has-datepicker']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => ''
            ])
            ->add('sens', ChoiceType::class, ['choices' => array_flip(Enregistrement::SENS), 'attr' => ['class' => 'sens']])
           
           /* ->add('dossier')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enregistrement::class,
            'doc_required' => true
        ]);
        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
