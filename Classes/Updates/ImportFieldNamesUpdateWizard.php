<?php

namespace BrainAppeal\CampusEventsConvert2News\Updates;

use BrainAppeal\CampusEventsConnector\Service\UpdateService as BaseUpdateService;
use BrainAppeal\CampusEventsConnector\Updates\ImportFieldNamesUpdateWizard as BaseImportFieldNamesUpdateWizard;
use BrainAppeal\CampusEventsConvert2News\Service\UpdateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportFieldNamesUpdateWizard extends BaseImportFieldNamesUpdateWizard
{
    /**
     * @return BaseUpdateService
     */
    protected function getUpdateService(): BaseUpdateService
    {
        if ($this->updateService === null) {
            $this->updateService = GeneralUtility::makeInstance(UpdateService::class);
        }
        return $this->updateService;
    }

}
