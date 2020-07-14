<?php

namespace Xact\CommandScheduler\Entity;

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
     * @ORM\Column(name="ID", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
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
     * @ORM\Column(name="CronExpression", type="string")
     *
     * @var string
     */
    private $cronExpression;

    /**
     * @ORM\Column(name="Disabled", type="boolean")
     *
     * @var bool
     */
    private $disabled = false;

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
     * @return int
     */
    public function getId(): int
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
     * @return string
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
     * @return int
     */
    public function getLastResultCode(): int
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
