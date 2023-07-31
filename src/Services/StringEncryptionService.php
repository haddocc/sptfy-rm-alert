<?php

namespace App\Services;

use App\Overrides\ReadStdinArrayInput;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class StringEncryptionService
{
    protected Application $application;
    public function __construct(KernelInterface $kernel) {
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }
    // encrypt string

    /**
     * @throws Exception
     */
    public function encrypt(string $key, string $value): void
    {
        // Mimic STDIN
        $stdInput = "echo -n '{$value}' | ";

        $inputArgs = [
            'command' => 'secrets:set',
            // (optional) define the value of command arguments
            $key => null
        ];

        // Create ChainInput by combining standard input and ArrayInput
        $readStdinInput = new ReadStdinArrayInput($inputArgs);
        $readStdinInput->setStandardInput($stdInput);

        // You can use NullOutput() if you don't need the output
        $output = new NullOutput();

        $this->application->run($readStdinInput, $output);
    }
    // keep in cache for 5 minutes, to prevent overhead for consecutive calls
    // decrypt string from vault
}