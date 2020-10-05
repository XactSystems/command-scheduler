<?php

namespace Xact\CommandScheduler\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduledCommand
 *
 * @ORM\Table(name="ScheduledCommandHistory")
 * @ORM\Entity()
 */
class ScheduledCommandHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Xact\CommandScheduler\Entity\ScheduledCommand
     *
     * @ORM\ManyToOne(targetEntity="ScheduledCommand", inversedBy="commandHistory")
     * @ORM\JoinColumn(name="ScheduledCommandID", referencedColumnName="ID")
     */
    private $scheduledCommand;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="LastRunAt", type="datetime", nullable=true)
     */
    private $runAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="LastResultCode", type="integer", nullable=true)
     */
    private $resultCode;

    /**
     * @var string
     *
     * @ORM\Column(name="LastResult", type="text", nullable=true)
     */
    private $result;

    /**
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     */
    public function setId(int $id): ScheduledCommandHistory
    {
        $this->id = $id;

        return $this;
    }

    /**
     */
    public function getScheduledCommand(): ScheduledCommand
    {
        return $this->scheduledCommand;
    }

    /**
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     */
    public function setScheduledCommand(ScheduledCommand $scheduledCommand): ScheduledCommandHistory
    {
        $this->scheduledCommand = $scheduledCommand;

        return $this;
    }

    /**
     */
    public function getResultCode(): ?int
    {
        return $this->resultCode;
    }

    /**
     * @param string $lastResultCode
     */
    public function setResultCode(int $resultCode): ScheduledCommandHistory
    {
        $this->resultCode = $resultCode;

        return $this;
    }

    /**
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     */
    public function setResult(string $result): ScheduledCommandHistory
    {
        $this->result = $result;

        return $this;
    }

    /**
     */
    public function getRunAt(): ?\DateTime
    {
        return $this->runAt;
    }

    /**
     */
    public function setRunAt(?\DateTime $runAt): ScheduledCommandHistory
    {
        $this->runAt = $runAt;

        return $this;
    }
}
