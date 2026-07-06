<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => false,
                'choices' => array_combine(range(1, 5), range(1, 5)),
                'expanded' => true,
                'invalid_message' => 'Choisissez une note valide.',
            ])
            ->add('comment', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'maxlength' => 2000,
                    'placeholder' => 'Votre retour d\'expérience (public, définitif, consultable par tous)…',
                    'class' => 'w-full resize-none border border-line bg-void-raised px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-faint focus:border-acid focus:outline-none focus:shadow-[0_0_0_1px_rgba(57,255,140,0.4)] transition',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
