<?php

/**
 * Sends statistics to the stats daemon over UDP
 *
 * This is derived from the original Etsy PHP client
 **/

namespace DataSift\Stone\StatsLib;

class BufferedStatsdClient
{
    protected $client;

    protected $stats = array();

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
        $this->client = new StatsdClient($host, $port);
    }

    /**
     * Log timing information
     *
     * @param string $stats The metric to in log timing info for.
     * @param float $time The ellapsed time (ms) to log
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     */
    public function timing($stat, $time, $sampleRate=1) {
        if (!isset($this->stats[$stat])) {
            $this->stats[$stat] = array();
        }
        $this->stats[$stat][] = array("type" => 'ms', "value" => "$time");
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
        foreach($stats as $stat) {
            if (!isset($this->stats[$stat])) {
                $this->stats[$stat] = array("type" => 'c', "value" => 0);
            }

            $this->stats[$stat]['value'] = $this->stats[$stat]['value'] + $delta;
        }
    }

    /**
     * call this when you're ready to send the data
     *
     * @return void
     */
    public function send() {
        foreach ($this->stats as $name => $data) {
            $this->client->send(array($name => $data['value'].'|'. $data['type']));
        }

        // now, reset our list
        $this->stats = array();
    }
}
