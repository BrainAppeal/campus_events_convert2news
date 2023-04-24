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
 * Convert2NewsConfiguration
 */
class Convert2NewsConfiguration extends \BrainAppeal\CampusEventsConnector\Domain\Model\ConvertConfiguration
{

    /**
     * @var int
     */
    protected $txnewsType;

    /**
     * @return int
     */
    public function getTxnewsType()
    {
        return $this->txnewsType;
    }

    /**
     * @param int $txnewsType
     */
    public function setTxnewsType($txnewsType)
    {
        $this->txnewsType = $txnewsType;
    }

    /**
     * Get sys language
     *
     * @return int
     */
    public function getSysLanguageUid(): int
    {
        return $this->_languageUid;
    }

}
