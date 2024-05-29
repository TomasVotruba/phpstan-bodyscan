<?php

declare (strict_types=1);
namespace TomasVotruba\PHPStanBodyscan\Command;

use PHPStanBodyscan202405\Nette\Utils\Strings;
use PHPStanBodyscan202405\Symfony\Component\Console\Command\Command;
use PHPStanBodyscan202405\Symfony\Component\Console\Input\InputInterface;
use PHPStanBodyscan202405\Symfony\Component\Console\Input\InputOption;
use PHPStanBodyscan202405\Symfony\Component\Console\Output\OutputInterface;
use PHPStanBodyscan202405\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\PHPStanBodyscan\DependencyInstaller;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter;
use TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter;
use TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory;
use TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory;
use TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverage;
use TomasVotruba\PHPStanBodyscan\ValueObject\TypeCoverageResult;
final class TypeCoverageCommand extends Command
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\Process\AnalyseProcessFactory
     */
    private $analyseProcessFactory;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\PHPStanConfigFactory
     */
    private $phpStanConfigFactory;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\OutputFormatter\JsonOutputFormatter
     */
    private $jsonOutputFormatter;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\OutputFormatter\TableOutputFormatter
     */
    private $tableOutputFormatter;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\DependencyInstaller
     */
    private $dependencyInstaller;
    /**
     * @readonly
     * @var \TomasVotruba\PHPStanBodyscan\Process\PHPStanResultResolver
     */
    private $phpStanResultResolver;
    /**
     * @var string
     * @see https://regex101.com/r/t64u6i/1
     */
    private const TYPE_COVERAGE_MESSAGE_REGEX = '#(?<category>.*?) coverage is (?<relative>.*?) % out of (?<total_count>\\d+) possible#';
    public function __construct(SymfonyStyle $symfonyStyle, AnalyseProcessFactory $analyseProcessFactory, PHPStanConfigFactory $phpStanConfigFactory, JsonOutputFormatter $jsonOutputFormatter, TableOutputFormatter $tableOutputFormatter, DependencyInstaller $dependencyInstaller, PHPStanResultResolver $phpStanResultResolver)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->analyseProcessFactory = $analyseProcessFactory;
        $this->phpStanConfigFactory = $phpStanConfigFactory;
        $this->jsonOutputFormatter = $jsonOutputFormatter;
        $this->tableOutputFormatter = $tableOutputFormatter;
        $this->dependencyInstaller = $dependencyInstaller;
        $this->phpStanResultResolver = $phpStanResultResolver;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('type-coverage');
        $this->setDescription('Check classes that are not used in any config and in the code');
        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'Path to project .env file');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Show result in JSON');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        /** @var string $projectDirectory */
        $projectDirectory = \getcwd();
        $isJson = (bool) $input->getOption('json');
        // silence output till the end to avoid invalid json format
        if ($isJson) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        $typeCoverageResult = $this->measureTypeCoverage($projectDirectory);
        // 3. tidy up temporary config
        \unlink($projectDirectory . '/phpstan-bodyscan.neon');
        if ($isJson) {
            $this->jsonOutputFormatter->outputTypeCoverageResult($typeCoverageResult);
        } else {
            $this->tableOutputFormatter->outputTypeCoverageResult($typeCoverageResult);
        }
        return self::SUCCESS;
    }
    private function measureTypeCoverage(string $projectDirectory) : TypeCoverageResult
    {
        $this->dependencyInstaller->ensureTypeCoverageIsInstalled($projectDirectory);
        // @todo the extension config should be loaded with phpstan extension installed,
        // @todo resolve manually here if missing
        $phpstanConfiguration = $this->phpStanConfigFactory->create($projectDirectory, ['parameters' => ['type_coverage' => ['measure' => \true], 'customRulesetUsed' => \true]]);
        \file_put_contents($projectDirectory . '/phpstan-bodyscan.neon', $phpstanConfiguration);
        $process = $this->analyseProcessFactory->createTypeCoverageProcess($projectDirectory);
        $process->run();
        $result = $this->phpStanResultResolver->resolve($process);
        $typeCoverageResults = [];
        foreach ($result['files'] as $file => $fileErrors) {
            // we look only for global data
            if ($file !== 'N/A') {
                continue;
            }
            foreach ($fileErrors['messages'] as $errorMessage) {
                $match = Strings::match($errorMessage['message'], self::TYPE_COVERAGE_MESSAGE_REGEX);
                if ($match === null) {
                    continue;
                }
                $typeCoverageResults[] = new TypeCoverage(\lcfirst((string) $match['category']), (float) $match['relative'], (int) $match['total_count']);
            }
        }
        return new TypeCoverageResult($typeCoverageResults);
    }
}
