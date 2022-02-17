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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as FileReferenceModel;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

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
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param PersistenceManagerInterface $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

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
        // Disable PID restriction, because we want to load all events from ALL pages
        // and then move the events to the configured page id
        return $eventRepository->findAllByConvertConfiguration($configuration, false);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private function getObjectManager()
    {
        if (null === $this->objectManager) {
            $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        }

        return $this->objectManager;
    }

    /**
     * @param FileReferenceModel $fileReference
     * @return \GeorgRinger\News\Domain\Model\FileReference
     */
    private function getFalObject(FileReferenceModel $fileReference)
    {
        $objectManager = $this->getObjectManager();

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
     * Returns true, if the event can be converted to the target object model; Override this function in custom
     * converter to support skipping import of single events
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\Event $event
     * @return bool
     */
    protected function isConversionPossible($event)
    {
        return !empty($event->getUrl());
    }

    /**
     * @param \GeorgRinger\News\Domain\Model\News $object
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\Event $event
     * @param \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration $configuration
     * @api Use this method to individualize your object
     */
    protected function individualizeObjectByEvent(&$object, $event, $configuration)
    {
        $templateEngine = $this->getTemplateEngine();
        $object->setType($configuration->getTxnewsType());

        $eventName = (string)$event->getName();
        $object->setTitle($eventName);
        $bodytext = $templateEngine->getFromTemplate($configuration, 'Bodytext', ['event' => $event]);
        // Replace multiple consecutive whitespaces with a single whitespace
        $bodytext = preg_replace('/ {2,}/', ' ', $bodytext);
        $object->setBodytext($bodytext);
        $teaser = '';
        if (method_exists($event, 'getSubtitle')) {
            $teaser = $event->getSubtitle();
        }
        if (empty($teaser)) {
            $teaser = $event->getShortDescription();
        }
        $object->setTeaser($teaser);
        $object->setDatetime($event->getStartDate());
        $object->setArchive($event->getEndDate());

        $object->setExternalurl((string)$event->getUrl());
        // Special methods added by EXT:eventnews
        if (method_exists($object, 'setEventEnd')
            && ($eventEnd = $event->getEndDate()) instanceof \DateTime
            && $eventEnd->getTimestamp() <= 2147483647) {
            $object->setEventEnd($eventEnd);
        }
        if (method_exists($object, 'setImportSource')
            && method_exists($object, 'setImportId')
            && method_exists($object, 'getCeImportSource')) {
            $importSource = $object->getCeImportSource() ?? 'campus_events_connector';
            $object->setImportSource($importSource);
            $object->setImportId($event->getUid());
        }
        if (method_exists($object, 'setIsEvent')) {
            $object->setIsEvent(true);
        }

        if (method_exists($object, 'setPathSegment')) {
            $slug = $this->createSlugForName($eventName);
            if ($slug) {
                $object->setPathSegment($slug);
            }
        }

        if ((int) $configuration->getTxnewsType() === 2) {
            if (empty($event->getShortDescription())) {
                $object->setTeaser($this->html2text($event->getDescription()));
            }
        }

        $this->addNewsMedia($object, $event);
        $this->addNewsAttachments($object, $event);
    }


    /**
     * Add the event attachments to news attachments
     * @param \GeorgRinger\News\Domain\Model\News $object
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\Event $event
     */
    protected function addNewsAttachments($object, $event)
    {
        /** @var FileReferenceModel[] $mapImportFileReferences */
        $mapImportFileReferences = [];
        /** @var \GeorgRinger\News\Domain\Model\FileReference $attachment */
        foreach ($event->getAttachments() as $attachment) {
            /** @var FileReferenceModel $attachment */
            $origFileUid = $attachment->getOriginalResource()->getOriginalFile()->getUid();
            $mapImportFileReferences[$origFileUid] = $attachment;
        }
        // New event model
        if (method_exists($event, 'getEventAttachments')) {
            /** @var \BrainAppeal\CampusEventsConnector\Domain\Model\EventAttachment $eventAttachment */
            foreach ($event->getEventAttachments() as $eventAttachment) {
                if (($fileReference = $eventAttachment->getAttachmentFile()) instanceof FileReferenceModel) {
                    $originalFile = $fileReference->getOriginalResource()->getOriginalFile();
                    if (!$originalFile->isMissing() && $originalFile->getStorage()->hasFile($originalFile->getIdentifier())) {
                        $origFileUid = $originalFile->getUid();
                        $mapImportFileReferences[$origFileUid] = $fileReference;
                    }
                }
            }
        }
        $mapImportFileUidList = array_keys($mapImportFileReferences);
        $existingFileUids = $this->processExistingFileReferences($object->getFalRelatedFiles(), $mapImportFileUidList);
        foreach ($mapImportFileReferences as $origFileUid => $fileReference) {
            if (!in_array($origFileUid, $existingFileUids, false)) {
                $object->addFalRelatedFile($this->getFalObject($fileReference));
            }
        }
    }

    /**
     * Add the event images to news media
     * @param \GeorgRinger\News\Domain\Model\News $object
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\Event $event
     */
    protected function addNewsMedia($object, $event)
    {
        /** @var FileReferenceModel[] $mapImportFileReferences */
        $mapImportFileReferences = [];
        /** @var \GeorgRinger\News\Domain\Model\FileReference $image */
        foreach ($event->getImages() as $image) {
            /** @var FileReferenceModel $image */
            $origFileUid = $image->getOriginalResource()->getOriginalFile()->getUid();
            $mapImportFileReferences[$origFileUid] = $image;
        }
        // New event model
        if (method_exists($event, 'getEventImages')) {
            /** @var \BrainAppeal\CampusEventsConnector\Domain\Model\EventImage $eventImage */
            foreach ($event->getEventImages() as $eventImage) {
                if (($fileReference = $eventImage->getImageFile()) instanceof FileReferenceModel) {
                    $originalFile = $fileReference->getOriginalResource()->getOriginalFile();
                    if (!$originalFile->isMissing() && $originalFile->getStorage()->hasFile($originalFile->getIdentifier())) {
                        $origFileUid = $originalFile->getUid();
                        $mapImportFileReferences[$origFileUid] = $fileReference;
                    }
                }
            }
        }
        $mapImportFileUidList = array_keys($mapImportFileReferences);
        $existingFileUids = $this->processExistingFileReferences($object->getFalMedia(), $mapImportFileUidList);
        foreach ($mapImportFileReferences as $origFileUid => $fileReference) {
            if (!in_array($origFileUid, $existingFileUids, false)) {
                $object->addFalMedia($this->getFalObject($fileReference));
            }
        }
    }

    /**
     * Returns the list of file uid's that already are referenced by the current object
     * Additionally filters out duplicates (that were already stored before)
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|\GeorgRinger\News\Domain\Model\FileReference[] $fileReferences
     * @param array|int[] $mapImportFileUidList File UID list of import file references
     * @return array|int[] $existingFileUidList
     */
    private function processExistingFileReferences($fileReferences, array $mapImportFileUidList): array
    {
        $existingFileUidList = [];
        foreach ($fileReferences as $existingMedia) {
            $originalFile = $existingMedia->getOriginalResource()->getOriginalFile();
            $origFileUid = $originalFile->getUid();
            // Remove file reference if either the file is not referenced in the imported files or the file is a duplicate
            if (null !== $this->persistenceManager
                && ($originalFile->isMissing()
                    || !$originalFile->getStorage()->hasFile($originalFile->getIdentifier())
                    || !in_array($origFileUid, $mapImportFileUidList, true)
                    || in_array($origFileUid, $existingFileUidList, true))) {
                $fileReferences->detach($existingMedia);
                $this->persistenceManager->remove($existingMedia);
            } else {
                $existingFileUidList[] = $origFileUid;
            }
        }
        return $existingFileUidList;
    }

    /**
     * @param \BrainAppeal\CampusEventsConnector\Domain\Model\Event $event
     * @return array
     */
    protected function getAdditionDataHandlerValues($event)
    {
        $eventName = (string) $event->getName();
        $data = [
            'title' => $eventName,
            'teaser' => (string) $event->getShortDescription(),
            'externalurl' => (string) $event->getUrl(),
            'path_segment' => (string) $this->createSlugForName($eventName)
        ];
        return $data;
    }

    /**
     * Creates a slug for the given event name
     * @param string $eventName
     * @return string|null
     */
    private function createSlugForName($eventName)
    {
        $slug = null;
        if ($eventName) {
            if (class_exists(\TYPO3\CMS\Core\DataHandling\SlugHelper::class)) {
                $slugConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];
                /** @var \TYPO3\CMS\Core\DataHandling\SlugHelper $slugService */
                $slugService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $slugConfig);
                $slug = $slugService->sanitize($eventName);
            } elseif (class_exists(\GeorgRinger\News\Service\SlugService::class)) {
                /** @var \GeorgRinger\News\Service\SlugService $slugService */
                $slugService = GeneralUtility::makeInstance(\GeorgRinger\News\Service\SlugService::class);
                $slug = $slugService->generateSlug($eventName);
            }
        }
        return $slug;
    }
}
