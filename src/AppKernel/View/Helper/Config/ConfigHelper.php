<?php

/**
 *            'view_helpers' => [
 *                'invokables' => [
 *                    'Config' => 'AppKernel\View\Helper\Config\ConfigHelper', // OR, You can register that in Module.php
 *                    'Utils' => 'AppKernel\View\Helper\Utils\UtilsHelper', // OR, You can register that in Module.php
 *                    'Image' => 'AppKernel\View\Helper\Media\ImageHelper', // OR, You can register that in Module.php
 *                    'Media' => 'AppKernel\View\Helper\Media\MediaHelper', // OR, You can register that in Module.php
 *                // 'Pagination' => 'AppKernel\View\Helper\PaginationHelper', // OR, You can register that in Module.php
 *                // 'strToLower' => 'View\Helper\StrToLower', // OR, You can register that in Module.php
 *                // 'sth_like_that' => 'View\Helper\PaginationHelper', // OR, You can register that in Module.php
 *                ],
 *            ],
 */

namespace AppKernel\View\Helper\Config;

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

class ConfigHelper extends AbstractHelper implements ServiceLocatorAwareInterface {
    /*
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @Hoang: em hoi tai sao getServiceLocator() 2 lan: lan 1 la get object serviceLocator tu local function 
     * ngay ben trong class nay *this* do em, sau do moi get cac methodology tu parent
     * 
     * @return type
     * @throws \RuntimeException
     * 
     * @call You can get the value from "view" by: $this->Config('settings', 'webapp', 'xxx', 'yyy', 'zzz');
     */
    public function __invoke() {
        $config = $this->getServiceLocator()->getServiceLocator()->get('Config');

        foreach (func_get_args() as $arg) {
            if (!isset($config[$arg])) {
                throw new \RuntimeException("Config option " . implode('.', func_get_args()) . " not found");
            }
            $config = $config[$arg];
        }
        return $config;
    }

}
