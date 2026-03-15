<?php

namespace App\Form;

use App\Entity\TenantFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class TenantFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', ChoiceType::class, [
                'label'   => 'Categorie',
                'choices' => array_flip(TenantFile::CATEGORIES),
            ])
            ->add('file', FileType::class, [
                'label'    => 'Fichier (PDF, image)',
                'mapped'   => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Veuillez selectionner un fichier.'),
                    new File([
                        'maxSize'          => '10M',
                        'mimeTypes'        => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Formats acceptes : PDF, JPEG, PNG, WEBP.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => TenantFile::class]);
    }
}
