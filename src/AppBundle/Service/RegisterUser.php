<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 29/05/2018
 * Time: 10:53
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;

class RegisterUser
{

    private $mailer;
    private $locale;
    private $minLength;

    public function __construct($userManager, $tokenStorage, $session)
    {
        $this->userManager    = $userManager;
        $this->tokenStorage    = $tokenStorage;
        $this->session = $session;
    }

    /**
     * This method registers an user in the database manually.
     *
     * @return User
     **/
    public function register($email,$username,$password,$firstName,$lastName,$role){

        $email_exist = $this->userManager->findUserByEmail($email);

        if($email_exist){
            return false;
        }

        $user = $this->userManager->createUser();
        $user->setEnabled(true);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setFirstName($firstName);
        $user->SetLastName($lastName);
        $user->setPlainPassword($password);
        $user->addRole($role);

        $this->userManager->updateUser($user);

        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken($user, $password, "main", array($role));
        $this->tokenStorage->setToken($token);

        $this->session->set('_security_secured_area', serialize($token));

        return $user;
    }
}