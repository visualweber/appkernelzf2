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

namespace AppKernel\View\Helper\Utils;

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

class UtilsHelper extends AbstractHelper implements ServiceLocatorAwareInterface {

    use \AppKernel\Traits\Utils; // Incl all methods from Utils's trait

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Retrieve a registered instance
     *
     * @param  string  $function
     * @param  string  $parameters
     * @throws Exception\ServiceNotFoundException
     * @return object|array
     * 
     * @call You can get the value from "view" by: $this->Utils('createAlias', 'Article title', 'xxx', 'yyy', 'zzz');
     */
    public function __invoke() {
        $args = func_get_args();
        if (!isset($args[0]) OR ! is_string($args[0])) {
            throw new \RuntimeException("Method can not empty");
        }

        if (isset($args[0]) AND ! method_exists($this, $args[0])) {
            throw new \RuntimeException("Method '{$args[0]}' does not exist in '\AppKernel\Traits\Utils'");
        }

        $function = $args [0];
        unset($args [0]);

        return call_user_func_array([$this, $function], $args);
    }

    /*
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */

    public function getQuery() {
        $routeMatch = $this->serviceLocator->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $result = array();
        if ($routeMatch):
            $params = $routeMatch->getParams();
            if (isset($params['action'])):
                $result['action'] = strtolower($params['action']);
                unset($params['action']);
            endif;
            if (isset($params['controller'])):
                $controller = explode('\\', $params['controller']);
                $result['module'] = strtolower($controller[1]);
                $result['controller'] = strtolower(end($controller));
                unset($params['controller']);
            endif;
            if (!empty($params)):
                foreach ($params as $key => $value):
                    if ($value && is_string($value)):
                        $result[strtolower($key)] = strtolower($value);
                    endif;
                endforeach;
            endif;
        endif;
        return $result;
    }

    public function getAdvsBanner() {
        $serviceLocator = $this->serviceLocator->getServiceLocator();
        $cache = $serviceLocator->get('cache');
        $advsBanner = $cache->getItem('advsBanner');
        if (!$advsBanner):
            $dm = $serviceLocator->get('doctrine.documentmanager.odm_default');
            $positionAdvs = $serviceLocator->get('Config')['settings']['advsposition'];
            if ($positionAdvs):
                $advs = $dm->createQueryBuilder('AppDocument\Advs')
                                ->select('content', 'position')
                                ->hydrate(false)
                                ->field('position')->in(array_keys($positionAdvs))
                                ->field('state')->equals(1)
                                ->getQuery()->execute()->toArray();
                if (!empty($advs)):
                    foreach ($advs as $adv):
                        if (!isset($advsBanner[$adv['position']])):
                            $advsBanner[$adv['position']] = [];
                        endif;
                        $advsBanner[$adv['position']][] = $adv['content'];
                    endforeach;
                    $cache->setItem('advsBanner', $advsBanner);
                endif;
            endif;
        endif;
        return $advsBanner;
    }

}
