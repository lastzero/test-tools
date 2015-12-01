<?php

namespace TestTools\Profiler;

/**
 * Lightweight profiler featuring low performance overhead and runtime aggregation
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class Profiler
{
    protected static $steps = array();
    protected static $aggregated = array();
    public static $labelWidth = 100;

    /**
     * Start profiling
     *
     * @param string $label
     */
    public static function start($label = 'Start') {
        self::addStep($label);
    }

    /**
     * End profiling
     */
    public static function stop() {
        self::addStep('Done');
    }

    /**
     * Add profiling step (split time)
     *
     * @param string $label
     * @param bool $silent Don't output this step (see getResultAsTable())
     */
    public static function addStep($label = '', $silent = false) {
        $diff = 0;
        $total = 0;
        $time = microtime(true);

        if(count(self::$steps) != 0) {
            $lastStep = end(self::$steps);
            $diff = $time - $lastStep['time'];
            $total = $lastStep['total'] + $diff;
        }

        self::$steps[] = array('label' => $label, 'time' => $time, 'diff' => $diff, 'total' => $total, 'silent' => $silent);
    }

    /**
     * Add step without displaying the time (see getResultAsTable())
     *
     * @param string $label
     */
    public static function addSilentStep($label = '') {
        self::addStep($label, true);
    }

    /**
     * Start aggregation
     *
     * @param string $label
     * @param string $sublabel
     */
    public static function startAggregate($label, $sublabel = '') {
        $time = microtime(true);

        if(empty(self::$aggregated[$label])) {
            self::$aggregated[$label] = array('total' => 0, 'start' => $time, 'count' => 0, 'sub' => array());
        } else {
            self::$aggregated[$label]['start'] = $time;
        }

        if($sublabel) {

            if(empty(self::$aggregated[$label]['sub'][$sublabel])) {
                self::$aggregated[$label]['sub'][$sublabel] = array('total' => 0, 'start' => $time, 'count' => 0);
            } else {
                self::$aggregated[$label]['sub'][$sublabel]['start'] = $time;
            }
        }
    }

    /**
     * Stop aggregation
     *
     * @param $label
     * @param string $sublabel
     */
    public static function stopAggregate($label, $sublabel = '') {
        $time = microtime(true);

        if(!empty(self::$aggregated[$label]) && self::$aggregated[$label]['start'] > 0) {
            self::$aggregated[$label]['total'] += $time - self::$aggregated[$label]['start'];
            self::$aggregated[$label]['start'] = 0;
            self::$aggregated[$label]['count']++;

            if($sublabel && !empty(self::$aggregated[$label]['sub'][$sublabel]) && self::$aggregated[$label]['sub'][$sublabel]['start'] > 0) {
                self::$aggregated[$label]['sub'][$sublabel]['total'] += $time - self::$aggregated[$label]['sub'][$sublabel]['start'];
                self::$aggregated[$label]['sub'][$sublabel]['start'] = 0;
                self::$aggregated[$label]['sub'][$sublabel]['count']++;
            }
        }
    }

    /**
     * Clear all results
     */
    public static function clear() {
        self::$steps = array();
        self::$aggregated = array();
    }

    protected static function stringPad($input, $pad_length, $pad_string = ' ', $pad_style = STR_PAD_RIGHT, $encoding = 'UTF-8') {
        return str_pad($input, strlen($input) - mb_strlen($input,$encoding)+$pad_length, $pad_string, $pad_style);
    }

    protected static function padLabel ($label, $width) {
        $result = substr(trim(preg_replace('!\s+!', ' ', strtr($label, array("\n" => ' ', "\t" => ' ')))), 0, $width);
        $result = self::stringPad($result, $width);

        return $result;
    }

    protected static function padSublabel ($sublabel, $width) {
        $result = substr(trim(preg_replace('!\s+!', ' ', strtr($sublabel, array("\n" => ' ', "\t" => ' ')))), 0, $width - 2);
        $result = self::stringPad('  ' . $result, $width);

        return $result;
    }

    /**
     * Return profiling results as plain text table
     *
     * @return string
     */
    public static function getResultAsTable () {
        $labelWidth = self::$labelWidth;

        $result = str_pad('Step', $labelWidth) . ' '
            . str_pad('Total (ms)', 10, ' ', STR_PAD_LEFT) . ' '
            . str_pad('Diff (ms)', 10, ' ', STR_PAD_LEFT) . ' '
            . str_pad('Diff (%)', 10, ' ', STR_PAD_LEFT) . "\n";

        $lastStep = end(self::$steps);
        $totalTime = $lastStep['total'];

        foreach(self::$steps as $step) {
            if($step['silent']) continue;

            $result .= self::padLabel($step['label'], $labelWidth) . ' '
                . str_pad(round($step['total'] * 1000), 10, ' ', STR_PAD_LEFT) . ' '
                . str_pad(number_format($step['diff'] * 1000, 1), 10, ' ', STR_PAD_LEFT) . ' '
                . str_pad(number_format(($step['diff'] / $totalTime) * 100, 1), 10, ' ', STR_PAD_LEFT) . "\n";
        }

        if(count(self::$aggregated) > 0) {
            ksort(self::$aggregated);
            $result .= "\n" . str_pad('Aggregated', $labelWidth) . ' '
                . str_pad('Count', 10, ' ', STR_PAD_LEFT) . ' '
                . str_pad('Time (ms)', 10, ' ', STR_PAD_LEFT) . ' '
                . str_pad('Time (%)', 10, ' ', STR_PAD_LEFT) . "\n";

            foreach(self::$aggregated as $label => $values) {
                if($values['count'] == 0) continue;

                $result .= self::padLabel($label, $labelWidth) . ' '
                    . str_pad($values['count'], 10, ' ', STR_PAD_LEFT) . ' '
                    . str_pad(number_format($values['total'] * 1000, 1), 10, ' ', STR_PAD_LEFT) . ' '
                    . str_pad(number_format(($values['total'] / $totalTime) * 100, 1), 10, ' ', STR_PAD_LEFT) . "\n";

                ksort($values['sub']);

                foreach($values['sub'] as $sublabel => $subvalues) {
                    $result .= self::padSublabel($sublabel, $labelWidth) . ' '
                        . str_pad($subvalues['count'], 10, ' ', STR_PAD_LEFT) . ' '
                        . str_pad(number_format($subvalues['total'] * 1000, 1), 10, ' ', STR_PAD_LEFT) . ' '
                        . str_pad(number_format(($subvalues['total'] / $totalTime) * 100, 1), 10, ' ', STR_PAD_LEFT) . "\n";
                }
            }
        }

        return $result;
    }

    /**
     * Append profiling results to file (as plain text table)
     *
     * @param $filename
     */
    public static function appendResultToFile ($filename) {
        $result = self::getResultAsTable();
        file_put_contents($filename, $result, FILE_APPEND);
    }

    /**
     * Write profiling results to new file (as plain text table)
     *
     * @param $filename
     */
    public static function writeResultToFile ($filename) {
        $result = self::getResultAsTable();
        file_put_contents($filename, $result);
    }

    /**
     * Determine caller function using debug_backtrace()
     *
     * @param int $level
     * @return mixed
     */
    public static function getCallerFunction ($level = 2) {
        $callers = debug_backtrace();
        return $callers[$level]['function'];
    }

    /**
     * Determine caller method using debug_backtrace()
     *
     * @param int $level
     * @return mixed
     */
    public static function getCallerMethod ($level = 2) {
        $callers = debug_backtrace();

        if(isset($callers[$level]['class'])) {
            $class = $callers[$level]['class'] . '::';
        } else {
            $class = 'function ';
        }

        return $class . $callers[$level]['function'];
    }
}