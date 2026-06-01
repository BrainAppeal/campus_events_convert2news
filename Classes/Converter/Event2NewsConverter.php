<?php

declare(strict_types=1);

/**
 * campus_events_convert2news comes with ABSOLUTELY NO WARRANTY
 * See the GNU GeneralPublic License for more details.
 * https://www.gnu.org/licenses/gpl-2.0
 *
 * Copyright (C) 2026 Brain Appeal GmbH
 *
 * @copyright 2019 Brain Appeal GmbH (www.brain-appeal.com)
 * @license   GPL-2 (www.gnu.org/licenses/gpl-2.0)
 * @link      https://www.campus-events.com/
 */

namespace BrainAppeal\CampusEventsConvert2News\Converter;

use BrainAppeal\CampusEventsConnector\Converter\AbstractEventToObjectConverter;
use BrainAppeal\CampusEventsConnector\Domain\Model\ConvertConfiguration;
use BrainAppeal\CampusEventsConnector\Domain\Model\Event;
use BrainAppeal\CampusEventsConnector\Domain\Model\EventAttachment;
use BrainAppeal\CampusEventsConnector\Domain\Model\EventImage;
use BrainAppeal\CampusEventsConnector\Domain\Model\ImportedModelInterface;
use BrainAppeal\CampusEventsConnector\Domain\Repository\EventRepository;
use BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration;
use BrainAppeal\CampusEventsConvert2News\Domain\Repository\NewsRepository;
use Doctrine\DBAL\Exception;
use GeorgRinger\News\Domain\Model\FileReference as NewsFileReferenceAlias;
use GeorgRinger\News\Domain\Model\News;
use GeorgRinger\News\Service\SlugService;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as FileReferenceModel;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class Event2NewsConverter extends AbstractEventToObjectConverter
{
    private const NEWS_TYPE_EXTERNAL = 2;

    /**
     * @var LanguageService|bool
     */
    protected $resetGlobalsLang = false;

    public function __construct(
        DataMapper $dataMapper,
        EventRepository $eventRepository,
        protected PersistenceManagerInterface $persistenceManager,
        NewsRepository $objectRepository,
        private readonly TemplateEngine $templateEngine,
        private readonly SiteMatcher $siteMatcher,
        private readonly LanguageServiceFactory $languageServiceFactory,
        private readonly ConnectionPool $connectionPool
    ) {
        parent::__construct($dataMapper, $eventRepository);
        $this->objectRepository = $objectRepository;
    }

    protected function getTargetTable(): string
    {
        return 'tx_news_domain_model_news';
    }

    /**
     * @param EventRepository $eventRepository
     * @param ConvertConfiguration $configuration
     * @return Event[]|QueryResultInterface<int, Event>
     */
    protected function getMatchingEventsByConfiguration(EventRepository $eventRepository, ConvertConfiguration $configuration): array|QueryResultInterface
    {
        // Disable PID restriction because we want to load all events from ALL pages
        // and then move the events to the configured page id
        return $eventRepository->findAllByConvertConfiguration($configuration, false);
    }

    protected function setLanguageBasedOnConfiguration(ConvertConfiguration $configuration): void
    {
        // Use labels for default language of current site; needed for news bodytext labels
        if (0 < $targetPid = (int)$configuration->getPid()) {
            $siteMatcher = $this->siteMatcher;
            if (!isset($GLOBALS['BE_USER'])) {
                Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
                Bootstrap::initializeBackendAuthentication();
            }
            try {
                $site = $siteMatcher->matchByPageId($targetPid);
                if (!($site instanceof NullSite)) {

                    /** @var LanguageServiceFactory $languageServiceFactory */
                    $languageServiceFactory = $this->languageServiceFactory;
                    if ($configuration instanceof Convert2NewsConfiguration) {
                        $languageUid = $configuration->getSysLanguageUid();
                        try {
                            $siteLanguage = $site->getLanguageById($languageUid);
                        } catch (\InvalidArgumentException $e) {
                            unset($e);
                            $siteLanguage = null;
                        }
                        if ($siteLanguage === null) {
                            $siteLanguage = $site->getDefaultLanguage();
                        }
                    } else {
                        $siteLanguage = $site->getDefaultLanguage();
                    }
                    $GLOBALS['BE_USER']->user['lang'] = $siteLanguage->getTypo3Language();
                    $this->resetGlobalsLang = true;
                    if (isset($GLOBALS['LANG'])) {
                        $this->resetGlobalsLang = $GLOBALS['LANG'];
                    }
                    $GLOBALS['LANG'] = $languageServiceFactory->createFromSiteLanguage($siteLanguage);
                }
            } catch (SiteNotFoundException $e) {
                unset($e);
            }
        }
    }

    /**
     * @param ConvertConfiguration $configuration
     */
    public function run($configuration): void
    {
        $this->setLanguageBasedOnConfiguration($configuration);
        parent::run($configuration);
        /** @var ConnectionPool $connectionPool */
        $connectionPool = $this->connectionPool;
        $connection = $connectionPool->getConnectionForTable('tx_news_domain_model_news');
        try {
            $connection->executeStatement('UPDATE tx_news_domain_model_news n, tx_news_domain_model_news o
    SET o.path_segment = CONCAT(o.path_segment, \'-\', o.uid)
    WHERE n.deleted = 0 AND n.path_segment = o.path_segment AND n.uid > o.uid');
        } catch (Exception $e) {
            unset($e);
        }
        // Fix custom language service initialized
        if ($this->resetGlobalsLang === true) {
            unset($GLOBALS['LANG']);
        } elseif ($this->resetGlobalsLang instanceof LanguageService) {
            $GLOBALS['LANG'] = $this->resetGlobalsLang;
        }
    }

    /**
     * @param FileReferenceModel $fileReference
     * @return ?NewsFileReferenceAlias
     */
    private function getFalObject(FileReferenceModel $fileReference): ?NewsFileReferenceAlias
    {
        if (($originalFileUid = $this->getUidOfValidOriginalFile($fileReference)) !== 0) {
            /** @var NewsFileReferenceAlias $media */
            $media = GeneralUtility::makeInstance(NewsFileReferenceAlias::class);
            $media->setFileUid($originalFileUid);
            return $media;
        }
        return null;

    }

    /**
     * Returns the uid of the original file for the given file reference if the file exists
     * @param FileReferenceModel $fileReference
     * @return int
     * @throws ResourceDoesNotExistException
     */
    protected function getUidOfValidOriginalFile(FileReferenceModel $fileReference): int
    {
        $originalFile = $fileReference->getOriginalResource()->getOriginalFile();
        if (!$originalFile->isMissing()
            && $originalFile->getStorage()->hasFile($originalFile->getIdentifier())) {
            return $originalFile->getUid();
        }
        throw new ResourceDoesNotExistException(
            'No file found for given UID: "' . $originalFile->getUid() . '"',
            1718106519
        );
    }

    /**
     * @param string $html
     * @return string
     */
    private function html2text(string $html): string
    {
        return html_entity_decode(strip_tags($html));
    }

    /**
     * Returns true, if the event can be converted to the target object model; Override this function in custom
     * converter to support skipping import of single events
     * @param Event $event
     * @return bool
     */
    protected function isConversionPossible(Event $event): bool
    {
        return !empty($event->getUrl());
    }

    /**
     * @param ImportedModelInterface $object
     * @param Event $event
     * @param ConvertConfiguration $configuration
     * @api Use this method to individualize your object
     */
    protected function individualizeObjectByEvent(ImportedModelInterface $object, Event $event, ConvertConfiguration $configuration): void
    {
        if ($object instanceof News && $configuration instanceof Convert2NewsConfiguration) {
            $this->individualizeNewsObjectByEvent($object, $event, $configuration);
        }
    }

    /**
     * Customizes a News object based on the details of a given Event and configuration settings.
     *
     * @param News $object The News object to be individualized.
     * @param Event $event The Event object containing data to populate the News object.
     * @param Convert2NewsConfiguration $configuration Configuration providing mapping details for the conversion.
     * @return void
     */
    protected function individualizeNewsObjectByEvent(News $object, Event $event, Convert2NewsConfiguration $configuration): void
    {
        $object->setType((string)$configuration->getTxnewsType());

        $eventName = (string)$event->getName();
        $object->setTitle($eventName);
        $bodytext = $this->templateEngine->getFromTemplate($configuration, 'Bodytext', ['event' => $event]);
        // Replace multiple consecutive whitespaces with a single whitespace
        $bodytext = preg_replace('/ {2,}/', ' ', (string)$bodytext);
        $object->setBodytext($bodytext);
        $teaser = trim((string)$event->getSubtitle());
        if ($teaser === '') {
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
        if (method_exists($object, 'getCeImportSource')) {
            $importSource = $object->getCeImportSource() ?? 'campus_events_connector';
            $object->setImportSource($importSource);
            $object->setImportId((string)$event->getUid());
        }
        if (method_exists($object, 'setIsEvent')) {
            $object->setIsEvent(true);
        }

        $slug = $this->createSlugForName($eventName);
        if ($slug) {
            $object->setPathSegment($slug);
        }

        if (($configuration->getTxnewsType() === self::NEWS_TYPE_EXTERNAL) && empty($event->getShortDescription())) {
            $object->setTeaser($this->html2text($event->getDescription()));
        }

        $this->addNewsMedia($object, $event);
        $this->addNewsAttachments($object, $event);
    }

    /**
     * Add the event attachments to news attachments
     * @param News $object
     * @param Event $event
     * @retur void
     */
    protected function addNewsAttachments($object, $event): void
    {
        /** @var FileReferenceModel[] $mapImportFileReferences */
        $mapImportFileReferences = [];
        /** @var EventAttachment $eventAttachment */
        foreach ($event->getEventAttachments() as $eventAttachment) {
            try {
                if ((($fileReference = $eventAttachment->getAttachmentFile()) instanceof FileReferenceModel)
                    && $originalFileUid = $this->getUidOfValidOriginalFile($fileReference)) {
                    $mapImportFileReferences[$originalFileUid] = $fileReference;
                }
            } catch (ResourceDoesNotExistException $e) {
                unset($e);
            }
        }
        $mapImportFileUidList = array_keys($mapImportFileReferences);
        $existingFileUids = $this->processExistingFileReferences($object->getFalRelatedFiles(), $mapImportFileUidList);
        foreach ($mapImportFileReferences as $origFileUid => $fileReference) {
            if (!in_array($origFileUid, $existingFileUids, false)
                && ($falReferenceModel = $this->getFalObject($fileReference)) instanceof NewsFileReferenceAlias) {
                $object->addFalRelatedFile($falReferenceModel);
            }
        }
    }

    /**
     * Add the event images to news media
     * @param News $object
     * @param Event $event
     */
    protected function addNewsMedia($object, $event): void
    {
        /** @var FileReferenceModel[] $mapImportFileReferences */
        $mapImportFileReferences = [];
        // New event model
        /** @var EventImage $eventImage */
        foreach ($event->getEventImages() as $eventImage) {
            try {
                if ((($fileReference = $eventImage->getImageFile()) instanceof FileReferenceModel)
                    && $originalFileUid = $this->getUidOfValidOriginalFile($fileReference)) {
                    $mapImportFileReferences[$originalFileUid] = $fileReference;
                }
            } catch (ResourceDoesNotExistException $e) {
                unset($e);
            }
        }
        $mapImportFileUidList = array_keys($mapImportFileReferences);
        $existingFileUids = $this->processExistingFileReferences($object->getFalMedia(), $mapImportFileUidList);
        foreach ($mapImportFileReferences as $origFileUid => $fileReference) {
            if (!in_array($origFileUid, $existingFileUids, false)
                && ($falReferenceModel = $this->getFalObject($fileReference)) instanceof NewsFileReferenceAlias) {
                $object->addFalMedia($falReferenceModel);
            }
        }
    }

    /**
     * Returns the list of file uid's that already are referenced by the current object
     * Additionally filters out duplicates (that were already stored before)
     * @param ObjectStorage|FileReferenceModel[] $fileReferences
     * @param array|int[] $mapImportFileUidList File UID list of import file references
     * @return array|int[] $existingFileUidList
     */
    private function processExistingFileReferences($fileReferences, array $mapImportFileUidList): array
    {
        $existingFileUidList = [];
        foreach ($fileReferences as $existingMedia) {
            try {
                $originalFileUid = $this->getUidOfValidOriginalFile($existingMedia);
                // Remove file reference if either the file is not referenced in the imported files or the file is a duplicate
                if ((!in_array($originalFileUid, $mapImportFileUidList, true)
                    || in_array($originalFileUid, $existingFileUidList, true))) {
                    $fileReferences->detach($existingMedia);
                    $this->persistenceManager->remove($existingMedia);
                } else {
                    $existingFileUidList[] = $originalFileUid;
                }
            } catch (ResourceDoesNotExistException) {
                $fileReferences->detach($existingMedia);
                $this->persistenceManager->remove($existingMedia);
            }
        }
        return $existingFileUidList;
    }

    /**
     * @param Event $event
     * @return array<string, mixed>
     */
    protected function getAdditionDataHandlerValues(Event $event): array
    {
        $eventName = (string)$event->getName();
        return [
            'title' => $eventName,
            'teaser' => (string)$event->getShortDescription(),
            'externalurl' => (string)$event->getUrl(),
            'path_segment' => (string)$this->createSlugForName($eventName),
        ];
    }

    /**
     * Creates a slug for the given event name
     * @param string $eventName
     * @return string|null
     */
    private function createSlugForName(string $eventName): ?string
    {
        $slug = null;
        if ($eventName !== '') {
            if (class_exists(SlugHelper::class)) {
                $slugConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];
                /** @var SlugHelper $slugService */
                $slugService = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $slugConfig);
                $slug = $slugService->sanitize($eventName);
            } elseif (class_exists(SlugService::class)) {
                /** @var SlugService $slugService */
                $slugService = GeneralUtility::makeInstance(SlugService::class);
                if (method_exists($slugService, 'generateSlug')) {
                    $slug = $slugService->generateSlug($eventName);
                }
            }
        }
        return $slug;
    }
}
