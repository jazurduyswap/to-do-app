<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class TaskEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título de la Tarea',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa el título de la tarea',
                    'maxlength' => 255
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El título de la tarea es obligatorio']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'El título debe tener al menos {{ limit }} caracteres',
                        'maxMessage' => 'El título no puede exceder {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('parentTask', EntityType::class, [
                'class' => Task::class,
                'choice_label' => 'titulo',
                'placeholder' => 'Sin tarea padre',
                'required' => false,
                'label' => 'Tarea Padre'
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Tags',
                'attr' => [
                    'class' => 'form-check-group'
                ],
                'choice_attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->add('usuario', EntityType::class, [
                'class' => Usuarios::class,
                'choice_label' => 'email',
                'required' => false,
                'label' => 'Asignar a usuario',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
