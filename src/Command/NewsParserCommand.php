<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\News;
use App\Entity\Source;
use App\Parser\LentaRuParser;
use App\Parser\OtherParser;
use App\Parser\RbcParser;
use App\Parser\RiaNewsParser;
use App\Service\NewsService;
use App\Parser\AbstractNewsParser;
use App\Storage\NewsStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class NewsParserCommand extends Command
{
    protected static $defaultName = 'app:parse-news';
    private EntityManagerInterface $entityManager;
    private NewsStorage $newsStorage;
    
    public function __construct(EntityManagerInterface $entityManager, NewsStorage $newsStorage)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->newsStorage = $newsStorage;
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Parses news from configured sources.')
            ->setHelp('This command allows you to parse news from sources specified in the database.')
            ->addOption(
                'source',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Specify the sources to parse (by name or ID)',
                []
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $sources = $this->getSourcesFromOption($input);
        
        if (empty($sources)) {
            $io->warning('No sources found to parse.');
            return Command::FAILURE;
        }
        
        $io->title('Parsing news from sources');
        
        foreach ($sources as $source) {
            $io->section('Parsing source: ' . $source->getName());
            
            // Получаем соответствующий парсер для источника
            $parser = $this->getParserForSource($source);
            
            if (!$parser) {
                $io->warning('No parser found for source: ' . $source->getName());
                continue;
            }
            
            // Получаем RSS ленту
            try {
                $parsedNews = $parser->parse();
                
                $io->success('Successfully parsed news for source: ' . $source->getName());
            } catch (\Exception $e) {
                $io->error('Error parsing news for source: ' . $source->getName() . ' - ' . $e->getMessage());
            }
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Получаем источники для парсинга на основе опции.
     *
     * @param InputInterface $input
     *
     * @return Source[]
     */
    private function getSourcesFromOption(InputInterface $input): array
    {
        $sourceNames = $input->getOption('source');
        
        if (empty($sourceNames)) {
            // Если опция не указана, парсим все источники
            return $this->entityManager->getRepository(Source::class)->findAll();
        }
        
        // Если указаны имена источников, находим их по этим именам
        return $this->entityManager->getRepository(Source::class)->findBy(['name' => $sourceNames]);
    }
    
    /**
     * Получаем парсер для источника.
     *
     * @param Source $source
     *
     * @return AbstractNewsParser|null
     */
    private function getParserForSource(Source $source): ?AbstractNewsParser
    {
        return match ($source->getName()) {
            'rbc' => new RbcParser(),
            'lenta' => new LentaRuParser(),
            'ria' => new RiaNewsParser(),
            default => new OtherParser($source->getName(), $source->getRssUrl()),
        };
    }
}
