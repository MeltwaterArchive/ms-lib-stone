<?php

/**
 * Stone - A PHP Library
 *
 * PHP Version 5.3
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  Libraries
 * @package   Stone
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://mediasift.com/licenses/internal MediaSift Internal License
 * @version   SVN: $Revision: 2496 $
 * @link      http://www.mediasift.com
 */

/**
 * A static proxy around the underlying logger
 *
 * @category Libraries
 * @package  Stone
 * @author   Stuart Herbert <stuart.herbert@datasift.com>
 * @license  http://mediasift.com/licenses/internal MediaSift Internal License
 * @link     http://www.mediasift.com
 */

namespace DataSift\Stone\LogLib;

use Exception;

class LogStdErr extends BaseLogger
{
    protected $fp;

    public function init($processName, $pid)
    {
        parent::init($processName, $pid);
        $this->openStderr();
    }

    public function openStderr()
    {
        $this->fp = fopen("php://stderr", "w");
        if (!$this->fp)
        {
            throw new E5xx_LogWriteFailure("Unable to open stderr for output");
        }
    }

    public function write($logLevel, $message, $cause = null)
    {
        $now = date('Y-m-d H:i:s', time());

        fwrite($this->fp, '[' . $now . '] [' . $this->processName . ':' . $this->pid . '] ' . $this->prefixes[$logLevel] . $message . "\n");
        fflush($this->fp);
    }
}