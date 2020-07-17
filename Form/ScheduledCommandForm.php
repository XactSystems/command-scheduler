<?php

namespace Xact\CommandScheduler\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xact\CommandScheduler\Entity\ScheduledCommand;

/**
 * Class ScheduledCommandType.
 *
 * @author  Ian Foulds <ianfoulds@x-act.co.uk>
 */
class ScheduledCommandForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', HiddenType::class)
            ->add('description', TextType::class, ['label' => 'Description', 'required' => true])
            ->add('command', CommandChoiceType::class, ['label' => 'Command', 'required' => true])
            ->add('arguments', TextType::class, ['label' => 'Arguments', 'required' => false])
            ->add('cronExpression', TextType::class, ['label' => 'Cron Expression', 'required' => false])
            ->add('runImmediately', CheckboxType::class, ['label' => 'Run Immediately', 'required' => false])
            ->add('priority', IntegerType::class, ['label' => 'Priority', 'required' => true, 'attr' => ['min' => 1, 'max' => 100]])
            ->add('disabled', CheckboxType::class, ['label' => 'Disabled', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Save Command'])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ScheduledCommand::class,
                'translation_domain' => 'XactCommandScheduler',
            ]
        );
    }

    /**
     * Form prefix.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'scheduler_edit';
    }
}
