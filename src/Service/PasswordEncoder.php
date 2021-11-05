<?php


namespace App\Service;


use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class PasswordEncoder
{
    public static function encodePassword(User $user, string $password): string
    {
        $passwordEncodedFactory = new EncoderFactory([
            User::class => new MessageDigestPasswordEncoder('sha512', true, 5000)
        ]);
        $encoder = $passwordEncodedFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }
}