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

$EM_CONF['campus_events_convert2news'] = [
    'title' => 'CampusEvents Converter2News',
    'description' => '',
    'category' => 'be',
    'author' => 'Brain Appeal DEV Team',
    'author_company' => 'Brain Appeal GmbH',
    'author_email' => 'info@brain-appeal.com',
    'state' => 'stable',
    'version' => '5.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'campus_events_connector' => '5.0.0-5.99.99',
            'news' => '12.0.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'eventnews' => '>=6.0.0',
        ],
    ],
];
