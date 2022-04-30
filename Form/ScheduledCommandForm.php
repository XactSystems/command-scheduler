<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
     * @param mixed[] $options
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $command = $builder->getData();
        $argumentsJson = json_encode($command->getArguments());
        $builder->add('id', HiddenType::class)
            ->add('description', TextType::class, ['label' => 'Description', 'required' => true])
            ->add('command', CommandChoiceType::class, ['label' => 'Command', 'required' => true])
            ->add('arguments', TextareaType::class, ['label' => 'Arguments', 'required' => false, 'mapped' => false, 'data' => $argumentsJson])
            ->add('cronExpression', TextType::class, ['label' => 'Cron Expression', 'required' => false])
            ->add('runImmediately', CheckboxType::class, ['label' => 'Run Immediately', 'required' => false])
            ->add('priority', IntegerType::class, ['label' => 'Priority', 'required' => true, 'attr' => ['min' => 1, 'max' => 100]])
            ->add('disabled', CheckboxType::class, ['label' => 'Disabled', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Save Command'])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event): void {
                $formData = $event->getData();
                $form = $event->getForm();
                if (array_key_exists('arguments', $formData)) {
                    $formData['arguments'] = json_decode($formData['arguments']);
                    $form->setData($formData);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ScheduledCommand::class,
                'translation_domain' => 'XactCommandScheduler',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'scheduler_edit';
    }
}
