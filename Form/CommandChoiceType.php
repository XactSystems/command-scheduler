<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xact\CommandScheduler\Service\CommandLister;

class CommandChoiceType extends AbstractType
{
    private CommandLister $commandLister;


    public function __construct(CommandLister $commandLister)
    {
        $this->commandLister = $commandLister;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices' => $this->commandLister->getCommandChoices(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
