<?php

namespace AppKernel\View\Helper\Job;

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

class JobHelper extends AbstractHelper implements ServiceLocatorAwareInterface {

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * 
     * @return type
     * @throws \RuntimeException
     * 
     * @call You can get the value from "view" by: $this->Config('settings', 'webapp', 'xxx', 'yyy', 'zzz');
     */
    public function __invoke() {

        $args = func_get_args();
        if (!isset($args[0])) {
            throw new \RuntimeException("Function Name can not empty");
        }
        $function = $args[0];
        unset($args[0]);
        return call_user_func_array([$this, $function], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageJob($jobid) {
        if (isset($jobid) && (int) $jobid):
            $response = [];
            $dm = $this->serviceLocator->getServiceLocator()->get('doctrine.documentmanager.odm_default');

            // da tot nghiep gan day nhat
            $response = $dm->createQueryBuilder('AppDocument\JobsPackages')
                            ->sort('endTime', 'desc')
                            ->field('job')->equals((int) $jobid)
                            //                            ->field('endTime')->gte(new \DateTime())
                            ->getQuery()->toArray();

            return $response;
        else:
            return [];
        endif;
    }

}
