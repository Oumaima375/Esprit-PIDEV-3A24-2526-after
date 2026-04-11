<?php

namespace App\Form;

use App\Entity\CategorieDocument;
use App\Entity\Document;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomDocument', TextType::class, [
                'label' => 'Nom du document',
                'attr'  => ['placeholder' => 'Ex: Passeport, Visa France...', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du document est obligatoire.']),
                    new Length([
                        'min'        => 3,
                        'max'        => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('fichier', FileType::class, [
                'label'    => 'Fichier du document',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize'          => '5M',
                        'maxSizeMessage'   => 'Le fichier ne doit pas dépasser 5 Mo.',
                        'mimeTypes'        => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Formats acceptés : PDF, JPG, PNG uniquement.',
                    ]),
                ],
            ])
            ->add('dateAjout', DateType::class, [
                'widget' => 'single_text',
                'label'  => "Date d'ajout",
                'attr'   => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => "La date d'ajout est obligatoire."]),
                    new GreaterThanOrEqual([
                        'value'   => new \DateTime('today'),
                        'message' => "La date d'ajout ne peut pas être dans le passé.",
                    ]),
                ],
            ])
            ->add('dateExpiration', DateType::class, [
                'widget'   => 'single_text',
                'required' => false,
                'label'    => "Date d'expiration",
                'attr'     => ['class' => 'form-control'],
            ])
            ->add('categorie', EntityType::class, [
                'class'        => CategorieDocument::class,
                'choice_label' => 'libelle',
                'choice_value' => 'idCategorie',
                'label'        => 'Catégorie',
                'placeholder'  => '🤖 Laisser vide pour détection Gemini AI',
                'required'     => false,
                'attr'         => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Document::class]);
    }
}