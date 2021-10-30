<?php

namespace famima65536\chaseplayer\chase;

class TerminateCondition{
    public function __construct(
        private bool $whenGetOff = false, 
        private ?int $chaseTime = null,
        private bool $whenTargetDie = false
    )
    {}
    
    public function whenGetOff(): bool{
        return $this->whenGetOff;
    }

    public function chaseTime(): ?int{
        return $this->chaseTime;
    }

    public function whenTargetDie(): bool{
        return $this->whenTargetDie;
    }
}