<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'activité',
                'attr' => ['placeholder' => 'Ex: Randonnée en montagne'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le nom doit contenir au moins 5 caractères'
                    ])
                ]
            ])
       ->add('description', TextareaType::class, [
    'label' => 'Description',
    'required' => false,
    'attr' => ['placeholder' => 'Décrivez l\'activité...', 'rows' => 4],
    'constraints' => [
        new Length([
            'min' => 10,
            'minMessage' => 'La description doit contenir au moins 10 caractères',
            'max' => 500,
            'maxMessage' => 'La description ne peut pas dépasser 500 caractères'
        ])
    ]
])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Sport, Culture, Loisir...'],
                'constraints' => [
                    new NotBlank(['message' => 'La catégorie est obligatoire'])
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Tunis, Hammamet...'],
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu est obligatoire'])
                ]
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (TND)',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: 50.00'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire']),
                    new Positive(['message' => 'Le prix doit être un nombre positif'])
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de l\'activité',
                'mapped' => false,
                'required' => false,
                'attr' => [
                'accept' => 'image/*',
                 'class' => 'form-control'
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary rounded-pill py-2 px-4'],
            ])
        ;
    }


    
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Activite::class,
        'csrf_protection' => false,
    ]);
}
}