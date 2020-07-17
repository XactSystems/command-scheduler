<?php

namespace Xact\CommandScheduler\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xact\CommandScheduler\Service\CommandLister;

class CommandChoiceType extends AbstractType
{
    /**
     * @var \Xact\CommandScheduler\Service\CommandLister
     */
    private $commandLister;

    /**
     * Class constructor.
     */
    public function __construct(CommandLister $commandLister)
    {
        $this->commandLister = $commandLister;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
    public function getParent()
    {
        return ChoiceType::class;
    }
}