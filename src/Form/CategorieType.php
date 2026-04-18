<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCategorie', TextType::class, [
                'label'       => 'Nom de la catégorie',
                'attr'        => ['placeholder' => 'Ex : Transport, Hébergement…'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Length(['max' => 100, 'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['placeholder' => 'Courte description (facultatif)', 'rows' => 3],
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('iconeUrl', TextType::class, [
                'label'    => 'URL de l\'icône',
                'required' => false,
                'attr'     => ['placeholder' => 'https://… ou laissez vide'],
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Categorie::class]);
    }
}