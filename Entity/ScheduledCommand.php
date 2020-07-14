<?php

namespace Xact\JobScheduler\Entity;

//annotations
use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduledCommand
 *
 * @ORM\Table(name="ScheduledCommand")
 * @ORM\Entity
 */
class ScheduledCommand
{
    //... Fields ...
    /**
     * @ORM\Column(name="ID", type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(name="Command", type="string")
     *
     * @var string
     */
    private $command;

    /**
     * @ORM\Column(name="Arguments", type="string", nullable=true)
     *
     * @var string
     */
    private $arguments;

    /**
     * @ORM\Column(name="Frequency", type="integer")
     *
     * @var integer
     */
    private $frequency;

    /**
     * @ORM\Column(name="Disabled", type="boolean")
     *
     * @var bool
     */
    private $disabled;

    /**
     * @ORM\Column(name="LastTriggeredAt", type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $lastTriggeredAt;

    /**
     * @ORM\Column(name="LastRunAt", type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $lastRunAt;

    /**
     * @ORM\Column(name="LastResult", type="text", nullable=true)
     *
     * @var string
     */
    private $lastResult;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ScheduledCommand
     */
    public function setId(string $id): ScheduledCommand
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return ScheduledCommand
     */
    public function setCommand(string $command): ScheduledCommand
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return string
     */
    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    /**
     * @param string $arguments
     * @return ScheduledCommand
     */
    public function setArguments(?string $arguments): ScheduledCommand
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return ScheduledCommand
     */
    public function setFrequency(int $frequency): ScheduledCommand
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param int $disabled
     * @return ScheduledCommand
     */
    public function setDisabled(bool $disabled): ScheduledCommand
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastResult(): string
    {
        return $this->lastResult;
    }

    /**
     * @param string $lastResult
     * @return ScheduledCommand
     */
    public function setLastResult(string $lastResult): ScheduledCommand
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTriggeredAt(): ?\DateTime
    {
        return $this->lastTriggeredAt;
    }

    /**
     * @param \DateTime|null $lastTriggeredAt
     * @return ScheduledCommand
     */
    public function setLastTriggeredAt(?\DateTime $lastTriggeredAt): ScheduledCommand
    {
        $this->lastTriggeredAt = $lastTriggeredAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastRunAt(): ?\DateTime
    {
        return $this->lastRunAt;
    }

    /**
     * @param \DateTime|null $lastRunAt
     * @return ScheduledCommand
     */
    public function setLastRunAt(?\DateTime $lastRunAt): ScheduledCommand
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }

}
