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

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'CampusEvents Converter2News',
    'description' => '',
    'category' => 'be',
    'author' => 'Joshua Billert',
    'author_company' => 'Brain Appeal GmbH',
    'author_email' => 'info@brain-appeal.com',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.26-12.4.99',
            'campus_events_connector' => '3.0.7-4.999.99',
            'news' => '8.6.0-11.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'eventnews' => '>=5.0.0',
        ],
    ],
];
