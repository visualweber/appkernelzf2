<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppKernel\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityManagerFactory extends AbstractFactory {

    /**
     * {@inheritDoc}
     * @return EntityManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        /* @var $options \DoctrineORMModule\Options\EntityManager */
        $options = $this->getOptions($serviceLocator, 'entitymanager');
        echo '<pre>';
        print_R($options);
        echo '</pre>';
        exit();
        $connection = $serviceLocator->get($options->getConnection());
        $config = $serviceLocator->get($options->getConfiguration());
        // initializing the resolver
        // @todo should actually attach it to a fetched event manager here, and not
        //       rely on its factory code
        $serviceLocator->get($options->getEntityResolver());

        // Table Prefix
        $tablePrefix = new \AppKernel\DoctrineExtension\TablePrefix('viweb_');
        $evm = $connection->getEventManager();
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);
        return EntityManager::create($connection, $config, $evm);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass() {
        return 'DoctrineORMModule\Options\EntityManager';
    }

}
