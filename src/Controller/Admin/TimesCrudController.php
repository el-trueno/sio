<?php

namespace App\Controller\Admin;

use App\Entity\Times;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class TimesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Times::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user'),
            AssociationField::new('project'),
            DateTimeField::new('startedAt'),
            DateTimeField::new('finishedAt'),
            Field::new('isDeleted'),
            Field::new('diff')->hideOnForm()
        ];
    }
}
