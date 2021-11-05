<?php


namespace App\EventSubscriber;


use App\Entity\User;
use App\Service\PasswordEncoder;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['persistUserEntity'],
            BeforeEntityUpdatedEvent::class => ['updateUserEntity']
        ];
    }

    private function setUserPlainPassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $encodedPassword = PasswordEncoder::encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);
        }
    }

    public function persistUserEntity(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof User) {
            return;
        }
        $this->setUserPlainPassword($entity);
    }

    public function updateUserEntity(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof User) {
            return;
        }
        $this->setUserPlainPassword($entity);
    }
}