<?php

namespace Brendt\Command;

use Symfony\Component\Console\Output\OutputInterface;

class Log {

    /**
     * @var OutputInterface
     */
    private static $output;

    /**
     * @var float
     */
    private static $startTime = 0.0;

    /**
     * @var float
     */
    private static $endTime = 0.0;

    public static function setOutput(OutputInterface $output) {
        self::$output = $output;
    }

    public static function debug(string ...$messages) {
        if (!self::$output) {
            throw new \Exception('No output interface set to log to.');
        }

        foreach ($messages as $message) {
            $time = date('Y-m-d H:i:s');
            self::$output->write("\n<fg=green>[DEBUG]</> {$time} - {$message}\n");
        }
    }

    public static function startTimer() {
        self::$output->writeln("<fg=blue>[TIMER]</> started");

        self::$startTime = microtime(true);
    }

    public static function endTimer() {
        self::$endTime = microtime(true);

        $time = round(self::$endTime - self::$startTime, 3);

        self::$output->writeln("<fg=blue>[TIMER]</> ended: {$time}s");
    }

}
