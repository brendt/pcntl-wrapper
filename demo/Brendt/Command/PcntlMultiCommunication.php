<?php

namespace Brendt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Brendt\Pcntl\CallbackProcess;

class PcntlMultiCommunication extends Command
{
    /** @var CallbackProcess[] */
    private $processes;

    public function __construct() {
        parent::__construct('pcntl:multi:communication');

        $processCallback = function (CallbackProcess $process) {
            Log::debug("Starting {$process->getName()}");
            sleep(1);
            Log::debug("Ending {$process->getName()}");

            return $process->getName();
        };

        /** @var CallbackProcess[] $processes */
        $this->processes = array_map(function ($name) use ($processCallback) {
            $process = new CallbackProcess($processCallback);
            $process->setName($name);

            return $process;
        }, ['A', 'B', 'A', 'B', 'A', 'B', 'A', 'B', 'A', 'B']);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);
        Log::startTimer();

        $threads = [];

        foreach ($this->processes as $process) {
            $threads[] = async($process);
        }

        Log::debug("Main thread");

        $output = wait($threads);

        d($output);

        Log::debug('Done');
        Log::endTimer();
    }
}

function async(CallbackProcess $process) {
    socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets);
    [$parentSocket, $childSocket] = $sockets;

    if (($pid = pcntl_fork()) == 0) {
        socket_close($childSocket);
        socket_write($parentSocket, serialize($process->execute()));
        socket_close($parentSocket);

        exit;
    } else {
        socket_close($parentSocket);

        return [$pid, $childSocket];
    }
}

function wait($threadHandlers) {
    $output = [];

    while (pcntl_waitpid(0, $status) != -1) {
        $status = pcntl_wexitstatus($status);
    }

    foreach ($threadHandlers as $threadHandler) {
        $output[] = unserialize(socket_read($threadHandler[1], 4096));
        socket_close($threadHandler[1]);
    }

    return $output;
}

