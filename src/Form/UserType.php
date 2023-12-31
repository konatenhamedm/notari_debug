<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class)
            ->add('prenoms')
            ->add('email',EmailType::class,[
                'attr' =>[
                    'placeholder' => 'Merci de saisir votre adresse email'
                ]

                ]
               )
            ->add('password', RepeatedType::class,
                [
                    'type'            => PasswordType::class,
                    'invalid_message' => 'Les mots de passe doivent être identiques.',
                    'required'        => $options['passwordRequired'],
                    'first_options'   => ['label' => 'Mot de passe'],
                    'second_options'  => ['label' => 'Répétez le mot de passe'],
                ]
            )

            ->add('roles', ChoiceType::class,
                [
                    'placeholder' => 'Choisir un role',
                    'label' => 'Privilèges Supplémentaires',
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2'],
                    'multiple' => true,
                    'choices'  => array_flip([
                        'ROLE_ADMIN' => 'Administrateur',
                        'ROLE_USER' => 'Collaborateurs'
                    ]),
                ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'passwordRequired' => false
        ]);
        $resolver->setRequired('passwordRequired');
    }
}
