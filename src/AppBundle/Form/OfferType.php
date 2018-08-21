<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 31/05/2018
 * Time: 15:44
 */

namespace AppBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Ivory\GoogleMapBundle\Form\Type\PlaceAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class OfferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $translator = $options['translator'];

        $builder
            ->add('location', PlaceAutocompleteType::class,array(
                'required' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' =>  $translator->trans('offer.locationPH')
                ),
                'label' => 'offer.location',
            ))
            ->add('zipcode',      TextType::class, array(
                'required' => true,
                'label' => 'offer.zipcode',
            ))
            ->add('town',      TextType::class, array(
                'required' => true,
                'label' => 'offer.town',
            ))
            ->add('type', ChoiceType::class, array('choices' => array(
                'type.house' => 'type.house',
                'type.flat' => 'type.flat',
            ),
                'multiple' => false,
                'required' => true,
                'label' => 'offer.type',
                'expanded' => false,
                'choices_as_values' => true,
                'empty_data' => false,
                'placeholder' => false,
            ))
            ->add('surface', IntegerType::class, array(
                'required' => true,
                'label' => 'offer.surface',
            ))
            ->add('groundSurface', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.groundSurface',
            ))
            ->add('roomNumber', IntegerType::class, array(
                'required' => true,
                'label' => 'offer.roomNumber',
            ))
            ->add('bathroomNumber', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.bathroomNumber',
            ))
            ->add('totalFloor', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.totalFloor',
            ))
            ->add('floor', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.floor',
            ))
            ->add('basementSurface', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.basementSurface',
            ))
            ->add('parkingNumber', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.parkingNumber',
            ))
            ->add('buildingYear', IntegerType::class, array(
                'required' => false,
                'label' => 'offer.buildingYear',
                'attr' => array(
                    'placeholder' => 'offer.buildingYearPH',
                )
            ))
            ->add('lift', CheckboxType::class, array(
                'required' => false,
                'label' => 'offer.lift',
            ))
            ->add('balcony', CheckboxType::class, array(
                'required' => false,
                'label' => 'offer.balcony',
            ))
            ->add('images', CollectionType::class, array(

                'entry_type' => ImageType::class,

                'entry_options' => array('label' => false),
                'attr' => array(
                    'class' => 'my-selector'
                ),
                'required' => false,
                'allow_add' => true,
                'allow_delete'=>true,
            ))
            ->add('submit',      SubmitType::class, array(
                'attr' => array(
                    'class' => 'cproject-button offer-submit login'
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
            'data_class' => 'AppBundle\Entity\Offer'
        ));

        $resolver->setRequired('translator');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_proposer';
    }


}