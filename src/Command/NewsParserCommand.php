<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\ParserFactory;
use App\Parser\BaseNewsParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:parse-news')]
class NewsParserCommand extends Command
{
    public const RIA = 'ria';
    public const RBC = 'rbc';
    public const LENTA = 'lenta';
    public const ADVANCED = 'advanced';
    public const ALLOWED_PARSERS = [
        self::RBC,
        self::LENTA,
        self::RIA,
    ];
    private ParserFactory $parserFactory;
    
    
    public function __construct(ParserFactory $parserFactory)
    {
        parent::__construct();
        $this->parserFactory = $parserFactory;
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Parses news from configured sources.')
            ->setHelp('This command allows you to parse news from sources in the database.')
            ->addOption(
                'source',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                sprintf(
                    'Specify the sources to parse by name, allowed parsers:%s, advanced parser',
                    implode(',', self::ALLOWED_PARSERS )
                ),
                []
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Limit to parse',
                []
            )
            ->addOption(
                'sourceName',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the Source name for advanced parser'
            )
            ->addOption(
                'rssUrl',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the RSS feed URL for custom parsers'
            )
            ->addOption(
                'titleTag',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the title tag name for RSS items'
            )
            ->addOption(
                'categoryTag',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the category tag name for RSS items'
            )
            ->addOption(
                'dateTag',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the publication date tag name for RSS items'
            )
            ->addOption(
                'descriptionTag',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the description tag name for RSS items'
            )
            ->addOption(
                'linkTag',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the link tag name for RSS items'
            )
            ->addOption(
                'descriptionPattern',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify the description pattern'
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $sources = $input->getOption('source') ?? self::ALLOWED_PARSERS;
        
        $limit = $input->getOption('limit');
        
        if (empty($sources)) {
            $io->warning('No sources found to parse.');
            
            return Command::FAILURE;
        }
        
        $io->title('Parsing news from sources');
        
        foreach ($sources as $source) {
            $io->section('Parsing source: ' . $source);
            
            $parser = $this->parserFactory->createParser($source);
            
            if ($parser === null) {
                $io->warning('No parser found for source: ' . $source);
                continue;
            }
            
            try {
                if ($source === self::ADVANCED) {
                    $this->setParserSettingsFromOptions($input, $parser);
                }
                
                if ($limit !== null) {
                    $parser->setMaxItems((int)$limit);
                }
                
                $parsedNews = $parser->parse();
                
                $io->success('Successfully parsed news for source: ' . $source);
            } catch (\Exception $e) {
                $io->error('Error parsing news for source: ' . $source . ' - ' . $e->getMessage());
            }
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * @param InputInterface $input
     * @param BaseNewsParser $parser
     *
     * @return void
     * @throws \Exception
     */
    protected function setParserSettingsFromOptions(InputInterface $input, BaseNewsParser $parser): void
    {
        $sourceName = $input->getOption('sourceName') ?? null;
        $rssUrl = $input->getOption('rssUrl') ?? null;
        $titleTag = $input->getOption('titleTag') ?? null;
        $categoryTag = $input->getOption('categoryTag') ?? null;
        $dateTag = $input->getOption('dateTag') ?? null;
        $descriptionTag = $input->getOption('descriptionTag') ?? null;
        $linkTag = $input->getOption('linkTag') ?? null;
        $descriptionPattern = $input->getOption('descriptionTag') ?? null;
        
        if ($rssUrl === null || $sourceName === null) {
            throw new \Exception('Parser must contain rssUrl and source name');
        }
        
        $parser->setRssUrl((string)$rssUrl);
        $parser->setSourceName((string)$sourceName);
        if ($titleTag !== null) {
            $parser->setTitleTag((string)$titleTag);
        }
        if ($categoryTag !== null) {
            $parser->setCategoryTag((string)$categoryTag);
        }
        if ($dateTag !== null) {
            $parser->setDateTag((string)$dateTag);
        }
        if ($descriptionTag !== null) {
            $parser->setDescriptionTag((string)$descriptionTag);
        }
        if ($linkTag !== null) {
            $parser->setLinkTag((string)$linkTag);
        }
        if ($descriptionPattern !== null) {
            $parser->getContentFetcher()->setDescriptionPattern((string)$descriptionPattern);
        }
    }
}
