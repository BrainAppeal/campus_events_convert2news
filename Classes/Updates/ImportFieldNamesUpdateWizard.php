<?php
namespace BrainAppeal\CampusEventsConvert2News\Updates;

use BrainAppeal\CampusEventsConvert2News\Service\UpdateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use BrainAppeal\CampusEventsConnector\Updates\ImportFieldNamesUpdateWizard as BaseImportFieldNamesUpdateWizard;

class ImportFieldNamesUpdateWizard extends BaseImportFieldNamesUpdateWizard
{
    /**
     * @return \BrainAppeal\CampusEventsConnector\Service\UpdateService
     */
    protected function getUpdateService()
    {
        if (null === $this->updateService) {
            $this->updateService = GeneralUtility::makeInstance(UpdateService::class);
        }
        return $this->updateService;
    }

}
