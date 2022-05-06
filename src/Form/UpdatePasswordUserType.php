<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdatePasswordUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $htmlAttr = [
            'minlength' => 6,
        ];
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'constraints' => [
                new NotBlank(['normalizer' => 'trim']),
                new Length([
                    'min' => 6,
                    'max' => 255,
                ]),
            ],
            'first_options' => ['label' => 'Nouveau mot de passe', 'attr' => [...$htmlAttr, ...['placeholder' => false]]],
            'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => [...$htmlAttr, ...['placeholder' => false]]],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms'
        ]);
    }
}
