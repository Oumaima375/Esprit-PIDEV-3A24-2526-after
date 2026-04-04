<?php

namespace App\Form;

use App\Entity\CategorieDocument;
use App\Entity\Document;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomDocument', TextType::class)
            ->add('cheminFichier', TextType::class)
            ->add('dateAjout', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('dateExpiration', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieDocument::class,
                'choice_label' => 'libelle',
                'choice_value' => 'idCategorie',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}