<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JWTAuthenticator extends AbstractAuthenticator
{
    private JWTTokenManagerInterface $jwtManager;
    private UserProviderInterface $userProvider;

    public function __construct(JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider)
    {
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');
        $jwt = str_replace('Bearer ', '', $authHeader);

        $data = $this->jwtManager->parse($jwt); 
        if (!$data || !isset($data['username'])) {
            throw new AuthenticationException('Invalid token');
        }

        return new SelfValidatingPassport(
            new UserBadge($data['username'], function ($username) {
                return $this->userProvider->loadUserByIdentifier($username);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        return new JsonResponse([
            'message' => 'Authentication successful',
            'user' => [
                'username' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'message' => 'Authentication failed',
            'error' => $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}