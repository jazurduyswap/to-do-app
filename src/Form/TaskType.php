<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\Usuarios;
use App\Form\TagNameType;
use App\Form\SubTaskNameType;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo')
            // EntityType solo permite una asociasion directa, para poder CRUD usar CollectionType
            ->add('parentTask', EntityType::class, [
                'class' => Task::class,
                'choice_label' => 'titulo', // o 'id' si no hay título
                'placeholder' => 'Sin tarea padre',
                'required' => false,
            ])
            ->add('tags', CollectionType::class, [
                'entry_type' => TagNameType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('tagsExistentes', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'label' => 'Seleccionar tags existentes',
            ])
            ->add('childTasks', CollectionType::class, [
                'entry_type' => SubTaskNameType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);

        // Solo mostrar el campo usuario si la opción is_admin es true
        if (!empty($options['is_admin'])) {
            $builder->add('usuario', EntityType::class, [
                'class' => Usuarios::class,
                'choice_label' => 'email',
                'required' => false,
                'label' => 'Asignar a usuario',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'is_admin' => false,
        ]);
    }
}
