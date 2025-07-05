<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        $url = $request->getPathInfo();

        return preg_match("/^\/(v\d*\/)?api\/.+/", $url);
    }

    public function authenticate(Request $request): Passport
    {
        $autorisationHeader = $request->headers->get('Authorization');

        if (empty($autorisationHeader) || $autorisationHeader !== 'skillify') {
            throw new AuthenticationException('Api key invalid');
        }

        return new SelfValidatingPassport(
            new UserBadge('skillify', function (string $username) {
                $user = new User();
                $user->setUsername($username)

                ;

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => $exception->getMessageKey(),
            'code' => $exception->getCode(),
        ];
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
