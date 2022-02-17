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

namespace BrainAppeal\CampusEventsConvert2News\Hook;



abstract class AbstractPostImportHook
{

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private function getObjectManager()
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        return $objectManager;
    }

    /**
     * @return \BrainAppeal\CampusEventsConnector\Converter\EventConverterInterface
     */
    private function getConverter()
    {
        /** @var \BrainAppeal\CampusEventsConnector\Converter\EventConverterInterface $converter */
        $converter = $this->getObjectManager()->get(\BrainAppeal\CampusEventsConvert2News\Converter\Event2NewsConverter::class);

        return $converter;
    }

    /**
     * @param int $pid
     * @return \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration[]
     */
    private function findConfigurationsByPid($pid)
    {
        /** @var \BrainAppeal\CampusEventsConvert2News\Domain\Repository\Convert2NewsConfigurationRepository $configurationRepository */
        $configurationRepository = $this->getObjectManager()->get(\BrainAppeal\CampusEventsConvert2News\Domain\Repository\Convert2NewsConfigurationRepository::class);

        return $configurationRepository->findActiveByPid($pid);
    }

    /**
     * @param int $pid The page id where the events are stored
     * @return bool
     */
    public function postImport($pid)
    {
        $configurations = $this->findConfigurationsByPid($pid);
        if (count($configurations)) {
            $converter = $this->getConverter();
            /** @var \BrainAppeal\CampusEventsConvert2News\Converter\Event2NewsConverter $converter */
            foreach ($configurations as $configuration) {
                $converter->run($configuration);
            }
        }

        return true;
    }
}
