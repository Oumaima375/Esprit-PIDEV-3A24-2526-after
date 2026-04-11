<?php

namespace App\Form;

use App\Entity\Planning;
use App\Entity\Activite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('activite', EntityType::class, [
                'class' => Activite::class,
                'choice_label' => function(Activite $a) {
                    return $a->getNom() . ' - ' . $a->getLieu();
                },
                'label' => 'Activité',
            ])
            ->add('date_activite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => false,
                'input' => 'datetime',
            ])
            ->add('heure_debut', TextType::class, [
                'label' => 'Heure de début',
                'attr' => ['placeholder' => 'HH:MM'],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)',
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
            'data_class' => Planning::class,
        ]);
    }
}