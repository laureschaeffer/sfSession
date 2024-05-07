<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Session;
use App\Entity\Formation;
use App\Entity\Stagiaire;
use App\Form\ProgrammeType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SessionType extends AbstractType
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
            //la collection attend l'élément qu'elle rentrera dans le form
            ->add('programmes', CollectionType::class, [
                'entry_type' => ProgrammeType::class,
                'prototype' => true,
                //autorise l'ajout de nouveau element dans l'entite session, qui seront persistés grace aux cascade_persist sur l'élément programme
                //va activer un data prototype qui sera un attribut html qu'on pourra manipuler en js
                'allow_add' => true, //ajoute plusieurs programmes
                'allow_delete' => true,
                'by_reference' => false //pour éviter un mapping false, obligatoire car Session n'a pas de SetProgramme, mais Programme a setSession
                // Programme est propriétaire de la relation
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
