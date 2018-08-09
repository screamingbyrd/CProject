<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class VoterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('email',      EmailType::class, array(
                'required' => true,
                'label' => 'form.registration.email',
                'constraints' => array(
                    new EMail(),
                ),
            ))
            ->add('firstName',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.firstname',

                ))
            ->add('lastName',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.lastname'

            ))

            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array(
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => array(
                        'autocomplete' => 'new-password',
                    ),
                ),
                'constraints' => array(
                    new Length(array('min' => 6)),
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*).*$/'),
                ),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('terms',CheckboxType::class, array('mapped' => false,
                'label'    => 'form.registration.accept',
                'constraints' => array(new NotNull())))

            ->add('phone',      TextType::class, array(
                'required' => false,
                'label' => 'form.registration.phone',
            ))
            ->add('submit',      SubmitType::class, array(
                'attr' => array(
                    'class' => 'cproject-button login',
                )
            ))

            ->getForm()
        ;
    }/**
 * {@inheritdoc}
 */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Voter'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_voter';
    }


}
