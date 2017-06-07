<?php

namespace Brendt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcntlSingle extends Command {

    public function __construct() {
        parent::__construct('pcntl:single');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Log::setOutput($output);

        $pid = pcntl_fork();

        if ($pid == -1) {
            Log::debug('Could not fork :(');

            exit;
        } else if ($pid) {
            Log::debug('Waiting');

            pcntl_wait($status);

            Log::debug('Done');
        } else {
            Log::debug('Child');
            sleep(1);
        }
    }

}
