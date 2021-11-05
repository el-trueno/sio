<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCrudController extends AbstractCrudController
{
    /** @var UserPasswordEncoderInterface */
    private $encoder;

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            Field::new('email'),
            Field::new('name'),
            Field::new('plainPassword'),
            ChoiceField::new('roles')->allowMultipleChoices(true)->setChoices(['"ROLE_USER"' => 'ROLE_USER', '"ROLE_ADMIN"' => 'ROLE_ADMIN']),
            Field::new('isDeleted'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->setPermission(Action::DELETE, 'ROLE_ADMIN')->setPermission(Action::INDEX, 'ROLE_ADMIN');
    }

    private function setUserPlainPassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        }
    }

    public function persistUserEntity(User $user): void
    {
        $this->setUserPlainPassword($user);

        $this->persistEntity($user);
    }

    public function updateUserEntity(User $user): void
    {
        $this->setUserPlainPassword($user);

        $this->updateEntity($user);
    }

    public function setEncoder(UserPasswordEncoderInterface $encoder): void
    {
        $this->encoder = $encoder;
    }
}
