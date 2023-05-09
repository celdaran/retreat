<?php namespace App\Service;

use League\CLImate\CLImate;

class CLI
{
    private string $expenseScenario;
    private string $assetScenario;
    private ?int $startYear;
    private ?int $startMonth;
    private int $duration;

    public function __construct()
    {
        $climate = new CLImate();
        $climate->arguments->add($this->getConfig());
        $climate->arguments->parse();

        if ($climate->arguments->get('help')) {
            $climate->usage();
            exit;
        }

        $this->expenseScenario = $climate->arguments->get('expense');
        $this->assetScenario = $climate->arguments->get('asset');
        $this->startYear = intval($climate->arguments->get('startYear'));
        $this->startMonth = intval($climate->arguments->get('startMonth'));
        $this->duration = intval($climate->arguments->get('duration'));

        if ($this->assetScenario === 'same as expense') {
            $this->assetScenario = $this->expenseScenario;
        }

        if ($this->startYear === 0) {
            $this->startYear = null;
        }

        if ($this->startMonth === 0) {
            $this->startMonth = null;
        }
    }

    public function getExpenseScenario(): string
    {
        return $this->expenseScenario;
    }

    public function getAssetScenario(): string
    {
        return $this->assetScenario;
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function getStartMonth(): ?int
    {
        return $this->startMonth;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    private function getConfig(): array
    {
        return [
            'help' => [
                'prefix' => '?',
                'longPrefix' => 'help',
                'noValue' => true,
            ],
            'expense' => [
                'prefix' => 'e',
                'longPrefix' => 'expense',
                'description' => 'Specify the name of the expense scenario',
                'defaultValue' => 'base',
            ],
            'asset' => [
                'prefix' => 'a',
                'longPrefix' => 'asset',
                'description' => 'Specify the name of the asset scenario',
                'defaultValue' => 'same as expense',
            ],
            'startYear' => [
                'prefix' => 'y',
                'longPrefix' => 'year',
                'description' => 'The start year of the simulation',
                'defaultValue' => null,
            ],
            'startMonth' => [
                'prefix' => 'm',
                'longPrefix' => 'month',
                'description' => 'The start month of the simulation',
                'defaultValue' => null,
            ],
            'duration' => [
                'prefix' => 'd',
                'longPrefix' => 'duration',
                'description' => 'The duration, in months, of the simulation',
                'defaultValue' => 360,
            ],
//        'adjust' => [
//            'prefix' => 'a',
//            'longPrefix' => 'adjust',
//            'description' => 'Adjust the start year and month to match the simulation start year and month. By default, each expense and asset begins in a pre-defined period, persisted to the database. However, for non-fixed-period expenses and assets, this value can be overridden by the simulation\'s start period.',
//            'noValue'     => true,
//        ],
        ];
    }
}