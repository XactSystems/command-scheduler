<?php

namespace Xact\CommandScheduler\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="Description", type="string", nullable=true)
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
     * @ORM\Column(name="Data", type="string")
     */
    private $data;

    /**
     * @var bool
     *
     * @ORM\Column(name="ClearData", type="boolean")
     */
    private $clearData = true;

    /**
     * @var string
     *
     * @ORM\Column(name="CronExpression", type="string", nullable=true)
     */
    private $cronExpression;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="RunAt", type="datetime", nullable=true)
     */
    private $runAt;

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
    private $runImmediately = true;

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
     * @param string[] $arguments
     */
    public function __construct(string $command = '', ?array $arguments = [], ?string $data = null)
    {
        $this->command = $command;
        $this->arguments = $arguments ?? [];
        $this->data = $data;
        $this->commandHistory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
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
    public function setArguments(?array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data =  $data;

        return $this;
    }

    public function getClearData(): ?bool
    {
        return $this->clearData;
    }

    public function setClearData(bool $clearData): self
    {
        $this->clearData =  $clearData;

        return $this;
    }

    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    public function setCronExpression(?string $cronExpression): self
    {
        $this->cronExpression = $cronExpression;
        if ($this->cronExpression) {
            $this->runAt = null;
            $this->runImmediately = false;
        }

        return $this;
    }

    public function getRunAt(): ?\DateTime
    {
        return $this->runAt;
    }

    public function setRunAt(?\DateTime $runAt): self
    {
        $this->runAt = $runAt;
        if ($this->runAt) {
            $this->cronExpression = null;
            $this->runImmediately = false;
        }

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getRunImmediately(): bool
    {
        return $this->runImmediately;
    }

    public function setRunImmediately(bool $runImmediately): self
    {
        $this->runImmediately = $runImmediately;
        if ($this->runImmediately) {
            $this->cronExpression = null;
            $this->runAt = null;
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastResultCode(): ?int
    {
        return $this->lastResultCode;
    }

    public function setLastResultCode(int $lastResultCode): self
    {
        $this->lastResultCode = $lastResultCode;

        return $this;
    }

    public function getLastResult(): ?string
    {
        return $this->lastResult;
    }

    public function setLastResult(string $lastResult): self
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(string $lastError): self
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

    public function getCommandHistory(): Collection
    {
        return $this->commandHistory;
    }

    public function setCommandHistory(Collection $commandHistory): self
    {
        $this->commandHistory = $commandHistory;

        return $this;
    }
}
