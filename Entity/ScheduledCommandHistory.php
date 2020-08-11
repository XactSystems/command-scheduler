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
     * @var \Xact\CommandScheduler\Entity\ScheduledCommand
     *
     * @ORM\ManyToOne(targetEntity="ScheduledCommand", inversedBy="commandHistory")
     * @ORM\JoinColumn(name="ScheduledCommandID", referencedColumnName="ID")
     */
    private $scheduledCommand;

    /**
     * @ORM\Column(name="LastRunAt", type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $runAt;

    /**
     * @ORM\Column(name="LastResultCode", type="integer", nullable=true)
     *
     * @var integer
     */
    private $resultCode;

    /**
     * @ORM\Column(name="LastResult", type="text", nullable=true)
     *
     * @var string
     */
    private $result;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ScheduledCommandHistory
     */
    public function setId(int $id): ScheduledCommandHistory
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \Xact\CommandScheduler\Entity\ScheduledCommand
     */
    public function getScheduledCommand(): ScheduledCommand
    {
        return $this->scheduledCommand;
    }

    /**
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $command
     * @return ScheduledCommandHistory
     */
    public function setScheduledCommand(ScheduledCommand $scheduledCommand): ScheduledCommandHistory
    {
        $this->scheduledCommand = $scheduledCommand;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getResultCode(): ?int
    {
        return $this->resultCode;
    }

    /**
     * @param string $lastResultCode
     * @return ScheduledCommandHistory
     */
    public function setResultCode(int $resultCode): ScheduledCommandHistory
    {
        $this->resultCode = $resultCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * @param string $result
     * @return ScheduledCommandHistory
     */
    public function setResult(string $result): ScheduledCommandHistory
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getRunAt(): ?\DateTime
    {
        return $this->runAt;
    }

    /**
     * @param \DateTime|null $runAt
     * @return ScheduledCommandHistory
     */
    public function setRunAt(?\DateTime $runAt): ScheduledCommandHistory
    {
        $this->runAt = $runAt;

        return $this;
    }
}
