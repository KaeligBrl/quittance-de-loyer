<?php

namespace App\Form;

use App\Entity\Property;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom du bien',
                'attr'  => ['placeholder' => 'ex: Appartement T3 Lyon'],
            ])
            ->add('type', ChoiceType::class, [
                'label'       => 'Type de bien',
                'choices'     => array_flip(Property::TYPES),
                'required'    => false,
                'placeholder' => '— Choisir —',
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr'  => ['placeholder' => '12 rue de la Paix'],
            ])
            ->add('zipcode', TextType::class, [
                'label'    => 'Code postal',
                'required' => false,
                'attr'     => ['placeholder' => '75001'],
            ])
            ->add('city', TextType::class, [
                'label'    => 'Ville',
                'required' => false,
                'attr'     => ['placeholder' => 'Paris'],
            ])
            ->add('surface', NumberType::class, [
                'label'    => 'Surface (m²)',
                'required' => false,
                'scale'    => 2,
                'attr'     => ['placeholder' => '45.00'],
            ])
            ->add('rentAmount', MoneyType::class, [
                'label'    => 'Loyer hors charges (€)',
                'required' => false,
                'currency' => 'EUR',
                'attr'     => ['placeholder' => '750.00'],
            ])
            ->add('chargesAmount', MoneyType::class, [
                'label'    => 'Charges (€)',
                'required' => false,
                'currency' => 'EUR',
                'attr'     => ['placeholder' => '50.00'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
}
