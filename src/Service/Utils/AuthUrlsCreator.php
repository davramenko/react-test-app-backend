<?php

namespace App\Service\Utils;

use DateTime;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class AuthUrlsCreator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected int    $expirationGap,
        protected string $authSignatureSecret,
        protected string $frontendBaseUrl,
        protected array  $authUris,
    ) {
        //
    }

    public function getEmailConfirmationUrl(string $email): string
    {
        $data = [
            'email' => $email,
            'expiration' => time() + $this->expirationGap
        ];
        $this->signQuery($data, $this->authUris['confirm-email']);
        return $this->frontendBaseUrl . $this->authUris['confirm-email'] . '?' . http_build_query($data, '', '&');
    }

    public function verifyEmailConfirmationData(array $parameters): bool
    {
        if (empty($parameters['email'])) {
            $this->logger->warning('verifyEmailConfirmationData@AuthUrlsCreator: no email');
            return false;
        }
        if (empty($parameters['signature'])) {
            $this->logger->warning('verifyEmailConfirmationData@AuthUrlsCreator: no signature');
            return false;
        }
        if (empty($parameters['expiration'])) {
            $this->logger->warning('verifyEmailConfirmationData@AuthUrlsCreator: no expiration');
            return false;
        }
        if (!$this->checkSignature($parameters, $this->authUris['confirm-email'])) {
            $this->logger->warning("verifyEmailConfirmationData@AuthUrlsCreator: signature doesn't match");
            return false;
        }
        if (intval($parameters['expiration']) < time()) {
            $expiration = (new DateTime())->setTimestamp(intval($parameters['expiration']));
            $this->logger->warning('verifyEmailConfirmationData@AuthUrlsCreator: signature expired: expiration: ' . $expiration->format('Y-m-d H:i:s'));
            return false;
        }
        return true;
    }

    protected function signQuery(array &$query, string $action): void
    {
        $query['signature'] = $this->calculateSignature($query, $action);
    }

    protected function calculateSignature(array &$data, string $action): string
    {
        ksort($data);
        $string2sign = array_reduce(array_keys($data), function ($acc, $key) use ($data) {
            return $acc . (strlen($acc) > 0 ? '&' : '') . $key . '=' . $data[$key];
        }, '');
        $this->logger->info('calculateSignature@AuthUrlsCreator: string2sign: ' . $string2sign);
        $secret = hash('sha256',
            $this->authSignatureSecret . ':' . $action,
            true
        );
        $this->logger->info('calculateSignature@AuthUrlsCreator: secret: ' . bin2hex($string2sign));
        return $this->base64UrlEncode(
            hash_hmac('sha256', $string2sign, $secret, true)
        );
    }

    protected function checkSignature(array $data, string $action): bool
    {
        if (empty($data['signature'])) {
            return false;
        }
        $signature = $data['signature'];
        unset($data['signature']);
        $calculatedSignature = $this->calculateSignature($data, $action);
        return strcmp($signature, $calculatedSignature) === 0;
    }

    protected function base64UrlEncode($input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * @noinspection PhpUnused
     */
    function base64UrlDecode($input): string
    {
        $length = strlen($input);
        if (($remainder = $length % 4) !== 0) {
            $length += 4 - $remainder;
        }
        return base64_decode(str_pad(strtr($input, '-_', '+/'), $length, '='));
    }
}