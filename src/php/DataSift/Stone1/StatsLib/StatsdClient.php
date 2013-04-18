<?php

/**
 * Sends statistics to the stats daemon over UDP
 *
 * This is derived from the original Etsy PHP client
 **/

namespace DataSift\Stone\StatsLib;

class StatsdClient
{
    /**
     * the IPv4 port where statsd normally runs
     */
    const DEFAULT_PORT = 8123;

    /**
     * the hostname / IP address to send statsd network traffic to
     * @var string
     */
    protected $statsdHost = '127.0.0.1';

    /**
     * the IPv4 port to send statsd network traffic to
     * @var integer
     */
    protected $statsdPort = 8123;

    /**
     * constructor
     *
     * Setup the client, tell it where statsd is located
     *
     * @param string $host
     *        the hostname or IP address of the statsd instance to use
     * @param int $port
     *        the IPv4 port where statsd is listening
     */
    public function __construct($host = null, $port = null)
    {
        if ($host !== null) {
            $this->statsdHost = $host;
        }

        if ($port !== null) {
            $this->statsdPort = $port;
        }

        // connect to statsd
        $this->fp = fsockopen("udp://{$this->statsdHost}", $this->statsdPort, $errno, $errstr);
        if (! $this->fp) {
            throw new RuntimeException("Unable to connect to statsd host '{$this->statsdHost}:{$this->statsdPort}'");
        }
    }

    /**
     * Log timing information
     *
     * @param string $stats The metric to in log timing info for.
     * @param float $time The ellapsed time (ms) to log
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     */
    public function timing($stat, $time, $sampleRate=1) {
        $this->send(array($stat => "$time|ms"), $sampleRate);
    }

    /**
     * Increments one or more stats counters
     *
     * @param string|array $stats The metric(s) to increment.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function increment($stats, $sampleRate=1) {
        $this->updateStats($stats, 1, $sampleRate);
    }

    /**
     * Decrements one or more stats counters.
     *
     * @param string|array $stats The metric(s) to decrement.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function decrement($stats, $sampleRate=1) {
        $this->updateStats($stats, -1, $sampleRate);
    }

    /**
     * Updates one or more stats counters by arbitrary amounts.
     *
     * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
     * @param int|1 $delta The amount to increment/decrement each metric by.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function updateStats($stats, $delta=1, $sampleRate=1) {
        if (!is_array($stats)) {
            $stats = array($stats);
        }
        $data = array();
        foreach($stats as $stat) {
            $data[$stat] = "$delta|c";
        }

        $this->send($data, $sampleRate);
    }

    /**
     * Squirt the metrics over UDP
     */
    public function send($data, $sampleRate=1) {
        // sampling
        $sampledData = array();

        if ($sampleRate < 1) {
            foreach ($data as $stat => $value) {
                if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
                    $sampledData[$stat] = "$value|@$sampleRate";
                }
            }
        } else {
            $sampledData = $data;
        }

        if (empty($sampledData)) { return; }

        // var_dump('>> SENDING STATS', $sampledData);

        // Wrap this in a try/catch - failures in any of this should be silently ignored
        try {
            foreach ($sampledData as $stat => $value) {
                fwrite($this->fp, "$stat:$value");
            }
        }
        catch (Exception $e) {
            // do nothing at all
        }
    }
}
