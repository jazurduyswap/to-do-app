<?php

namespace App\Form;

use App\Entity\Grupo;
use App\Form\GrupoType;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UsuariosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nueva contraseña',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('email')
            ->add('grupos', EntityType::class, [
                'class' => Grupo::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,
        ]);
    }
}
