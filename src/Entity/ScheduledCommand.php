<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Entity;

use DateTime;
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
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_RETRIES_EXCEEDED = 'RETRIES_EXCEEDED';

    /**
     * @ORM\Column(name="ID", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="Description", type="string", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(name="Command", type="string")
     */
    private string $command = '';

    /**
     * @var mixed[]|null
     * @ORM\Column(name="Arguments", type="json", nullable=true)
     */
    private ?array $arguments = null;

    /**
     * @ORM\Column(name="Data", type="string", nullable=true)
     */
    private ?string $data = null;

    /**
     * @ORM\Column(name="ClearData", type="boolean")
     */
    private bool $clearData = true;

    /**
     * @ORM\Column(name="CronExpression", type="string", nullable=true)
     */
    private ?string $cronExpression = null;

    /**
     * @ORM\Column(name="RunAt", type="datetime", nullable=true)
     */
    private ?\DateTime $runAt = null;

    /**
     * @ORM\Column(name="Priority", type="integer")
     */
    private int $priority = 1;

    /**
     * @ORM\Column(name="Disabled", type="boolean")
     */
    private bool $disabled = false;

    /**
     * @ORM\Column(name="RunImmediately", type="boolean")
     */
    private bool $runImmediately = true;

    /**
     * @ORM\Column(name="RetryOnFail", type="boolean")
     */
    private bool $retryOnFail = false;

    /**
     * @ORM\Column(name="RetryDelay", type="integer")
     */
    private int $retryDelay = 60;

    /**
     * @ORM\Column(name="RetryMaxAttempts", type="integer")
     */
    private int $retryMaxAttempts = 60;

    /**
     * @ORM\Column(name="RetryCount", type="integer")
     */
    private int $retryCount = 0;

    /**
     * @ORM\Column(name="RetryAt", type="datetime", nullable=true)
     */
    private ?\DateTime $retryAt = null;

    /**
     * @ORM\Column(name="CreatedAt", type="datetime", nullable=true)
     */
    private ?\DateTime $createdAt;

    /**
     * @ORM\Column(name="Status", type="string", length=20, nullable=false)
     */
    private string $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="LastRunAt", type="datetime", nullable=true)
     */
    private ?\DateTime $lastRunAt = null;

    /**
     * @ORM\Column(name="LastResultCode", type="integer", nullable=true)
     */
    private ?int $lastResultCode = null;

    /**
     * @ORM\Column(name="LastResult", type="text", nullable=true)
     */
    private ?string $lastResult = null;

    /**
     * @ORM\Column(name="LastError", type="text", nullable=true)
     */
    private ?string $lastError = null;

    /**
     * @ORM\OneToOne(targetEntity="ScheduledCommand", fetch="LAZY")
     * @ORM\JoinColumn(name="OnSuccessCommandID", referencedColumnName="ID", nullable=true)
     */
    private ?self $onSuccessCommand = null;

    /**
     * @ORM\OneToOne(targetEntity="ScheduledCommand", fetch="LAZY")
     * @ORM\JoinColumn(name="OnFailureCommandID", referencedColumnName="ID", nullable=true)
     */
    private ?self $onFailureCommand = null;

    /**
     * @ORM\OneToOne(targetEntity="ScheduledCommand", fetch="LAZY")
     * @ORM\JoinColumn(name="OriginalCommandID", referencedColumnName="ID", nullable=true)
     */
    private ?self $originalCommand = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Xact\CommandScheduler\Entity\ScheduledCommandHistory>
     * @ORM\OneToMany(targetEntity="ScheduledCommandHistory", mappedBy="scheduledCommand", cascade={"all"}, orphanRemoval=true)
     */
    private Collection $commandHistory;

    /**
     * @param string[] $arguments
     */
    public function __construct(string $command = '', ?array $arguments = [], ?string $data = null)
    {
        $this->command = $command;
        $this->arguments = $arguments ?? [];
        $this->data = $data;
        $this->commandHistory = new ArrayCollection();
        $this->createdAt = new DateTime();
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return mixed[]|null
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @param mixed[] $arguments
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

    public function getClearData(): bool
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

    public function getRetryOnFail(): bool
    {
        return $this->retryOnFail;
    }

    public function setRetryOnFail(bool $retryOnFail): self
    {
        $this->retryOnFail = $retryOnFail;

        return $this;
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    public function setRetryDelay(int $retryDelay): self
    {
        $this->retryDelay = $retryDelay;

        return $this;
    }

    public function getRetryMaxAttempts(): int
    {
        return $this->retryMaxAttempts;
    }

    public function setRetryMaxAttempts(int $retryMaxAttempts): self
    {
        $this->retryMaxAttempts = $retryMaxAttempts;

        return $this;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): self
    {
        $this->retryCount = $retryCount;

        return $this;
    }

    public function getRetryAt(): ?\DateTime
    {
        return $this->retryAt;
    }

    public function setRetryAt(?\DateTime $retryAt): self
    {
        $this->retryAt = $retryAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): string
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

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \Xact\CommandScheduler\Entity\ScheduledCommandHistory>
     */
    public function getCommandHistory(): Collection
    {
        return $this->commandHistory;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, \Xact\CommandScheduler\Entity\ScheduledCommandHistory> $commandHistory
     */
    public function setCommandHistory(Collection $commandHistory): self
    {
        $this->commandHistory = $commandHistory;

        return $this;
    }

    public function getOnSuccessCommand(): ?self
    {
        return $this->onSuccessCommand;
    }

    public function setOnSuccessCommand(?self $onSuccessCommand): self
    {
        $this->onSuccessCommand = $onSuccessCommand;

        return $this;
    }

    public function getOnFailureCommand(): ?self
    {
        return $this->onFailureCommand;
    }

    public function setOnFailureCommandId(?self $onFailureCommand): self
    {
        $this->onFailureCommand = $onFailureCommand;

        return $this;
    }

    public function getOriginalCommand(): ?self
    {
        return $this->originalCommand;
    }

    public function setOriginalCommand(?self $originalCommand): self
    {
        $this->originalCommand = $originalCommand;

        return $this;
    }
}
