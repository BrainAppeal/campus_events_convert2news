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


use BrainAppeal\CampusEventsConnector\Importer\PostImportHookInterface;

// campus_events_connector > 2.x
if (class_exists(\BrainAppeal\CampusEventsConnector\Importer\PostImportHookInterface::class)) {

    class PostImportHook extends AbstractPostImportHook implements PostImportHookInterface
    {
    }

} else {
    class PostImportHook extends AbstractPostImportHook
    {
    }

}
