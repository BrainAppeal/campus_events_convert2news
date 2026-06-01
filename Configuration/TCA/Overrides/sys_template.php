<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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

ExtensionManagementUtility::addStaticFile('campus_events_convert2news', 'Configuration/TypoScript', 'CampusEvents Converter2News');
