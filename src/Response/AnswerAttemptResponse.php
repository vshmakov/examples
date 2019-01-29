<?php

namespace App\Response;

final class AnswerAttemptResponse
{
    /**
     * @var bool|null
     */
    private $isAnswerRight;

    /**
     * @var AttemptResponse
     */
    private $attempt;

    public function __construct(?bool $isAnswerRight, AttemptResponse $attempt)
    {
        $this->isAnswerRight = $isAnswerRight;
        $this->attempt = $attempt;
    }

    public function isAnswerRight(): ?bool
    {
        return $this->isAnswerRight;
    }

    public function getAttempt(): AttemptResponse
    {
        return $this->attempt;
    }
}
