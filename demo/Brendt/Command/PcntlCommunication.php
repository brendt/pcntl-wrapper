<?php

namespace Brendt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Brendt\Pcntl\CallbackProcess;

class PcntlCommunication extends Command
{
    /** @var CallbackProcess[] */
    private $processes;

    public function __construct() {
        parent::__construct('pcntl:communication');

        $processCallback = function (CallbackProcess $process) {
            Log::debug("Starting {$process->getName()}");
            sleep(1);
            Log::debug("Ending {$process->getName()}");

            pcntl_signal(1, function () use ($process) {
                Log::debug("Signal sent from {$process->getName()}");
            });
            exit;
        };

        /** @var CallbackProcess[] $processes */
        $this->processes = array_map(function ($name) use ($processCallback) {
            $process = new CallbackProcess($processCallback);
            $process->setName($name);

            return $process;
        }, ['A', 'B']);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);
        Log::startTimer();

        $ary = [];
        $strone = 'Message From Parent.';
        $strtwo = 'Message From Child.';

        if (socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $ary) === false) {
            Log::debug("socket_create_pair() failed. Reason: " . socket_strerror(socket_last_error()));

            exit;
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            Log::debug('Could not fork Process.');

            exit;
        } elseif ($pid) {
            /*parent*/
            socket_close($ary[0]);

            if (socket_write($ary[1], $strone, strlen($strone)) === false) {
                Log::debug("socket_write() failed. Reason: " . socket_strerror(socket_last_error($ary[1])));
            }

            if (socket_read($ary[1], strlen($strtwo), PHP_BINARY_READ) == $strtwo) {
                Log::debug("Recieved $strtwo\n");
            }

            socket_close($ary[1]);
        } else {
            /*child*/
            socket_close($ary[1]);

            if (socket_write($ary[0], $strtwo, strlen($strtwo)) === false) {
                Log::debug("socket_write() failed. Reason: " . socket_strerror(socket_last_error($ary[0])));
            }

            if (socket_read($ary[0], strlen($strone), PHP_BINARY_READ) == $strone) {
                Log::debug("Recieved $strone\n");
            }

            socket_close($ary[0]);
        }

        Log::endTimer();
    }

}
