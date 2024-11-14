<?php

declare(strict_types=1);

namespace App\Factory;

use App\Command\NewsParserCommand;
use App\Parser\AdvancedParser;
use App\Parser\BaseNewsParser;
use App\Parser\LentaRuParser;
use App\Parser\RbcParser;
use App\Parser\RiaNewsParser;

/*
 * Фабрика для определения нужного парсера на основании опции
 */
class ParserFactory
{
    private RbcParser $rbcParser;
    private LentaRuParser $lentaParser;
    private RiaNewsParser $riaParser;
    private AdvancedParser $advancedParser;
    
    public function __construct(
        RbcParser $rbcParser,
        LentaRuParser $lentaParser,
        RiaNewsParser $riaParser,
        AdvancedParser $advancedParser
    )
    {
        $this->rbcParser = $rbcParser;
        $this->lentaParser = $lentaParser;
        $this->riaParser = $riaParser;
        $this->advancedParser = $advancedParser;
    }
    
    /**
     * @param string $source
     *
     * @return BaseNewsParser|null
     */
    public function createParser(string $source): ?BaseNewsParser
    {
        return match ($source) {
            NewsParserCommand::RBC => $this->rbcParser,
            NewsParserCommand::LENTA => $this->lentaParser,
            NewsParserCommand::RIA => $this->riaParser,
            NewsParserCommand::ADVANCED => $this->advancedParser,
            default => null
        };
    }
}
