<?php
// src/Command/CreateUserCommand.php
namespace App\Command;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\String\UnicodeString;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:authorize-user', description: 'Authorizes a new user.', aliases: ['app:auth'])]
class AuthorizeUserCommand extends Command
{
    public function __construct(private readonly ClientRegistry $clientRegistry, private readonly RouterInterface $router, string $name = null)
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
        // set router context to get proper redirect uri base url for a local env
        $context = $this->router->getContext();
        $context->setHttpsPort(8000);

        $helper = $this->getHelper('question');

        $identifierQuestion = new Question('Please provide a unique identifier to store the access token to your Spotify account: ', false);

        $cacheIdentifier = $helper->ask($input, $output, $identifierQuestion);

        if ($cacheIdentifier) {
            $this->initOAuth2($output, $cacheIdentifier);
        } else {
            $output->writeln('No identifier given. exiting...');
        }
        return Command::SUCCESS;
    }

    protected function initOAuth2(OutputInterface $output, string $cacheIdentifier): void
    {
        // Store redirect for manipulation rather than returning immediately
        $redirectResponse = $this->clientRegistry->getClient('spotify')->redirect(['scope' => 'user-library-read']);
        $targetUrl = $redirectResponse->getTargetUrl();

        // Manipulate `redirect_uri` to add a cache identifier so we can reuse access token
        $parsedUrl = parse_url($targetUrl);
        parse_str($parsedUrl['query'], $query);
        $query['redirect_uri'] = $query['redirect_uri'] . '?id=' . $cacheIdentifier;

        // rebuild redirect url
        $newQueryString = http_build_query($query);
        (new UnicodeString($targetUrl))->before('?')->append('?' . $newQueryString);

        $output->writeln('Please visit ' . $redirectResponse->getTargetUrl()
            . ' to grant permissions to read your library of liked tracks.');
    }
}