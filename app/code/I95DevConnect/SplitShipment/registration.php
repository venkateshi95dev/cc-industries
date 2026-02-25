<?php

/**
 * I95Dev.com
 *
 * Imageupload Class Doc Comment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.i95dev.com/LICENSE-M1.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sub@i95dev.com so we can send you a copy immediately.
 *
 * PHP version 5
 *
 * @category  I95Dev
 * @package   I95DevConnect_Splitshipment
 * @Description registration for module
 * @author    I95Dev <info@i95dev.com>
 * @copyright 2000-2016 i95Dev
 * @license   http://store.i95dev.com/LICENSE-M1.txt EULA
 * @link      http://store.i95dev.com/
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'I95DevConnect_Splitshipment',
    __DIR__
);
