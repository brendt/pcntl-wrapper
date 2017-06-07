<?php

namespace Brendt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Brendt\Pcntl\CallbackProcess;

class PcntlMultiSync extends Command
{
    public function __construct() {
        parent::__construct('pcntl:multi:sync');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);
        Log::startTimer();

        $processCallback = function (CallbackProcess $process) {
            Log::debug("Starting {$process->getName()}");
            sleep(1);
            Log::debug("Ending {$process->getName()}");
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

            if ($pid == -1) {
                Log::debug('Could not fork :(');

                exit;
            } else if ($pid) {
                Log::debug('Waiting');

                pcntl_wait($status);

                Log::debug('Done');
            } else {
                $process->execute();
            }
        }

        Log::endTimer();
    }

}
