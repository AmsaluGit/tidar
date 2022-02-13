<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }
        $this->user=$user;

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
        //     return new RedirectResponse($targetPath);
        // }






        $user = $this->user;
        $role = $user->getRoles()[0];
        // if ($role === "ROLE_USER") {
        // } else {
        //     if (!$user->getLastLogin()) {
        //         return new RedirectResponse($this->urlGenerator->generate('change_password'));
        //     } elseif ($user->getIsActive()==false) {
        //         return new RedirectResponse($this->urlGenerator->generate('user_show', ['id'=>$user->getId()]));
        //     }
        // }

        // $this->user->setLastLogin(new \DateTime());
        // $this->entityManager->flush();
        $permissions=[];

        if ($user->getId()==1) {
            $permission=$this->entityManager->getRepository(Permission::class)->findAll();
            foreach ($permission as $key => $value1) {
                $permissions[]=$value1->getCode();
            }
        } else {
            //role to be added
            // $groups=$this->entityManager->getRepository(UserGroup::class)->findBy(['users'=>$this->user,'isActive'=>1]) ;
            $groups=$this->user->getUserGroup();
            // addUserGroup
            // dd($groups);
            foreach ($groups as $key => $value) {
                if (!$value->getIsActive()) {
                    continue;
                }
                $permission=$value->getPermission();

                foreach ($permission as $key => $value1) {
                    $permissions[]=$value1->getCode();
                }
            }
        }
        $request->getSession()->set(
            "PERMISSION",
            $permissions
        );


        if($role === "ROLE_USER")
         {
            return new RedirectResponse($this->urlGenerator->generate('home'));
         }
         else
         {
             return new RedirectResponse($this->urlGenerator->generate('user_index'));
         }


        //   else if($role == "ROLE_ADMIN")






        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        return new RedirectResponse($this->urlGenerator->generate('user_index'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
