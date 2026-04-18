<?php

namespace App\Form;

use App\Entity\Offre;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Original fields ─────────────────────────────
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'offre',
                'attr'  => ['class' => 'form-control', 'placeholder' => 'Ex: Séjour Spa Luxe 5 étoiles'],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (€)',
                'attr'  => ['class' => 'form-control', 'min' => 10, 'placeholder' => '299'],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (jours)',
                'attr'  => ['class' => 'form-control', 'min' => 1, 'placeholder' => '7'],
            ])
            ->add('service', EntityType::class, [
                'class'        => Service::class,
                'choice_label' => 'titre',
                'label'        => 'Service',
                'attr'         => ['class' => 'form-select'],
            ])

            // ── NEW: destination for weather + map ──────────
            ->add('destination', TextType::class, [
                'label'    => '📍 Destination (ville)',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Paris, Tunis, Tokyo…',
                    'id'          => 'field_destination',
                ],
                'help' => 'Utilisée pour afficher la météo et la carte.',
            ])

            // ── NEW: geo coordinates (auto-filled by JS) ────
            ->add('latitude', NumberType::class, [
                'label'    => 'Latitude',
                'required' => false,
                'scale'    => 6,
                'attr'     => [
                    'class' => 'form-control',
                    'id'    => 'field_latitude',
                    'step'  => 'any',
                    'placeholder' => 'Auto-rempli',
                ],
            ])
            ->add('longitude', NumberType::class, [
                'label'    => 'Longitude',
                'required' => false,
                'scale'    => 6,
                'attr'     => [
                    'class' => 'form-control',
                    'id'    => 'field_longitude',
                    'step'  => 'any',
                    'placeholder' => 'Auto-rempli',
                ],
            ])

            // ── NEW: availability calendar ──────────────────
            ->add('dateDebut', DateType::class, [
                'label'    => '📅 Date de début',
                'widget'   => 'single_text',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
                'help'     => 'Laisser vide si l\'offre est disponible immédiatement.',
            ])
            ->add('dateFin', DateType::class, [
                'label'    => '📅 Date de fin',
                'widget'   => 'single_text',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
                'help'     => 'Laisser vide si pas d\'expiration.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}