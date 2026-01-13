<?php

namespace LiveWave\Broadcasting;

use Illuminate\Broadcasting\Channel;

class LiveWaveChannel extends Channel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    /**
     * Convert the channel to a string.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
