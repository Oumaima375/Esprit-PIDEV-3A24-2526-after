<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis.']),
                    new Length(['max' => 100]),
                ],
                'attr' => ['placeholder' => 'Dupont'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est requis.']),
                    new Length(['max' => 100]),
                ],
                'attr' => ['placeholder' => 'Jean'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'invalid_message' => 'Le type ou le format de l\'email n\'est pas valide : utilisez une adresse avec un domaine correct (ex. contact@societe.fr).',
                'constraints' => [
                    new NotBlank(['message' => "L'email est requis."]),
                ],
                'attr' => ['placeholder' => 'jean.dupont@email.com'],
            ])
            ->add('password', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,   // handled manually in controller
                'required'       => !$isEdit, // optional on edit
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr'  => ['placeholder' => $isEdit ? 'Laisser vide pour ne pas changer' : ''],
                    'constraints' => $isEdit ? [] : [
                        new NotBlank(['message' => 'Le mot de passe est requis.']),
                        new Length(['min' => 6, 'minMessage' => 'Minimum {{ limit }} caractères.']),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [
                    new NotBlank(['message' => 'Le téléphone est requis.']),
                    new Length(['max' => 20]),
                ],
                'attr' => ['placeholder' => '+33 6 00 00 00 00'],
            ])
            ->add('photo_profil', FileType::class, [
                'label'    => 'Photo de profil',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'          => '2M',
                        'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Formats acceptés : JPG, PNG, WEBP.',
                    ]),
                ],
            ])
            ->add('type_utilisateur', ChoiceType::class, [
                'label'   => 'Type d\'utilisateur',
                'choices' => [
                    'Administrateur' => 'admin',
                    'Voyageur'       => 'voyageur',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'is_edit'    => false,
        ]);
    }
}