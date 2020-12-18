<?php

namespace Xact\CommandScheduler\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Form\ScheduledCommandForm;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class CommandSchedulerController extends Controller
{
    /**
     * @Route("/command-scheduler/list", name="xact_command_scheduler_list")
     */
    public function list(CommandScheduler $scheduler): Response
    {
        return $this->render('@XactCommandScheduler/index.html.twig', [
            'scheduledCommands' => $scheduler->getAll(),
        ]);
    }

    /**
     * @param \Xact\CommandScheduler\Scheduler\CommandScheduler $scheduler
     *
     * @Route("/command-scheduler/history/{id}", name="xact_command_scheduler_history")
     * @ParamConverter("command", class="Xact\CommandScheduler\Entity\ScheduledCommand")
     */
    public function history(ScheduledCommand $command): Response
    {
        return $this->render('@XactCommandScheduler/history.html.twig', [
            'command' => $command,
        ]);
    }

    /**
     * Edit a scheduled command
     *
     * @Route("/command-scheduler/edit/{id}", name="xact_command_scheduler_edit")
     * @ParamConverter("command", class="Xact\CommandScheduler\Entity\ScheduledCommand")
     */
    public function edit(Request $request, ScheduledCommand $command, CommandScheduler $scheduler): Response
    {
        $form = $this->createForm(ScheduledCommandForm::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scheduler->set($command);

            $this->addFlash('success', "The schedule for command '{$command->getCommand()} has been updated.'");

            return $this->redirectToRoute('xact_command_scheduler_list');
        }

        return $this->render('@XactCommandScheduler/edit.html.twig', [
            'title' => 'Edit Scheduled Command',
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete a scheduled command
     *
     * @Route("/command-scheduler/delete/{id}", name="xact_command_scheduler_delete")
     * @ParamConverter("command", class="Xact\CommandScheduler\Entity\ScheduledCommand")
     */
    public function delete(ScheduledCommand $command, CommandScheduler $scheduler): Response
    {
        $scheduler->delete($command);

        $this->addFlash('success', "The schedule for command '{$command->getCommand()} has been deleted.'");

        return $this->redirectToRoute('xact_command_scheduler_list');
    }

    /**
     * Disabled/enable a scheduled command
     *
     * @Route("/command-scheduler/disable/{id}", name="xact_command_scheduler_disable")
     * @ParamConverter("command", class="Xact\CommandScheduler\Entity\ScheduledCommand")
     */
    public function disable(ScheduledCommand $command, CommandScheduler $scheduler): Response
    {
        $scheduler->disable($command->getId(), !$command->getDisabled());

        $disabledText = $command->getDisabled() ? 'disabled' : 'enabled';
        $this->addFlash('success', "The schedule for command '{$command->getCommand()}' has been {$disabledText}.");
        
        return $this->redirectToRoute('xact_command_scheduler_list');
    }

    /**
     * Immediately run a scheduled command
     *
     * @Route("/command-scheduler/run/{id}", name="xact_command_scheduler_run")
     * @ParamConverter("command", class="Xact\CommandScheduler\Entity\ScheduledCommand")
     */
    public function run(ScheduledCommand $command, CommandScheduler $scheduler): Response
    {
        $scheduler->runImmediately($command->getId());

        $this->addFlash('success', "The command '{$command->getCommand()} has been scheduled to run immediately.'");

        return $this->redirectToRoute('xact_command_scheduler_list');
    }

    /**
     * Create a new scheduled command
     *
     * @Route("/command-scheduler/new", name="xact_command_scheduler_new")
     */
    public function new(Request $request, CommandScheduler $scheduler): Response
    {
        $command = new ScheduledCommand();

        $form = $this->createForm(ScheduledCommandForm::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scheduler->set($command);

            $this->addFlash('success', "A new schedule for command '{$command->getCommand()} has been created.'");

            return $this->redirectToRoute('xact_command_scheduler_list');
        }

        return $this->render('@XactCommandScheduler/edit.html.twig', [
            'title' => 'New Scheduled Command',
            'form' => $form->createView(),
        ]);
    }
}
