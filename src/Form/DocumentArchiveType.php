<?php

namespace App\Form;

use App\Entity\DocumentArchive;
use App\Entity\DocumentTypeActe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentArchiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        /*->add('document', EntityType::class, [
            'required' => false,
            'label' => false,
            'class' => DocumentTypeActe::class,
            'choice_label' => 'libelle',
            'attr'=>['class' =>'form-control has-select2']
        ])*/
        ->add('libDocument', null, ['label' => false, 'empty_data' => '', 'attr' => ['class' => 'lib-document']])
        ->add('fichier', FichierType::class, ['label' => 'Fichier', 'label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentArchive::class,
            'doc_required' => true
        ]);

        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
        
    }
}
