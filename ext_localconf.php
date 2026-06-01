<?php

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
defined('TYPO3') || die();

call_user_func(
    static function ($extKey) {
        // typo3-dev/fb05/typo3conf/ext/news/Documentation/DeveloperManual/ExtendNews/ProxyClassGenerator/Index.rst
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = $extKey;
    },
    'campus_events_convert2news'
);
