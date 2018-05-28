<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;


class CandidateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName',      TextType::class, array('required' => true))
            ->add('lastName',      TextType::class, array('required' => true))
            ->add('email',      EmailType::class, array('required' => true))
            ->add('password',      PasswordType::class, array('required' => true))
            ->add('description',      TextType::class, array('required' => false))
            ->add('age',      TextType::class, array('required' => false))
            ->add('experience',      TextType::class, array('required' => false))
            ->add('license',      TextType::class, array('required' => false))
            ->add('diploma',      TextType::class, array('required' => false))
            ->add('socialMedia',      TextType::class, array('required' => false))
            ->add('phone',      TextType::class, array('required' => true))
            ->add('save',      SubmitType::class)
            ->getForm()
        ;
    }/**
 * {@inheritdoc}
 */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Candidate'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_candidate';
    }


}
