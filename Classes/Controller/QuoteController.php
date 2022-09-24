<?php

declare(strict_types=1);

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
     * Get single quote from Stämpfli AG quote database, using their API.
     *
     * @return string|object|null|void
     */
    public function indexAction()
    {
        // Create new quote object
        $quote = new Quote;

        // Get single quote
        $quote->setRandomQuotes( 1 );

        // Pass quotes (containing one quote) to the template
        $this->view->assign( 'quotes' , $quote->getQuotes() );
        $this->view->assign( 'extConfig' , $quote->getExtensionConfiguration() );
    }

    /**
     * Get multiple quotes from Stämpfli AG quote database, using their API.
     *
     * @return string|object|null|void
     */
    public function multipleQuotesAction()
    {
        // Create new quote object
        $quote = new Quote;

        // Get multiple quotes
        $quote->setRandomQuotes( 3 );

        // Pass quotes to the template
        $this->view->assign( 'quotes' , $quote->getQuotes() );
        $this->view->assign( 'extConfig' , $quote->getExtensionConfiguration() );
    }
}
