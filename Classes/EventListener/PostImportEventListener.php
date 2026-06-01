<?php

declare(strict_types=1);

/**
 * campus_events_convert2news comes with ABSOLUTELY NO WARRANTY
 * See the GNU GeneralPublic License for more details.
 * https://www.gnu.org/licenses/gpl-2.0
 *
 * Copyright (C) 2026 Brain Appeal GmbH
 *
 * @copyright 2026 Brain Appeal GmbH (www.brain-appeal.com)
 * @license   GPL-2 (www.gnu.org/licenses/gpl-2.0)
 * @link      https://www.campus-events.com/
 */

namespace BrainAppeal\CampusEventsConvert2News\EventListener;

use BrainAppeal\CampusEventsConnector\Import\Event\ImportFinishEvent;
use BrainAppeal\CampusEventsConvert2News\Converter\Event2NewsConverter;
use BrainAppeal\CampusEventsConvert2News\Domain\Repository\Convert2NewsConfigurationRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(
    identifier: 'campus-events-convert2news/post-import',
    after: 'ce/import/finish-set-imported-at'
)]
class PostImportEventListener
{
    public function __construct(
        private readonly Event2NewsConverter                 $converter,
        private readonly Convert2NewsConfigurationRepository $configurationRepository
    )
    {
    }

    public function __invoke(ImportFinishEvent $event): void
    {
        $this->postImport($event->getContext()->options->getPid());
    }

    /**
     * @param int $pid The page id where the events are stored
     * @return void
     */
    public function postImport(int $pid): void
    {
        foreach ($this->configurationRepository->findActiveByPid($pid) as $config) {
            $this->converter->run($config);
        }
    }
}

