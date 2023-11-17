<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a word',
                    ]),
                ]
            ])
            ->add('description',TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a word',
                    ]),
                ]
            ])
            ->add('content',CKEditorType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a word',
                    ]),
                ]
            ])
            ->add('imageFile',VichImageType::class,[
                'label'=>'Importer une image?',
                'required'=>false,

            ])
            ->add('submit',SubmitType::class,[
                'label'=>'Envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
