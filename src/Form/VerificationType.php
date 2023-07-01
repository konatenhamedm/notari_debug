<?php

namespace App\Form;

use App\Entity\DocumentSigne;
use App\Entity\DocumentTypeActe;
use App\Entity\Verification;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          

            ->add('libDocument', null, ['label' => false, 'empty_data' => '', 'attr' => ['class' => 'lib-document','placeholder'=>'Libelle']])
            ->add('fichierReponse', FichierType::class, ['label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
            ->add('fichierDemande', FichierType::class, ['label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
            ->add('dateEnvoi', DateType::class, [
                'label' => false
                , 'html5' => false
                , 'attr' => ['class' => 'no-auto skip-init has-datepicker']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => ''
            ])
            ->add('dateReponse', DateType::class, [
                'label' => false
                , 'html5' => false
                , 'attr' => ['class' => 'no-auto skip-init has-datepicker']
                , 'widget' => 'single_text'
                , 'format' => 'dd/MM/yyyy'
                , 'empty_data' => ''
            ])
           /* ->add('dossier')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Verification::class,
            'doc_required' => true,
        ]);

        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
