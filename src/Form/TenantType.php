<?php

namespace App\Form;

use App\Entity\Tenant;
use App\Entity\Property;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TenantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom complet',
                'attr'  => ['placeholder' => 'Prénom Nom'],
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
                'attr'  => ['placeholder' => 'locataire@email.fr'],
            ])
            ->add('phone', null, [
                'label'    => 'Téléphone',
                'required' => false,
                'attr'     => ['placeholder' => '06 00 00 00 00'],
            ])
            ->add('property', EntityType::class, [
                'class'        => Property::class,
                'choice_label' => 'title',
                'label'        => 'Bien associé',
                'required'     => false,
                'placeholder'  => '— Aucun bien —',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tenant::class,
        ]);
    }
}
