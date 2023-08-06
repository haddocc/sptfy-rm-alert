<?php

namespace App\Services;

use App\Services\StringEncryption\SecretScopeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpKernel\KernelInterface;

class StringEncryptionService
{
    protected Application $application;

    public function __construct(KernelInterface $kernel, protected Filesystem $filesystem)
    {
        //TODO think if we can make sure this doesn't have to happen every instantiation
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }

    /**
     * Encrypt string using the secrets service
     * @throws Exception
     */
    public function encrypt(string $key, string $value): bool
    {
        $tmpFilename = 'ses_' . bin2hex(random_bytes(5));
        $tmpFilePath = Path::normalize(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpFilename);

        $this->filesystem->dumpFile(
            $tmpFilePath,
            $value
        );

        $inputArgs = [
            'command' => 'secrets:set',
            'name' => $key,
            'file' => $tmpFilePath
        ];

        $input = new ArrayInput($inputArgs);
        $input->setInteractive(false);

        $output = new NullOutput();

        $exitCode = $this->application->run($input, $output);

        $this->filesystem->remove($tmpFilePath);

        return $exitCode === 0;
    }

    /**
     * Decrypt all secrets to local vault and return value of specified key
     * @throws Exception
     */
    public function decrypt(string $key): string
    {
        $input = new ArrayInput([
            'command' => 'secrets:decrypt-to-local'
        ]);
        $output = new NullOutput();
        //TODO mitigate failure
        $exitCode = $this->application->run($input, $output);
        return $_ENV[$key];
    }

    /**
     * Remove a secret with specified key in specified scope
     * @throws Exception
     */
    public function remove(string $key, SecretScopeEnum $scope): bool
    {
        $parameters = $defaultParameters = [
            'command' => 'secrets:remove',
            'name' => $key,
        ];
        $parameters['--local'] = true;

        $inputs = match ($scope) {
            SecretScopeEnum::Local => [new ArrayInput($parameters)],
            SecretScopeEnum::Global => [new ArrayInput($parameters), new ArrayInput($defaultParameters)],
            SecretScopeEnum::All => [new ArrayInput($defaultParameters)],
        };

        $output = new NullOutput();

        foreach ($inputs as $input) {
            $exitCode = $this->application->run($input, $output);
            if ($exitCode !== 0) return false;
        }

        return isset($exitCode) && $exitCode === 0;
    }
    // decrypt string from vault, keep private key separate maybe Hasicorp Vault?
    // keep secrets inshared memory not write to filesystem
    // keep in cache for 5 minutes, to prevent overhead for consecutive calls
}