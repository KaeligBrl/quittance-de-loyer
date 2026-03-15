<?php

namespace App\Form;

use App\Entity\Payment;
use App\Entity\RentReceipt;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label'    => 'Montant',
                'currency' => 'EUR',
            ])
            ->add('paidAt', DateType::class, [
                'label'  => 'Date de paiement',
                'widget' => 'single_text',
                'data'   => new \DateTime(),
            ])
            ->add('method', ChoiceType::class, [
                'label'   => 'Mode de paiement',
                'choices' => [
                    'Virement bancaire'     => 'virement',
                    'Chèque'                => 'cheque',
                    'Espèces'               => 'especes',
                    'Prélèvement automatique' => 'prelevement',
                    'Autre'                 => 'autre',
                ],
                'data' => 'virement',
            ])
            ->add('note', TextareaType::class, [
                'label'    => 'Note',
                'required' => false,
                'attr'     => ['rows' => 3],
            ])
            ->add('rentReceipt', EntityType::class, [
                'class'        => RentReceipt::class,
                'choice_label' => 'number',
                'label'        => 'Quittance associée',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
