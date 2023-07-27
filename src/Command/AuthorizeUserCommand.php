<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:authorize-user', description: 'Authorizes a new user.', aliases: ['app:auth'])]
class AuthorizeUserCommand extends Command
{
    public function __construct(private readonly ClientRegistry $clientRegistry, string $name = null)
    {
        parent::__construct($name);
    }
    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to authorize a user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $emailQuestion = new Question('Please fill in email address of your Spotify account: ', false);

        //TODO: email validation
        $email = $helper->ask($input, $output, $emailQuestion);

        if($email) {
            $output->writeln('You entered: ' . $email);
            $this->initOauth2();
        } else {
            $output->writeln('No email given. exiting...');
        }
        return Command::SUCCESS;
    }

    protected function initOauth2(): void
    {
        dump($this->clientRegistry->getClient('spotify')->redirect(['user-library-read']));
    }
}