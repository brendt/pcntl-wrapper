<?php

namespace Brendt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Brendt\Pcntl\CallbackProcess;

class PcntlMultiAsync extends Command
{
    public function __construct() {
        parent::__construct('pcntl:multi:async');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);
        Log::startTimer();

        $processCallback = function (CallbackProcess $process) {
            Log::debug("Starting {$process->getName()}");
            sleep(1);
            Log::debug("Ending {$process->getName()}\n");
            exit;
        };

        /** @var CallbackProcess[] $processes */
        $processes = array_map(function ($name) use ($processCallback) {
            $process = new CallbackProcess($processCallback);
            $process->setName($name);

            return $process;
        }, ['A', 'B', 'C']);

        foreach ($processes as $process) {
            $pid = pcntl_fork();

            if (!$pid) {
                $process->execute();
            }
        }

        pcntl_wait($pid);

        Log::debug('Done');
        Log::endTimer();
    }

}
