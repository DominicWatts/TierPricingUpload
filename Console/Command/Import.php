<?php


namespace Xigen\TierPricingUpload\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import console
 */
class Import extends Command
{
    const IMPORT_ARGUMENT = 'import';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Xigen\TierPricingUpload\Helper\Import
     */
    private $importHelper;

    /**
     * @var \Xigen\TierPricingUpload\Helper\Import
     */
    private $csvImportHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Xigen\TierPricingUpload\Helper\Import $importHelper,
        \Xigen\CsvUpload\Helper\Import $csvImportHelper
    ) {
        $this->logger = $logger;
        $this->state = $state;
        $this->dateTime = $dateTime;
        $this->importHelper = $importHelper;
        $this->csvImportHelper = $csvImportHelper;
        parent::__construct();
    }

    /**
    * {@inheritdoc}
    */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $import = $input->getArgument(self::IMPORT_ARGUMENT) ?: false;
        $importData = [];
        if ($import) {
            $this->output->writeln((string)__('[%1] Start', $this->dateTime->gmtDate()));
                        
            $imports = $this->csvImportHelper->getImports();
            $progress = new ProgressBar($this->output, count($imports));
            $progress->start();
            foreach ($imports as $import) {
                $priceEntry = $this->csvImportHelper->parseImport($import);
  
                if (!isset($priceEntry['sku'])) {
                    throw new LocalizedException(__('Problem with data'));
                }

                $product = $this->importHelper->get($priceEntry['sku']);
                if (!$product) {
                    $this->output->writeln((string)__('[%1] Sku not found : %2', $this->dateTime->gmtDate(), $priceEntry['sku']));
                    continue;
                }

                $importData[] = $priceEntry;
                $progress->advance();
            }

            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->tier = $this->_objectManager->create(\Xigen\TierPricingUpload\Model\Import\AdvancedPricing::class);
            $this->tier->xigenSaveAndReplaceAdvancedPrices($importData);

            $progress->advance();
            $progress->finish();
            
            $this->output->writeln('');
            $this->output->writeln((string)__('[%1] Finish', $this->dateTime->gmtDate()));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("xigen:tierpricing:import");
        $this->setDescription("Process to import tier pricing");
        $this->setDefinition([
            new InputArgument(self::IMPORT_ARGUMENT, InputArgument::REQUIRED, 'Import'),
        ]);
        parent::configure();
    }
}
