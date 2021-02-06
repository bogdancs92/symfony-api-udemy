<?php


namespace App\Serializer;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class UserAttributeNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private $tokenStorage;
    const USER_ATTRIBUTE_ORMALIZER_ALREADY_CALLED = 'USER_ATTRIBUTE_ORMALIZER_ALREADY_CALLED';

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        if (isset($context[self::USER_ATTRIBUTE_ORMALIZER_ALREADY_CALLED])) {
            return false;
        }
        return $data instanceof User;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        // TODO: Implement normalize() method.
        if ($this->isUserHimself($object)) {
            $context['groups'][] = 'get-owner';
        }
        return $this->passOn($object,$format,$context);
    }

    private function isUserHimself($object)
    {
        $token = $this->tokenStorage->getToken();
        $author=$token->getUsername();
        if ($object->getUsername() === $author) {
            return true;
        }
        return false;
    }

    private function passOn($object, string $format, array $context)
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException(sprintf("Cannot normalize object because the injected object is not a normalizer interface",$object));
        }
        $context[self::USER_ATTRIBUTE_ORMALIZER_ALREADY_CALLED] = true;
        return $this->serializer->normalize($object,$format,$context);
    }

}