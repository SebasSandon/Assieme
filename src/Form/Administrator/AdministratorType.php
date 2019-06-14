<?php

namespace App\Form\Administrator;

use App\Entity\Administrator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdministratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'attr' => array('class' => 'form-control', 'title' => 'Ingrese el nombre de usuario'),
                'label' => 'Nombre de Usuario',
                'label_attr' => array('class' => 'control-label'),
            ))
            ->add('password', PasswordType::class, array(
                'attr' => array('class' => 'form-control', 'title' => 'Mínimo 6 y máximo 18 caracteres', 'pattern' => '.{6,18}'),
                'label' => 'Contraseña',
                'label_attr' => array('class' => 'control-label'),
            ))
            ->add('role', ChoiceType::class, array(
                'choices' => array(
                    'Administrador' => 'ROLE_ADMIN',
                    'Usuario' => 'ROLE_USER'
                ),
                'attr' => array('class' => 'form-control'),
                'label' => 'Rol',
                'label_attr' => array('class' => 'control-label'),
            ))
            ->add('email', EmailType::class, array(
                'required' => false,
                'attr' => array('class' => 'form-control'),
                'label' => 'Email',
                'label_attr' => array('class' => 'control-label'),
            ))
            ->add('name', TextType::class, array(
                'required' => false,
                'attr' => array('class' => 'form-control'),
                'label' => 'Nombre',
                'label_attr' => array('class' => 'control-label'),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Administrator::class,
        ]);
    }
}
