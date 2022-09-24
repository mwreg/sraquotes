<?php
defined('TYPO3_MODE') || die();

call_user_func(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Sraquotes',
        'Plstraquotes',
        [
            \Mwreg\Sraquotes\Controller\QuoteController::class => 'index'
        ],
        // non-cacheable actions
        [
            
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    plstraquotes {
                        iconIdentifier = sraquotes-plugin-plstraquotes
                        title = LLL:EXT:sraquotes/Resources/Private/Language/locallang_db.xlf:tx_sraquotes_plstraquotes.name
                        description = LLL:EXT:sraquotes/Resources/Private/Language/locallang_db.xlf:tx_sraquotes_plstraquotes.description
                        tt_content_defValues {
                            CType = list
                            list_type = sraquotes_plstraquotes
                        }
                    }
                }
                show = *
            }
       }'
    );

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'sraquotes-plugin-plstraquotes',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:sraquotes/Resources/Public/Icons/user_plugin_plstraquotes.svg']
    );
});
