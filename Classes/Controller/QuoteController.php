<?php

declare(strict_types=1);

//        Vendor\ExtensionKey\Controller
namespace Mwreg\Sraquotes\Controller;


use Mwreg\Sraquotes\Domain\Model\Quote;

/**
 * This file is part of the "Random Quotes" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Marc Wampfler <mwreg@outlook.com>, 2108
 */


/**
 * QuoteController
 */
class QuoteController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action index
     */
    public function indexAction()
    {
        $quote = new Quote;

        return $quote->index();
    }
}
