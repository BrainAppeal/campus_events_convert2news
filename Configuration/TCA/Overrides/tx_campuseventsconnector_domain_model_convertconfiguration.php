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

defined('TYPO3') or die();

$convertconfiguration = [
    'ctrl' => [
        'typeicon_classes' => [
            1 => 'ext-convertconfiguration-type-news',
        ],
    ],
    'types' => [
        1 => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, type, target_pid, txnews_type, template_path, target_groups, filter_categories, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'columns' => [
        'type' => [
            'config' => [
                'items' => [
                    1 => ['LLL:EXT:campus_events_convert2news/Resources/Private/Language/locallang_db.xlf:tx_campuseventsconnector_domain_model_convertconfiguration.convert2news', 1, 'ext-convertconfiguration-type-news']
                ]
            ]
        ]
    ],
];

$GLOBALS['TCA']['tx_campuseventsconnector_domain_model_convertconfiguration'] = array_replace_recursive(
    $GLOBALS['TCA']['tx_campuseventsconnector_domain_model_convertconfiguration'], $convertconfiguration
);


$additionalFields = [
    'txnews_type' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:campus_events_convert2news/Resources/Private/Language/locallang_db.xlf:tx_campuseventsconnector_domain_model_convertconfiguration.txnews_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:news/Resources/Private/Language/locallang_db.xlf:tx_news_domain_model_news.type.I.0', 0, 'EXT:news/Resources/Public/Icons/news_domain_model_news.svg'],
//                ['LLL:EXT:news/Resources/Private/Language/locallang_db.xlf:tx_news_domain_model_news.type.I.1', 1, 'EXT:news/Resources/Public/Icons/news_domain_model_news_internal.svg'],
                ['LLL:EXT:news/Resources/Private/Language/locallang_db.xlf:tx_news_domain_model_news.type.I.2', 2, 'EXT:news/Resources/Public/Icons/news_domain_model_news_external.svg'],
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_campuseventsconnector_domain_model_convertconfiguration',
    $additionalFields
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tx_campuseventsconnector_domain_model_convertconfiguration',
    'general',
    'txnews_type',
    'after:target_pid'
);
