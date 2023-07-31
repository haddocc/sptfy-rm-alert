<?php

namespace App\Overrides;

use Symfony\Component\Console\Input\ArrayInput;

class ReadStdinArrayInput extends ArrayInput
{
    private string $stdInput;

    public function setStandardInput(string $stdInput): void
    {
        $this->stdInput = $stdInput;
    }

    public function getFirstArgument(): ?string
    {
        return $this->stdInput ?: parent::getFirstArgument();
    }
}