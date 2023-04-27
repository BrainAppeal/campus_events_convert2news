<?php
/**
 * campus_events_convert2news comes with ABSOLUTELY NO WARRANTY
 * See the GNU GeneralPublic License for more details.
 * https://www.gnu.org/licenses/gpl-2.0
 *
 * Copyright (C) 2019 Brain Appeal GmbH
 *
 * @copyright 2019 Brain Appeal GmbH (www.brain-appeal.com)
 * @license   GPL-2 (www.gnu.org/licenses/gpl-2.0)
 * @link      https://www.campus-events.com/
 */

defined('TYPO3') or die();


call_user_func(
    static function ($extKey) {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tx_campuseventsconnector']['postImport']['tx_campuseventsconvert2news'] = \BrainAppeal\CampusEventsConvert2News\Hook\PostImportHook::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['importFieldNamesUpdateWizard'] = \BrainAppeal\CampusEventsConvert2News\Updates\ImportFieldNamesUpdateWizard::class;

        // TODO: any updates needed here?
        $versionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        if ($versionInformation->getMajorVersion() < 12) {
            /** @var \TYPO3\CMS\Extbase\Object\Container\Container $objectRegistry */
            $objectRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
            $objectRegistry->registerImplementation(\BrainAppeal\CampusEventsConnector\Domain\Model\ConvertConfiguration::class, \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration::class);
        }
        // typo3-dev/fb05/typo3conf/ext/news/Documentation/DeveloperManual/ExtendNews/ProxyClassGenerator/Index.rst
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = $extKey;
    },
    'campus_events_convert2news'
);
