<?php


namespace App\Security;


use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use Symfony\Component\Security\Core\Authentication\Provider\PreAuthenticatedAuthenticationProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends JWTTokenAuthenticator
{
    /**
     * @param PreAuthenticationJWTUserToken $preAuthToken
     * @param UserProviderInterface $userProvider
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        /**
         * @var User $user
         */
        $user = parent::getUser($preAuthToken, $userProvider);

        //var_dump($preAuthToken->getPayload());die();
        if ($user->getPasswordChangedDate() && $preAuthToken->getPayload()['iat'] <$user->getPasswordChangedDate()) {
            throw new ExpiredTokenException();
        }
        return $user;
    }

}