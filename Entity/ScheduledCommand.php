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
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_RUNNING = 'RUNNING';
    public const STATUS_COMPLETED = 'COMPLETED';

    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="Command", type="string")
     */
    private $command;

    /**
     * @var array
     *
     * @ORM\Column(name="Arguments", type="json_array", nullable=true)
     */
    private $arguments;

    /**
     * @var string
     *
     * @ORM\Column(name="CronExpression", type="string", nullable=true)
     */
    private $cronExpression;

    /**
     * @var int
     *
     * @ORM\Column(name="Priority", type="integer")
     */
    private $priority = 1;

    /**
     * @var bool
     *
     * @ORM\Column(name="Disabled", type="boolean")
     */
    private $disabled = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="RunImmediately", type="boolean")
     */
    private $runImmediately = false;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", length=20, nullable=false)
     */
    private $status = self::STATUS_PENDING;

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
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     */
    public function setId(int $id): ScheduledCommand
    {
        $this->id = $id;

        return $this;
    }

    /**
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     */
    public function setDescription(string $description): ScheduledCommand
    {
        $this->description = $description;

        return $this;
    }

    /**
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     */
    public function setCommand(string $command): ScheduledCommand
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @param string[] $arguments
     */
    public function setArguments(?array $arguments): ScheduledCommand
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     */
    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    /**
     */
    public function setCronExpression(?string $cronExpression): ScheduledCommand
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    /**
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     */
    public function setPriority(int $priority): ScheduledCommand
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     */
    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     */
    public function setDisabled(bool $disabled): ScheduledCommand
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     */
    public function getRunImmediately(): bool
    {
        return $this->runImmediately;
    }

    /**
     */
    public function setRunImmediately(bool $runImmediately): ScheduledCommand
    {
        $this->runImmediately = $runImmediately;

        return $this;
    }

    /**
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     */
    public function setStatus(string $status): ScheduledCommand
    {
        $this->status = $status;

        return $this;
    }

    /**
     */
    public function getLastResultCode(): ?int
    {
        return $this->lastResultCode;
    }

    /**
     * @param string $lastResultCode
     */
    public function setLastResultCode(int $lastResultCode): ScheduledCommand
    {
        $this->lastResultCode = $lastResultCode;

        return $this;
    }

    /**
     */
    public function getLastResult(): ?string
    {
        return $this->lastResult;
    }

    /**
     */
    public function setLastResult(string $lastResult): ScheduledCommand
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    /**
     */
    public function getLastRunAt(): ?\DateTime
    {
        return $this->lastRunAt;
    }

    /**
     */
    public function setLastRunAt(?\DateTime $lastRunAt): ScheduledCommand
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }
    
    /**
     */
    public function getCommandHistory(): Collection
    {
        return $this->commandHistory;
    }

    /**
     */
    public function setCommandHistory(Collection $commandHistory): ScheduledCommand
    {
        $this->commandHistory = $commandHistory;

        return $this;
    }
}
