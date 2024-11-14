<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class NewsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Новости')
            ->setEntityLabelInSingular('Новость')
            ->setDefaultSort(['publishedAt' => 'DESC'])
            ->setSearchFields(['title', 'category.name', 'source.name']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Заголовок'),
            TextEditorField::new('content', 'Контент'),
            AssociationField::new('category', 'Категория'),
            AssociationField::new('source', 'Источник'),
            UrlField::new('link', 'Ссылка'),
            DateTimeField::new('publishedAt', 'Дата публикации')->setFormat('short', 'short'),
        ];
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('source')
            ->add('category')
            ->add('publishedAt');
    }
}
