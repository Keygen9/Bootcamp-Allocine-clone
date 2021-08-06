<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                // Libellé => valeur
                'choices' => [
                    'Membre' => 'ROLE_USER',
                    'Manager' => 'ROLE_MANAGER',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                // $roles = array = multiple
                'multiple' => true,
                // checkboxes
                'expanded' => true,
            ])
            // @link https://symfony.com/doc/current/form/events.html#event-listeners
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                // On récupère le form
                // => en effet on est dans une fonction,
                // donc pas accès à la variable $form qui se situe au-dessus
                $form = $event->getForm();

                // On récupère le user
                $user = $event->getData();

                // Si le user n'a pas id, c'est qu'il n'a jamais été "persisté"
                if($user->getId() === null) {

                    // Si nouveau user
                    $form->add('password', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'invalid_message' => 'Les mots de passe ne correspondent pas.',
                        //'required' => true,
                        'first_options'  => [
                            'constraints' => new NotBlank(),
                            'label' => 'Mot de passe',
                            'help' => 'Minimum eight characters, at least one letter, one number and one special character.'
                        ],
                        'second_options' => ['label' => 'Répéter le mot de passe'],
                    ]);

                } else {

                    // Si user existant
                    $form->add('password', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'invalid_message' => 'Les mots de passe ne correspondent pas.',
                        // Si besoin de remplacer un "null" par une valeur, on peut utiliser
                        // @link https://symfony.com/doc/current/reference/forms/types/password.html#empty-data
                        //'empty_data' => '',
                        // @link https://symfony.com/doc/current/reference/forms/types/password.html#mapped
                        'mapped' => false,
                        //'required' => true,
                        'first_options'  => [
                            'constraints' => [
                                new Regex('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&-\/])[A-Za-z\d@$!%*#?&-\/]{8,}$/'),
                                new NotCompromisedPassword(),
                            ],
                            'attr' => [
                                'placeholder' => 'Laissez vide si inchangé...',
                            ],
                            'label' => 'Mot de passe',
                            'help' => 'Minimum eight characters, at least one letter, one number and one special character.'
                        ],
                        'second_options' => ['label' => 'Répéter le mot de passe'],
                    ]);
                }
            })
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}