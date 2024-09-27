<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\Utils\AuthUrlsCreator;
use App\Service\Utils\MailerUtility;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:registration-email:send',
    description: 'Just to test the email sending',
)]
class AppEmailTestCommand extends Command
{
    public function __construct(
        protected MailerUtility $utility,
        protected UserRepository $userRepository,
        protected MailerInterface $mailer,
        protected AuthUrlsCreator $authUrlsCreator,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'An email address');
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailAddress = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $emailAddress]);
        if (!$user) {
            $io->error("User not found");
            return Command::FAILURE;
        }
        if ($user->getEmailVerifiedAt() !== null) {
            $io->error("User is already verified");
            return Command::FAILURE;
        }

        $email = (new TemplatedEmail())
            ->from($this->utility->getSender())
            ->to($emailAddress)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            //->text('Sending emails is fun again!')
            //->html('<p>See Twig integration for better HTML integration!</p>');
            ->htmlTemplate('email/registration_confirmation.html.twig')
            ->context([
                'user' => $user,
                'url' => $this->authUrlsCreator->getEmailConfirmationUrl($user->getEmail()),
            ]);
        $this->mailer->send($email);

        $io->success('Email has been sent.');

        return Command::SUCCESS;
    }
}
