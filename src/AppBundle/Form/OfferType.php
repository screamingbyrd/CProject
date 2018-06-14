<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 31/05/2018
 * Time: 15:44
 */

namespace AppBundle\Form;

use AppBundle\Entity\ContractType;
use AppBundle\Entity\Tag;
use Ivory\GoogleMapBundle\Form\Type\PlaceAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class OfferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',      TextType::class, array('required' => false))
            ->add('description', TextareaType::class, array(
                'required' => false,
            ))

            ->add('tag', EntityType::class, array(
                'required' => false,
                'class' => Tag::class,
                'choice_label' =>  'name',
                'attr' => array('class' => 'select2'),
                'choices_as_values' => true,
                'multiple' => true,
            ))


            ->add('location', PlaceAutocompleteType::class)

            ->add('availableDate',      DateType::class, array('required' => false,'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'attr' => [
                    'class' => 'form-control input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd-mm-yyyy']))

            ->add('contractType', EntityType::class, array(
                'required' => false,
                'class' => ContractType::class,
                'choice_label' =>  'name',
                'placeholder' => 'form.offer.contractType',
                'choice_translation_domain' => 'messages',
            ))

            ->add('experience', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',
                'form.registration.exp2' => 'form.registration.exp2',
                'form.registration.exp3' => 'form.registration.exp3',

            ),
                'placeholder' => 'form.registration.exp0',
            ))


            ->add('diploma', ChoiceType::class, array('choices' => array(
                'form.registration.dip1' => 'form.registration.dip1',
                'form.registration.dip2' => 'form.registration.dip2',
                'form.registration.dip3' => 'form.registration.dip3',
                'form.registration.dip4' => 'form.registration.dip4',
                'form.registration.dip5' => 'form.registration.dip5',
            ),
                'placeholder' => 'form.registration.dip0',
            ))

            ->add('wage', ChoiceType::class, array('choices' => array(
                'form.registration.wag1' => 'form.registration.wag1',
                'form.registration.wag2' => 'form.registration.wag2',
                'form.registration.wag3' => 'form.registration.wag3',
                'form.registration.wag4' => 'form.registration.wag4',
                'form.registration.wag5' => 'form.registration.wag5',
                'form.registration.wag6' => 'form.registration.wag6',
                'form.registration.wag7' => 'form.registration.wag7',
                'form.registration.wag8' => 'form.registration.wag8',
                'form.registration.wag9' => 'form.registration.wag9',
                'form.registration.wag10' => 'form.registration.wag10',

            ),
                'required' => false,
                'placeholder' => 'form.registration.wag0',
            ))

            ->add('benefits', ChoiceType::class, array('choices' => array(
                'form.registration.ben1' => 'form.registration.ben1',
                'form.registration.ben2' => 'form.registration.ben2',
                'form.registration.ben3' => 'form.registration.ben3',
                'form.registration.ben4' => 'form.registration.ben4',

            ),
                'attr' => array('class' => 'select2'),
                'required' => false,
                'multiple' => true,
                'expanded' => true,

                'placeholder' => 'form.registration.ben0',
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
                'attr' => array('class' => 'select2'),
                'choices_as_values' => true,
                'multiple' => true,
                'required' => false,
            ))


            ->add('image', ImageType::class, array(
                'required' => false,
            ))


            ->add('submit',      SubmitType::class)
            ->getForm()
        ;
    }/**
 * {@inheritdoc}
 */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Offer'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_employer';
    }


}