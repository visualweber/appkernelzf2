<?php

/**
 * http://ebanshi.cc/questions/2577908/how-to-integrate-elasticsearch-in-zend-framework-2-using-doctrine-2
 * http://stackoverflow.com/questions/25242126/how-to-integrate-elasticsearch-in-zend-framework-2-using-doctrine-2
 * https://zf2.readthedocs.io/en/latest/modules/zend.service-manager.html#initializers
 * 
 * You may want certain injection points to be always called. As an example, any object you load via the service manager that implements 
 * Zend\EventManager\EventManagerAwareInterface should likely receive an EventManager instance. 
 * Initializers can be either PHP callbacks or classes implementing Zend\ServiceManager\InitializerInterface. 
 * They receive the new instance, and can then manipulate 
 */

namespace AppKernel\Initializers;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AppKernel\Service\ElasticSearchAwareInterface;

/**
 * can nhac xem co su dung Initializer khong, 
 * vi object se duoc nap vao moi thoi diem khi instance EM duoc tao ra
 */
class ElasticSearchInitializer implements InitializerInterface {

    /**
     * Initializer for the elasticsearch-aware domain services.
     * Properly creates a new elasticsearch client and injects it into related service.
     */
    public function initialize($service, ServiceLocatorInterface $serviceManager) {
        /**
         * Beware: This if statement will be run for every service instance
         * we grab from $serviceManager because the nature of initializers.
         * This worth think about on it. With ZF3 this went further. 
         * We may load our services lazily using delegator factories.
         */
        if ($service instanceof ElasticSearchAwareInterface) {
            $service->setElasticSearchClient($serviceManager->getServiceLocator()->get('elasticsearch-client'));
        }
    }

}
