<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Story;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoryType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('title')
                ->add('body')
                ->add('category', EntityType::class, [
                    'class' => Category::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Select one',
                ])
                ->add('tag',ChoiceType::class,['mapped'=>false])
                ->add('insta', TextType::class, ['label' => 'Instagram Url'])
               ->add('instagramTitle')
                ->add('createdOn', DateTimeType::class, array(
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'with_seconds' => false, 'label' => 'Date'
                ))
//                ->add('upload', FileType::class, ['mapped' => false])

        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Story::class,
                        'extra_fields_message' => 'This form should not contain extra fields.',
        
        ]);
    }

}
