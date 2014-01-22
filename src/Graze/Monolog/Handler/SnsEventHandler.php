<?php
/*
 * This file is part of Monolog Extensions
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <http://graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/MonologExtensions/blob/master/LICENSE
 * @link http://github.com/graze/MonologExtensions
 */
namespace Graze\Monolog\Handler;

use Aws\Common\Aws;
use Aws\Sns\SnsClient;
use Graze\Monolog\Formatter\JsonDateAwareFormatter;
use Graze\Monolog\Handler\AbstractEventHandler;
use Monolog\Logger;

class SnsEventHandler extends AbstractEventHandler
{
    const DATE_FORMAT = 'Y-m-d\TH:i:s.uO';

    public function __construct(SnsClient $client, $topic, $level = Logger::DEBUG, $bubble = true)
    {
        if (!defined('Aws\Common\Aws::VERSION') || version_compare('3.0', Aws::VERSION, '<=')) {
            throw new \RuntimeException('The SnsHandler is only known to work with the AWS SDK 2.x releases');
        }

        $this->client = $client;
        $this->topic = $topic;

        parent::__construct($level, $bubble);
    }


    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {

        $this->client->publish(array(
            'TopicArn' => $this->topic,
            'Message' => $record['formatted'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter()
    {
        return new JsonDateAwareFormatter(self::DATE_FORMAT);
    }
}
