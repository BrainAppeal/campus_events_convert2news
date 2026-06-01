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

use BrainAppeal\CampusEventsConnector\Domain\Model\ConvertConfiguration;

class TemplateEngine extends \BrainAppeal\CampusEventsConnector\Utility\TemplateEngine
{
    /**
     * @param ConvertConfiguration $configuration
     * @return string[]
     */
    protected function getTemplateRootPaths(ConvertConfiguration $configuration): array
    {
        return [
            0 => 'EXT:campus_events_convert2news/Resources/Private/Templates/',
            1 => $configuration->getTemplatePath(),
        ];
    }
}
