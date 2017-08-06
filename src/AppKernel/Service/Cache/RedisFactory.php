<?php

/**
 * Cau hinh cache level 2 chua xong. xem them Application/Dashboard/src/Dashboard/config/module.config.php
 * https://github.com/doctrine/DoctrineORMModule/blob/master/docs/cache.md
 * Add Annotations @Cache(usage="READ_ONLY", region="my_entity_region") to
 * /library/AppEntity/ViwebJobs.php
 */

namespace AppKernel\Service\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RedisFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);

        return $redis;
    }

}
