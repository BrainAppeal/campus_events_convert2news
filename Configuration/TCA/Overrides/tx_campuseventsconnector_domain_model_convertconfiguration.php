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

$GLOBALS['TCA']['tx_campuseventsconnector_domain_model_convertconfiguration']['ctrl']['typeicon_classes'][1] = 'ext-convertconfiguration-type-news';
$GLOBALS['TCA']['tx_campuseventsconnector_domain_model_convertconfiguration']['types'][1] = ['showitem' => 'type, target_pid, txnews_type, template_path, target_groups, filter_categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;paletteLanguage,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
            --palette--;;access'];
$GLOBALS['TCA']['tx_campuseventsconnector_domain_model_convertconfiguration']['columns']['type']['config']['items'][1] = [
    'label' => 'LLL:EXT:campus_events_convert2news/Resources/Private/Language/locallang_db.xlf:tx_campuseventsconnector_domain_model_convertconfiguration.convert2news',
    'value' => 1,
    'icon' => 'EXT:news/Resources/Public/Icons/news_domain_model_news.svg'
];

$additionalFields = [
    'txnews_type' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:campus_events_convert2news/Resources/Private/Language/locallang_db.xlf:tx_campuseventsconnector_domain_model_convertconfiguration.txnews_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    'label' => 'LLL:EXT:news/Resources/Private/Language/locallang_db.xlf:tx_news_domain_model_news.type.I.0',
                    'value' => 0,
                    'icon' => 'EXT:news/Resources/Public/Icons/news_domain_model_news.svg'
                ],
                [
                    'label' => 'LLL:EXT:news/Resources/Private/Language/locallang_db.xlf:tx_news_domain_model_news.type.I.2',
                    'value' => 2,
                    'icon' => 'EXT:news/Resources/Public/Icons/news_domain_model_news_external.svg'
                ],
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
