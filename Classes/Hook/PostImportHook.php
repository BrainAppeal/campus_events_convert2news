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


class PostImportHook
{

    /**
     * @return \BrainAppeal\CampusEventsConnector\Converter\EventConverterInterface
     */
    private function getConverter()
    {
        /** @var \BrainAppeal\CampusEventsConnector\Converter\EventConverterInterface $converter */
        $converter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\BrainAppeal\CampusEventsConvert2News\Converter\Event2NewsConverter::class);

        return $converter;
    }

    /**
     * @param int $pid
     * @return \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration[]
     */
    private function findConfigurationsByPid($pid)
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \BrainAppeal\CampusEventsConvert2News\Domain\Repository\Convert2NewsConfigurationRepository $configurationRepository */
        $configurationRepository = $objectManager->get(\BrainAppeal\CampusEventsConvert2News\Domain\Repository\Convert2NewsConfigurationRepository::class);

        return $configurationRepository->findActiveByPid($pid);
    }

    /**
     * @param int $pid
     * @return bool
     */
    public function postImport($pid) {
        $configurations = $this->findConfigurationsByPid($pid);

        if (count($configurations)) {
            $converter = $this->getConverter();

            foreach ($configurations as $configuration) {
                $converter->run($configuration);
            }
        }

        return true;
    }
}
