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
     * @var string
     *
     * @ORM\Column(name="LastError", type="text", nullable=true)
     */
    private $error;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getScheduledCommand(): ScheduledCommand
    {
        return $this->scheduledCommand;
    }

    public function setScheduledCommand(ScheduledCommand $scheduledCommand): self
    {
        $this->scheduledCommand = $scheduledCommand;

        return $this;
    }

    public function getResultCode(): ?int
    {
        return $this->resultCode;
    }

    public function setResultCode(int $resultCode): self
    {
        $this->resultCode = $resultCode;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getRunAt(): ?\DateTime
    {
        return $this->runAt;
    }

    public function setRunAt(?\DateTime $runAt): self
    {
        $this->runAt = $runAt;

        return $this;
    }
}
