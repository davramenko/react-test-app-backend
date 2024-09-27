<?php

namespace App\Service\Api\Normalizer;

use ArrayObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JsonLdNormalizer
{
    public function __construct(
        protected NormalizerInterface $normalizer
    ) {
        //
    }

    /**
     * @param mixed $object
     * @param array $groups
     * @param string $format
     * @return string|array|ArrayObject|bool|int|float|null
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, array $groups = [], string $format = 'jsonld'): string|array|ArrayObject|bool|int|null|float
    {
        if (false === empty($groups)) {
            $context = ['groups' => $groups];
        } else {
            $context = [];
        }

        return $this->normalizer->normalize($object, $format, $context);
    }
}