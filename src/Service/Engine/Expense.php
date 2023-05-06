<?php namespace App\Service\Engine;

/**
 * A class representing an expense
 */
class Expense {

    const PLANNED = 0;
    const ACTIVE = 1;
    const ENDED = 9;

    protected string $name;
    protected Money $amount;
    protected float $inflationRate;
    protected ?int $beginYear;
    protected ?int $beginMonth;
    protected ?int $endYear;
    protected ?int $endMonth;
    protected ?int $repeatEvery;
    protected bool $fixedPeriod;
    protected int $status;

    public function __construct()
    {
    }

    //--------------------------------------------
    // Setters
    //--------------------------------------------

    public function setName(string $name): Expense
    {
        $this->name = $name;
        return $this;
    }

    public function setAmount(Money $amount): Expense
    {
        $this->amount = $amount;
        return $this;
    }

    public function increaseAmount(float $n): Expense
    {
        $this->amount->add($n);
        return $this;
    }

    public function setInflationRate(float $inflationRate): Expense
    {
        $this->inflationRate = round($inflationRate, 3);
        return $this;
    }

    public function setBeginYear(?int $beginYear): Expense
    {
        $this->beginYear = $beginYear;
        return $this;
    }

    public function setBeginMonth(?int $beginMonth): Expense
    {
        $this->beginMonth = $beginMonth;
        return $this;
    }

    public function setEndYear(?int $endYear): Expense
    {
        $this->endYear = $endYear;
        return $this;
    }

    public function setEndMonth(?int $endMonth): Expense
    {
        $this->endMonth = $endMonth;
        return $this;
    }

    public function setRepeatEvery(?int $repeatEvery): Expense
    {
        $this->repeatEvery = $repeatEvery;
        return $this;
    }

    public function setFixedPeriod(bool $fixedPeriod): Expense
    {
        $this->fixedPeriod = $fixedPeriod;
        return $this;
    }

    public function markPlanned(): Expense
    {
        $this->status = self::PLANNED;
        return $this;
    }

    public function markActive(): Expense
    {
        $this->status = self::ACTIVE;
        return $this;
    }

    public function markEnded(): Expense
    {
        $this->status = self::ENDED;
        return $this;
    }

    //--------------------------------------------
    // Getters
    //--------------------------------------------

    public function name(): string
    {
        return $this->name;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function inflationRate(): float
    {
        return $this->inflationRate;
    }

    public function beginYear(): int
    {
        return $this->beginYear;
    }

    public function beginMonth(): int
    {
        return $this->beginMonth;
    }

    public function endYear(): ?int
    {
        return $this->endYear;
    }

    public function endMonth(): ?int
    {
        return $this->endMonth;
    }

    public function repeatEvery(): ?int
    {
        return $this->repeatEvery;
    }

    public function fixedPeriod(): bool
    {
        return $this->fixedPeriod;
    }

    public function isPlanned(): bool
    {
        return $this->status === self::PLANNED;
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isEnded(): bool
    {
        return $this->status === self::ENDED;
    }

    public function status(): string
    {
        switch ($this->status) {
            case self::PLANNED:
                return 'planned';
            case self::ACTIVE:
                return 'active';
            case self::ENDED:
                return 'ended';
        }
        return 'unknown';
    }

    public function timeToActivate(Period $period): bool
    {
        if ($this->isPlanned()) {
            if (($period->getYear() >= $this->beginYear()) && ($period->getMonth() >= $this->beginMonth())) {
                return true;
            }
        }

        return false;
    }

    public function timeToEnd(Period $period): bool
    {
        if ($this->isActive()) {
            if (($period->getYear() >= $this->endYear()) && ($period->getMonth() >= $this->endMonth())) {
                return true;
            }
        }

        return false;
    }

}
