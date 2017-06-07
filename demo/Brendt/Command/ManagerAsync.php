<?php

namespace Brendt\Command;

use Brendt\Pcntl\AsyncManager;
use Brendt\Pcntl\Manager;
use Brendt\Pcntl\Process;
use Brendt\Pcntl\ProcessCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Brendt\Pcntl\CallbackProcess;

class ManagerAsync extends Command
{
    /** @var CallbackProcess[] */
    private $processes;

    public function __construct() {
        parent::__construct('manager:async');

        $processCallback = function (CallbackProcess $process) {
            $sleeptime = rand(1, 5);
            $name = $process->getName() . ' ' . $sleeptime;

            Log::debug("Starting {$name}");
            sleep($sleeptime);

            return $name;
        };

        /** @var CallbackProcess[] $processes */
        $this->processes = array_map(function ($name) use ($processCallback) {
            $process = new CallbackProcess($processCallback);
            $process->setName($name);

            $process->onSuccess(function (Process $process) {
                Log::debug("Ending {$process->getName()} via success callback");
            });

            return $process;
        }, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);
        Log::startTimer();

        $manager = new AsyncManager();
        $processCollection = new ProcessCollection();

        foreach ($this->processes as $process) {
            $processCollection[] = $manager->async($process);
        }

        Log::debug("Main thread");

        $output = $manager->wait($processCollection);

        d($output);

        Log::debug('Done');
        Log::endTimer();
    }
}
