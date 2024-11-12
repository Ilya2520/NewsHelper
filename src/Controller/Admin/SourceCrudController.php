<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Source;
use App\Repository\NewsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class SourceCrudController extends AbstractCrudController
{
    private NewsRepository $newsRepository;
    
    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }
    
    public static function getEntityFqcn(): string
    {
        return Source::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Источники')
            ->setEntityLabelInSingular('Источник');
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Название'),
            IntegerField::new('newsCount', 'Количество новостей')
                ->setFormTypeOption('mapped', false)
                ->setVirtual(true)
                ->formatValue(function ($value, $entity) {
                    return $this->newsRepository->countNewsBySource($entity);
                }),
        ];
    }
}
