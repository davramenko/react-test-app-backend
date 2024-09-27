<?php

namespace App\Validator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CheckPasswordValidator extends ConstraintValidator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected Security $security,
        protected UserPasswordHasherInterface $userPasswordHasher,
    ) {
        //
    }

    /**
     * @noinspection PhpParamsInspection
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var CheckPassword $constraint */
        $this->logger->info(json_encode($constraint->payload));
        // TODO: implement the validation here
        if (
            null === $value ||
            '' === $value ||
            empty($this->security?->getUser()) ||
            !$this->userPasswordHasher->isPasswordValid($this->security->getUser(), $value)
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
