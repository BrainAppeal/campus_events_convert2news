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

namespace BrainAppeal\CampusEventsConvert2News\Domain\Model;

/**
 * News
 */
class News extends \GeorgRinger\News\Domain\Model\News implements \BrainAppeal\CampusEventsConnector\Domain\Model\ImportedModelInterface
{

    use \BrainAppeal\CampusEventsConnector\Domain\Model\ImportedModelTrait;

}
