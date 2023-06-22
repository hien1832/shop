<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-box',
                    'placeholder' => 'Enter the quantity'
                )
            ])
            ->add('phone', TelType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-box',
                    'placeholder' => 'Enter Your Phone Number'
                )
            
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-box',
                    'placeholder' => 'Enter Your Email'
                )
            
            ])
            ->add('address', TextType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-box',
                    'placeholder' => 'Enter Your Address'
                )
            ])
            ->add('note', TextareaType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-area',
                    'placeholder' => 'Give Some Notes'
                )
            ])
            //->add('item')
            //->add('buyer')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
