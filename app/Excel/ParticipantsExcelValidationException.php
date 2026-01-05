<?php

namespace Masso\Excel;

use Illuminate\Support\MessageBag;

class ParticipantsExcelValidationException extends \RuntimeException
{
    /** @var MessageBag */
    protected $bag;

    public function __construct(MessageBag $bag, $message = 'Invalid participants excel')
    {
        parent::__construct($message);
        $this->bag = $bag;
    }

    public function getBag()
    {
        return $this->bag;
    }
}
