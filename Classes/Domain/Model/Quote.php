<?php

declare(strict_types=1);

//        Vendor\ExtensionKey\Domain\Model
namespace Mwreg\Sraquotes\Domain\Model;


/**
 * This file is part of the "Random Quotes" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Marc Wampfler <mwreg@outlook.com>, 2108
 */


/**
 * Quote
 */
class Quote extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    public function index()
    {
        return "Hello from Model";
    }
}
