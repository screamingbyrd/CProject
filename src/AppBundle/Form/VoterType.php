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
            ->add('title',      TextType::class, array(
                'required' => false,
                'label' => 'form.registration.title.title'

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
            ->add('description',      TextareaType::class, array(
                'required' => false,
                'label' => 'form.registration.description'
                ))

            ->add('age',      TextType::class, array(
                'required' => false,
                'label' => 'voter.age'
            ))

            ->add('experience', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',
                'form.registration.exp2' => 'form.registration.exp2',
                'form.registration.exp3' => 'form.registration.exp3',
            ),
                'required' => false,
                'attr' => array('class' => 'select2'),
                'label' => 'offer.experience',
            ))
            ->add('language', ChoiceType::class, array('choices' => array(
                'language.fr' => 'language.fr',
                'language.en' => 'language.en',
                'language.de' => 'language.de',
                'language.lu' => 'language.lu',
            ),
                'multiple' => true,
                'required' => false,
                'attr' => array('class' => 'select2'),
                'label' => 'form.registration.language',
            ))
            ->add('license', ChoiceType::class, array('choices' => array(
                'form.registration.lis1' => 'form.registration.lis1',
                'form.registration.lis2' => 'form.registration.lis2',
                'form.registration.lis3' => 'form.registration.lis3',
                'form.registration.lis4' => 'form.registration.lis4',
                'form.registration.lis5' => 'form.registration.lis5',
                'form.registration.lis6' => 'form.registration.lis6',
                'form.registration.lis7' => 'form.registration.lis7',
                'form.registration.lis8' => 'form.registration.lis8',
                'form.registration.lis9' => 'form.registration.lis9',
                'form.registration.lis10' => 'form.registration.lis10',
                'form.registration.lis11' => 'form.registration.lis11',
                'form.registration.lis12' => 'form.registration.lis12',
                'form.registration.lis13' => 'form.registration.lis13',
                'form.registration.lis14' => 'form.registration.lis14',
            ),

                'multiple' => true,
                'required' => false,
                'attr' => array('class' => 'select2'),
                'label' => 'form.registration.license',
            ))

            ->add('searchedtag', EntityType::class, array(
                'choice_translation_domain' => true,
                'required' => false,
                'label' => 'form.registration.searchedtag',
                'class' => Tag::class,
                'choice_label' =>  'name',
                'placeholder' => 'form.registration.searchedtagPH',
                'multiple' => true,
                'attr' => array('class' => 'select2'),

            ))

            ->add('tag', EntityType::class, array(
                'choice_translation_domain' => true,
                'required' => false,
                'class' => Tag::class,
                'label' => 'form.registration.tag',
                'choice_label' =>  'name',
                'multiple' => true,
                'attr' => array('class' => 'select2'),
                'placeholder' => 'form.registration.tagPH',

            ))


            ->add('diploma', ChoiceType::class, array(
                'choices' => array(
                'form.registration.dip1' => 'form.registration.dip1',
                'form.registration.dip2' => 'form.registration.dip2',
                'form.registration.dip3' => 'form.registration.dip3',
                'form.registration.dip4' => 'form.registration.dip4',
                'form.registration.dip5' => 'form.registration.dip5',
                 ),
                'attr' => array('class' => 'select2'),
                'placeholder' => 'form.registration.dip0',
                'required' => false,
                'label' => 'offer.diploma',

            ))


            ->add('socialMedia',      TextType::class, array(
                'required' => false,
                'label' => 'form.registration.socialMedia',
            ))
            ->add('phone',      TextType::class, array(
                'required' => false,
                'label' => 'form.registration.phone',
            ))
            ->add('submit',      SubmitType::class, array(
                'attr' => array(
                    'class' => 'jobnow-button login',
                )
            ))
            ->add('cv', FileType ::class, array(
                'data_class' => null,
                'required' => false,
                'label' => 'offer.apply.cv',
            ))

            ->add('coverLetter', FileType ::class, array(
                'data_class' => null,
                'required' => false,
                'label' => 'offer.apply.message',
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
