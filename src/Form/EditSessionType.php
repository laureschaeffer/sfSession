<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Session;
use App\Entity\Formation;
use App\Entity\Stagiaire;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class EditSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbPlace', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Nombre de places'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text', 
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text', 
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('formation', EntityType::class, [
                'class' => Formation::class,
                // 'choice_label' => 'id',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            //pour que le select ne propose que les users qui ont le role de formateur
            // https://symfony.com/doc/current/reference/forms/types/entity.html#using-a-custom-query-for-the-entities
            //SELECT * FROM user WHERE roles LIKE "%FORMATEUR%"
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Prof référent',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE \'%FORMATEUR%\'');
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('Valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
