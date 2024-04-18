<?php

namespace App\Form;

use App\Entity\Module;
use App\Entity\Session;
use App\Entity\Programme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProgrammeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //pas besoin de recuperer la session comme le form est accessible depuis une session précise
            ->add('session', HiddenType::class) 
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'attr' => [
                    'class' => 'form-control'
                ]
                // 'choice_label' => 'nom',
            ])
            ->add('duree', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1, 'max' => 100
                ],
                'label' => 'Durée en jours'
            ])
            ->add('Valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Programme::class,
        ]);
    }
}
