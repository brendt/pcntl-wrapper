<?php

namespace Brendt\Command;

use Brendt\Pcntl\Manager;
use Brendt\Pcntl\Process;
use Brendt\Pcntl\ProcessCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcntlProcess extends Command
{
    /** @var MyProcess[] */
    private $processes;

    public function __construct() {
        parent::__construct('pcntl:process');

        /** @var MyProcess[] $processes */
        $this->processes = array_map(function ($name) {
            return new MyProcess($name);
        }, ['A', 'B', 'C', 'D', 'E']);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);
        Log::startTimer();

        $manager = new Manager();
        $threadHandlerCollection = new ProcessCollection();

        foreach ($this->processes as $process) {
            $threadHandlerCollection[] = $manager->async($process);
        }

        Log::debug("Main thread");

        $output = $manager->wait($threadHandlerCollection);

        d($output);

        Log::debug('Done');
        Log::endTimer();
    }
}

class MyProcess extends Process
{
    public function __construct($name) {
        $this->name = $name;
    }

    public function execute() {
        Log::debug("Starting {$this->getName()}");
        sleep(1);
        Log::debug("Ending {$this->getName()}");

        return rand(0, 10);
    }
}
