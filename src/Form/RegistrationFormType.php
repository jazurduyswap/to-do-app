<?php

namespace App\Form;

use App\Entity\Usuarios;
use App\Entity\Grupo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de Usuario',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa tu nombre de usuario',
                    'maxlength' => 180
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor ingresa un nombre de usuario',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'El nombre debe tener al menos {{ limit }} caracteres',
                        'max' => 180,
                        'maxMessage' => 'El nombre no puede exceder los {{ limit }} caracteres',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo Electrónico',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ejemplo@correo.com',
                    'maxlength' => 180
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor ingresa tu correo electrónico',
                    ]),
                    new Email([
                        'message' => 'Por favor ingresa un correo electrónico válido',
                    ]),
                    new Length([
                        'max' => 180,
                        'maxMessage' => 'El correo no puede exceder los {{ limit }} caracteres',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Contraseña',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa una contraseña segura',
                    'minlength' => 6
                ],
                'help' => 'La contraseña debe tener al menos 6 caracteres',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor ingresa una contraseña',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('grupos', EntityType::class, [
                'class' => Grupo::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Grupos (Opcional)',
                'help' => 'Puedes seleccionar los grupos a los que quieres unirte',
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
