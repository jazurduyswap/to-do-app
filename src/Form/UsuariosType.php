<?php

namespace App\Form;

use App\Entity\Grupo;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuariosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de Usuario',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa el nombre de usuario',
                    'maxlength' => 180
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nueva contraseña',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa una nueva contraseña',
                    'minlength' => 6
                ],
                'help' => 'Deja en blanco si no quieres cambiar la contraseña'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo Electrónico',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ejemplo@correo.com',
                    'maxlength' => 180
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles del Usuario',
                'choices' => [
                    'Usuario' => 'ROLE_USER',
                    'Administrador' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'help' => 'ROLE_USER se asigna automáticamente a todos los usuarios',
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('grupos', EntityType::class, [
                'class' => Grupo::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Grupos',
                'help' => 'Selecciona los grupos a los que pertenece el usuario',
                'attr' => [
                    'class' => 'form-check-input'
                ]
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
