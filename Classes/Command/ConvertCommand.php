<?php

declare(strict_types=1);

namespace BrainAppeal\CampusEventsConvert2News\Command;

use BrainAppeal\CampusEventsConnector\Importer\PostImportHookInterface;
use BrainAppeal\CampusEventsConvert2News\Hook\PostImportHook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
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
        Bootstrap::initializeBackendAuthentication();
        $targetPid = (int)$input->getArgument('pid');
        $this->initializeExtbaseEnvironment($targetPid);
        $converter = GeneralUtility::makeInstance(PostImportHook::class);
        $converter->postImport($targetPid);

        return Command::SUCCESS;
    }

    /**
     * Since TYPO3 13.4 we need the request object to initialize the configuration manager for Extbase
     * @param int $targetPid
     * @return void
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
