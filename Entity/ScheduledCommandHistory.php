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
    private $lastRunAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="LastResultCode", type="integer", nullable=true)
     */
    private $lastResultCode;

    /**
     * @var string
     *
     * @ORM\Column(name="LastResult", type="text", nullable=true)
     */
    private $lastResult;

    /**
     * @var string
     *
     * @ORM\Column(name="LastError", type="text", nullable=true)
     */
    private $lastError;

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

    public function getLastResultCode(): ?int
    {
        return $this->lastResultCode;
    }

    public function setLastResultCode(?int $lastResultCode): self
    {
        $this->lastResultCode = $lastResultCode;

        return $this;
    }

    public function getLastResult(): ?string
    {
        return $this->lastResult;
    }

    public function setLastResult(?string $lastResult): self
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): self
    {
        $this->lastError = $lastError;

        return $this;
    }

    public function getLastRunAt(): ?\DateTime
    {
        return $this->lastRunAt;
    }

    public function setLastRunAt(?\DateTime $lastRunAt): self
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }
}
