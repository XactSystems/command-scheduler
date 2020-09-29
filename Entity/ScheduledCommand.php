<?php

namespace Xact\CommandScheduler\Entity;

//annotations
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ScheduledCommand
 *
 * @ORM\Table(name="ScheduledCommand")
 * @ORM\Entity(repositoryClass="Xact\CommandScheduler\Repository\ScheduledCommandRepository")
 */
class ScheduledCommand
{
    //... Fields ...
    /**
     * @ORM\Column(name="ID", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="Description", type="string")
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="Command", type="string")
     *
     * @var string
     */
    private $command;

    /**
     * @ORM\Column(name="Arguments", type="json_array", nullable=true)
     *
     * @var array
     */
    private $arguments;

    /**
     * @ORM\Column(name="CronExpression", type="string", nullable=true)
     *
     * @var string
     */
    private $cronExpression;

    /**
     * @ORM\Column(name="Priority", type="integer")
     *
     * @var int
     */
    private $priority = 1;

    /**
     * @ORM\Column(name="Disabled", type="boolean")
     *
     * @var bool
     */
    private $disabled = false;

    /**
     * @ORM\Column(name="RunImmediately", type="boolean")
     *
     * @var bool
     */
    private $runImmediately = false;

    /**
     * @ORM\Column(name="LastRunAt", type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $lastRunAt;

    /**
     * @ORM\Column(name="LastResultCode", type="integer", nullable=true)
     *
     * @var integer
     */
    private $lastResultCode;

    /**
     * @ORM\Column(name="LastResult", type="text", nullable=true)
     *
     * @var string
     */
    private $lastResult;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="ScheduledCommandHistory", mappedBy="scheduledCommand", cascade="all", orphanRemoval=true)
     */
    private $commandHistory;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->commandHistory = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ScheduledCommand
     */
    public function setId(int $id): ScheduledCommand
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ScheduledCommand
     */
    public function setDescription(string $description): ScheduledCommand
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommand(): ?string
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
     * @return array|null
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return ScheduledCommand
     */
    public function setArguments(?array $arguments): ScheduledCommand
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    /**
     * @param string $cronExpression
     * @return ScheduledCommand
     */
    public function setCronExpression(?string $cronExpression): ScheduledCommand
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return ScheduledCommand
     */
    public function setPriority(int $priority): ScheduledCommand
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     * @return ScheduledCommand
     */
    public function setDisabled(bool $disabled): ScheduledCommand
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRunImmediately(): bool
    {
        return $this->runImmediately;
    }

    /**
     * @param bool $runImmediately
     * @return ScheduledCommand
     */
    public function setRunImmediately(bool $runImmediately): ScheduledCommand
    {
        $this->runImmediately = $runImmediately;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLastResultCode(): ?int
    {
        return $this->lastResultCode;
    }

    /**
     * @param string $lastResultCode
     * @return ScheduledCommand
     */
    public function setLastResultCode(int $lastResultCode): ScheduledCommand
    {
        $this->lastResultCode = $lastResultCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastResult(): ?string
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
    
    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommandHistory(): Collection
    {
        return $this->commandHistory;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $commandHistory
     * @return ScheduledCommand
     */
    public function setCommandHistory(Collection $commandHistory): ScheduledCommand
    {
        $this->commandHistory = $commandHistory;

        return $this;
    }
}
