<?php

namespace Xact\CommandScheduler\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Form\ScheduledCommandForm;

class CommandSchedulerController extends Controller
{
    /**
     * @Route("/command-scheduler/list", name="xact_command_scheduler_list")
     */
    function list(EntityManagerInterface $em) {
        $scheduledCommands = $em->getRepository(ScheduledCommand::class)->findAll();

        return $this->render('@XactCommandScheduler/index.html.twig', [
            'scheduledCommands' => $scheduledCommands,
        ]);
    }

    /**
     * Disabled/enable a scheduled command
     *
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Doctrine\ORM\EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/disable/{id}", name="xact_command_scheduler_disable")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
     */
    public function disable(ScheduledCommand $command, EntityManagerInterface $em): Response
    {
        $command->setDisabled(!$command->getDisabled());
        $em->flush();

        return $this->redirectToRoute('xact_command_scheduler_list');
    }

    /**
     * Edit a scheduled command
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Doctrine\ORM\EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/edit/{id}", name="xact_command_scheduler_edit")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
     */
    public function edit(Request $request, ScheduledCommand $command, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ScheduledCommandForm::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($command->getId() === null) {
                $em->persist($command);
            }
            $em->flush();

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
     * @param \Doctrine\ORM\EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/delete/{id}", name="xact_command_scheduler_delete")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
     */
    public function delete(ScheduledCommand $command, EntityManagerInterface $em): Response
    {
        $em->remove($command);
        $em->flush();

        $this->addFlash('success', "The schedule for command '{$command->getCommand()} has been deleted.'");


        return $this->redirectToRoute('xact_command_scheduler_list');
    }

    /**
     * Immediately run a scheduled command
     *
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @param \Doctrine\ORM\EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/run/{id}", name="xact_command_scheduler_run")
     * @ParamConverter("command", class="XactCommandSchedulerBundle:ScheduledCommand")
     */
    public function run(ScheduledCommand $command, EntityManagerInterface $em): Response
    {
        $command->setRunImmediately(true);
        $em->flush();

        $this->addFlash('success', "The command '{$command->getCommand()} has been scheduled to run immediately.'");

        return $this->redirectToRoute('xact_command_scheduler_list');
    }

    /**
     * Create a new scheduled command
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/command-scheduler/new", name="xact_command_scheduler_new")
     */
    function new (Request $request, EntityManagerInterface $em): Response {
        $command = new ScheduledCommand();

        $form = $this->createForm(ScheduledCommandForm::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($command->getId() === null) {
                $em->persist($command);
            }
            $em->flush();

            $this->addFlash('success', "A new schedule for command '{$command->getCommand()} has been created.'");


            return $this->redirectToRoute('xact_command_scheduler_list');
        }

        return $this->render('@XactCommandScheduler/edit.html.twig', [
            'title' => 'New Scheduled Command',
            'form' => $form->createView(),
        ]);
    }
}
