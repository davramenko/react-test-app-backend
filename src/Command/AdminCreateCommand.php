<?php

namespace App\Command;

use App\Service\Cli\CreateUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Add a short description for your command',
)]
class AdminCreateCommand extends Command
{
    public function __construct(
        protected CreateUser $createUser,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED, 'User first name')
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED, 'User last name')
            ->addOption('phone', null, InputOption::VALUE_REQUIRED, 'User phone number')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        if (!$this->createUser->createUser($io, $username, 'ROLE_ADMIN', array_intersect_key(
            $input->getOptions(),
            ['first-name' => '', 'last-name' => '', 'phone' => '']
        ))) {
            return Command::FAILURE;
        }

        $io->success('Admin user has been created');
        return Command::SUCCESS;
    }
}
