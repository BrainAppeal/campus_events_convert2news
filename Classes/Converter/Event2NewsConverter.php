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

namespace BrainAppeal\CampusEventsConvert2News\Converter;

class Event2NewsConverter extends \BrainAppeal\CampusEventsConnector\Converter\AbstractEventToObjectConverter
{
    /**
     * @var TemplateEngine
     */
    private $templateEngine;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

    /**
     * @return TemplateEngine
     */
    private function getTemplateEngine()
    {
        if (null === $this->templateEngine) {
            $this->templateEngine = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TemplateEngine::class);
        }

        return $this->templateEngine;
    }

    /**
     * @return string
     */
    protected function getObjectRepositoryClass()
    {
       return \BrainAppeal\CampusEventsConvert2News\Domain\Repository\NewsRepository::class;
    }

    /**
     * @param \BrainAppeal\CampusEventsConnector\Domain\Repository\EventRepository $eventRepository
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\ConvertConfiguration $configuration
     * @return \BrainAppeal\CampusEventsConnector\Domain\Model\Event[]
     */
    protected function getMatchingEventsByConfiguration($eventRepository, $configuration)
    {
        return $eventRepository->findAllByPid($configuration->getPid());
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private function getCachedObjectManager()
    {
        if (null === $this->objectManager) {
            $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }

        return $this->objectManager;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference
     * @return \GeorgRinger\News\Domain\Model\FileReference
     */
    private function getFalObject(\TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference)
    {
        $objectManager = $this->getCachedObjectManager();

        /** @var \GeorgRinger\News\Domain\Model\FileReference $media */
        $media = $objectManager->get(\GeorgRinger\News\Domain\Model\FileReference::class);
        $media->setFileUid($fileReference->getOriginalResource()->getOriginalFile()->getUid());

        return $media;
    }

    /**
     * @param string $html
     * @return string
     */
    private function html2text($html)
    {
        return html_entity_decode(strip_tags($html));
    }

    /**
     * @param \BrainAppeal\CampusEventsConvert2News\Domain\Model\News $object
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\Event $event
     * @param \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration $configuration
     * @api Use this method to individualize your object
     */
    protected function individualizeObjectByEvent(&$object, $event, $configuration)
    {
        $templateEngine = $this->getTemplateEngine();

        $object->setType($configuration->getTxnewsType());

        $object->setTitle($event->getName());
        $object->setBodytext($templateEngine->getFromTemplate($configuration, 'Bodytext', ['event' => $event]));
        $object->setTeaser($event->getShortDescription());
        $object->setDatetime($event->getStartDate());
        $object->setArchive($event->getEndDate());
        $object->setExternalurl($event->getUrl());

        if ($configuration->getTxnewsType() == 2) {
            if (empty($event->getShortDescription())) {
                $object->setTeaser($this->html2text($event->getDescription()));
            }
        }

        /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $image */
        foreach ($event->getImages() as $image) {
            $object->addFalMedia($this->getFalObject($image));
        }

        /** @var \GeorgRinger\News\Domain\Model\FileReference $attachment */
        foreach ($event->getAttachments() as $attachment) {
            $object->addFalRelatedFile($this->getFalObject($attachment));
        }
    }
}
