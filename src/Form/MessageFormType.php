<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'rows' => 3,
                    'maxlength' => 5000,
                    'placeholder' => 'Votre message (lu, archivé, retenu contre vous)…',
                    'class' => 'w-full resize-none border border-line bg-void-raised px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-faint focus:border-acid focus:outline-none focus:shadow-[0_0_0_1px_rgba(57,255,140,0.4)] transition',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
