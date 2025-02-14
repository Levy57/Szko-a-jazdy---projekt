<?php

namespace App\Form;

use App\Entity\Pojazd;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PojazdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nazwa')
            ->add('marka')
            ->add('rok')
            ->add('stan')
            ->add('kategoria')
            ->add('vin')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pojazd::class,
        ]);
    }
}
