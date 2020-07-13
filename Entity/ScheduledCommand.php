<?php

namespace Xact\JobScheduler\Entity;

//annotations
use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduledCommand
 *
 * @ORM\Table(name="ScheduledCommand")
 * @ORM\Entity(repositoryClass="App\Entity\Repository\ScheduledCommandRepository")
 */
class ScheduledCommand
{
    //... Fields ...
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $command;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array
     */
    private $options;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $frequency;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $lastTriggeredAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $lastRunAt;

    /**
     * @ORM\Column(type="text", nullable=true)
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
     * @return array
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return ScheduledCommand
     */
    public function setOptions(?array $options): ScheduledCommand
    {
        $this->options = $options;

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
