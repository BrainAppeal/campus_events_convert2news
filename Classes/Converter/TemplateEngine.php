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

class TemplateEngine extends \BrainAppeal\CampusEventsConnector\Utility\TemplateEngine
{

    /**
     * @param \BrainAppeal\CampusEventsConvert2News\Domain\Model\Convert2NewsConfiguration $configuration
     * @param string $templateName
     * @return string[]
     */
    protected function getTemplateRootPaths($configuration, $templateName)
    {
        return [
            0 => 'EXT:campus_events_convert2news/Resources/Private/Templates/',
            1 => $configuration->getTemplatePath(),
        ];
    }
}
