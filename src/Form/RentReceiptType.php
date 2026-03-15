<?php

namespace App\Form;

use App\Entity\RentReceipt;
use App\Entity\Tenant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tenant', EntityType::class, [
                'class'        => Tenant::class,
                'choice_label' => 'name',
                'label'        => 'Locataire',
                'placeholder'  => '— Choisir un locataire —',
            ])
            ->add('periodStart', DateType::class, [
                'label'  => 'Début de période',
                'widget' => 'single_text',
            ])
            ->add('periodEnd', DateType::class, [
                'label'  => 'Fin de période',
                'widget' => 'single_text',
            ])
            ->add('issuedAt', DateType::class, [
                'label'  => 'Date d\'émission',
                'widget' => 'single_text',
            ])
        ;

        if ($options['include_amount']) {
            $builder->add('amount', MoneyType::class, [
                'label'    => 'Montant (€)',
                'currency' => 'EUR',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'     => RentReceipt::class,
            'include_amount' => false,
        ]);
    }
}
