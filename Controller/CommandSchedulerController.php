<?php

namespace Xact\CommandScheduler\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Form\ScheduledCommandForm;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class CommandSchedulerController extends AbstractController
{
    /**
     * @Route("/command-scheduler/list", name="xact_command_scheduler_list")
     */
    function list(CommandScheduler $scheduler) {
        return $this->render('@XactCommandScheduler/index.html.twig', [
            'scheduledCommands' => $scheduler->getAll(),
        ]);
    }

    /**
     * Edit a scheduled command
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Xact\CommandScheduler\Scheduler\CommandScheduler $scheduler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/edit/{id}", name="xact_command_scheduler_edit")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
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
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Xact\CommandScheduler\Scheduler\CommandScheduler $scheduler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/delete/{id}", name="xact_command_scheduler_delete")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
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
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Xact\CommandScheduler\Scheduler\CommandScheduler $scheduler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/disable/{id}", name="xact_command_scheduler_disable")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
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
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Xact\CommandScheduler\Scheduler\CommandScheduler $scheduler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/run/{id}", name="xact_command_scheduler_run")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Xact\CommandScheduler\Scheduler\CommandScheduler $scheduler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/new", name="xact_command_scheduler_new")
     */
    function new (Request $request, CommandScheduler $scheduler): Response {
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
