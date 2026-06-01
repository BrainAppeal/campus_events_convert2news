<?php

declare(strict_types=1);

namespace BrainAppeal\CampusEventsConvert2News\Command;

use BrainAppeal\CampusEventsConvert2News\Converter\Event2NewsConverter;
use BrainAppeal\CampusEventsConvert2News\Domain\Repository\Convert2NewsConfigurationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertCommand extends Command
{
    public function __construct(
        private readonly Event2NewsConverter $converter,
        private readonly Convert2NewsConfigurationRepository $configurationRepository
    ) {
        parent::__construct();
    }
    /**
     * Configure the command by defining the name, options, and arguments
     */
    protected function configure(): void
    {
        $this->addArgument(
            'pid',
            InputArgument::REQUIRED,
            'The target page id for the imported events'
        );
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
        Bootstrap::initializeBackendAuthentication();
        $targetPid = (int)$input->getArgument('pid');
        $this->initializeExtbaseEnvironment($targetPid);
        foreach ($this->configurationRepository->findActiveByPid($targetPid) as $config) {
            $this->converter->run($config);
        }
        return Command::SUCCESS;
    }

    /**
     * Since TYPO3 13.4 we need the request object to initialize the configuration manager for Extbase
     * @param int $targetPid
     */
    protected function initializeExtbaseEnvironment(int $targetPid): void
    {
        // TYPO3 >= 13: Initialize TYPO3_REQUEST, so Extbase can be used
        // @see \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::getConfiguration
        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            $request = (new ServerRequest())->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
            $pageRecord = BackendUtility::getRecord('pages', $targetPid);
            if ($pageRecord) {
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                try {
                    $site = $siteFinder->getSiteByPageId($targetPid);
                    $request = $request->withAttribute('site', $site);
                } catch (SiteNotFoundException $e) {
                    unset($e);
                }
                $request = $request->withQueryParams(['id' => $targetPid]);
            }
            $GLOBALS['TYPO3_REQUEST'] = $request;
        }
    }
}
