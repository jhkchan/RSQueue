<?php

/*
 * This file is part of the RSQueue library
 *
 * Copyright (c) 2016 - now() Marc Morera
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace RSQueue\Services;

use RSQueue\Event\RSQueuePublisherEvent;
use RSQueue\RSQueueEvents;
use RSQueue\Services\Abstracts\AbstractService;

/**
 * Publisher class.
 */
class Publisher extends AbstractService
{
    /**
     * Enqueues payload inside desired queue.
     *
     * @param string $channelAlias Name of channel to publish payload
     * @param mixed  $payload      Data to publish
     *
     * @return Producer self Object
     */
    public function publish($channelAlias, $payload)
    {
        $channel = $this
            ->queueAliasResolver
            ->getQueue($channelAlias);

        $payloadSerialized = $this
            ->serializer
            ->apply($payload);

        $this
            ->redis
            ->publish(
                $channel,
                $payloadSerialized
            );

        /*
         * Dispatching publisher event...
         */
        $publisherEvent = new RSQueuePublisherEvent(
            $payload,
            $payloadSerialized,
            $channel,
            $this->redis
        );

        $this
            ->eventDispatcher
            ->dispatch(
                $publisherEvent,
                RSQueueEvents::RSQUEUE_PUBLISHER
            );

        return $this;
    }
}
