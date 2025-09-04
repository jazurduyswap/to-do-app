<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título de la Subtarea',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa el título de la subtarea',
                    'maxlength' => 255
                ]
            ])
            ->add('tags', CollectionType::class, [
                'entry_type' => TagNameType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Tags de la Subtarea',
                'attr' => [
                    'data-controller' => 'collection',
                    'data-collection-add-label-value' => 'Agregar Tag'
                ]
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
