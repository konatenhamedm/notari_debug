<?php

namespace App\Form;

use App\Entity\DocumentClient;
use App\Entity\DocumentTypeActe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('docHash', HiddenType::class, ['attr' => ['class' => 'row-hash']])
            ->add('fichier', FichierType::class, ['label' => 'Fichier', 'label' => false, 'doc_options' => $options['doc_options'], 'required' => $options['doc_required'] ?? true])
           ->add('libelle', null, ['label' => false, 'empty_data' => ''])
            ->add('document', EntityType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => '---',
                'class' => DocumentTypeActe::class,
                'choice_label' => 'libelle',
                'attr'=>['class' =>'form-control has-select2']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentClient::class,
            'doc_required' => true
        ]);

        $resolver->setRequired('doc_options');
        $resolver->setRequired('doc_required');
    }
}
