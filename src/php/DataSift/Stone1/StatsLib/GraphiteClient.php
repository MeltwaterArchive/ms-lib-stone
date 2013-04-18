<?php

/**
 * Sends statistics to the stats daemon over UDP
 *
 * This is derived from the original Etsy PHP client
 **/

namespace DataSift\Stone\StatsLib;

class GraphiteClient
{
    /**
     * the IPv4 port where carbon normally listens
     */
    const DEFAULT_PORT = 2003;

    /**
     * the hostname / IP address to send graphite network traffic to
     * @var string
     */
    protected $graphiteHost = '127.0.0.1';

    /**
     * the IPv4 port to send graphite network traffic to
     * @var integer
     */
    protected $graphitePort = 2003;

    /**
     * constructor
     *
     * Setup the client, tell it where graphite is located
     *
     * @param string $host
     *        the hostname or IP address of the graphite instance to use
     * @param int $port
     *        the IPv4 port where carbon is listening
     */
    public function __construct($host = null, $port = null)
    {
        if ($host !== null) {
            $this->graphiteHost = $host;
        }

        if ($port !== null) {
            $this->graphitePort = $port;
        }

        // connect to graphite
        $this->fp = fsockopen("tcp://{$this->graphiteHost}", $this->graphitePort, $errno, $errstr);
        if (! $this->fp) {
            throw new RuntimeException("Unable to connect to graphite host '{$this->graphiteHost}:{$this->graphitePort}'");
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
        $this->send(array($stat => $time), $sampleRate);
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
     * @return boolean
     */
    public function updateStats($stats, $delta=1) {
        if (!is_array($stats)) {
            $stats = array($stats);
        }
        $data = array();
        foreach($stats as $stat) {
            $data[$stat] = $delta;
        }

        $this->send($data);
    }

    /**
     * Squirt the metrics over UDP
     */
    public function send($data) {
        $now = time();
        // var_dump('>> SENDING STATS', $sampledData);

        // Wrap this in a try/catch - failures in any of this should be silently ignored
        try {
            foreach ($data as $stat => $value) {
                $this->fp = fsockopen("{$this->graphiteHost}", $this->graphitePort, $errno, $errstr);
                fwrite($this->fp, "stats.{$stat} {$value} {$now}\n");
                fclose($this->fp);
                echo "stats.{$stat} {$value} {$now}\n";
            }
        }
        catch (Exception $e) {
            // do nothing at all
            // fclose($this->fp);

        }
    }
}
