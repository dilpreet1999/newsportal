<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Page;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('keyword')
            ->add('description')
            ->add('url')
            ->add('target', ChoiceType::class,['choices'=>['open in same window'=>'_top','open in blank window'=>'_blank']])
            ->add('menu', EntityType::class,['class'=> Menu::class,'choice_label'=>'name'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
