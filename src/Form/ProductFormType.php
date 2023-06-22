<?php

namespace App\Form;

use App\Entity\Product;
use Doctrine\Common\Annotations\Annotation\Required;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-box',
                    'placeholder' => 'Enter the product title'
                )
            ])
            ->add('price', IntegerType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-box',
                    'placeholder' => 'Enter the product price'
                )
            ])
            ->add('img_path', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Image:',
                'attr' => array(
                    'class' => 'form-checkbox',
                )
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr' => array(
                    'class' => 'form-area',
                    'placeholder' => 'Enter the product title'
                )
            ])
            //->add('seller')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
