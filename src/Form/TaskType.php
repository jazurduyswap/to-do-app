<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Task;
use App\Form\TagType;
use App\Form\SubType;
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
                'entry_type' => TagType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
            ])
            ->add('childTasks', CollectionType::class, [
                'entry_type' => SubTaskType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
