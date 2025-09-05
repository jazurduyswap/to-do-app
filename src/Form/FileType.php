<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\Task;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType as FormFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('uploadedFile', FormFileType::class, [
                'mapped' => false,
                'label' => 'Selecciona un archivo',
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '10M', # Limite de tamaño del archivo
                        'mimeTypesMessage' => 'Por favor, sube un archivo válido (PDF o Word).',
                        'mimeTypes' => [ # Tipos de archivos permitidos 
                            'application/pdf',
                            'application/x-pdf',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/msword',
                        ],
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
