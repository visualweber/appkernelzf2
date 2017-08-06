<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppKernel\Media;

use Zend\ServiceManager\ServiceLocatorInterface,
    Zend\ServiceManager\ServiceLocatorAwareInterface;
use AppEntity\MediaMedia;

class Generator implements GeneratorInterface, ServiceLocatorAwareInterface {

    /**
     * @var int
     */
    protected $firstLevel;

    /**
     * @var int
     */
    protected $secondLevel;

    /**
     * @param int $firstLevel
     * @param int $secondLevel
     */
    public function __construct($firstLevel = 100000, $secondLevel = 1000) {
        $this->firstLevel = $firstLevel;
        $this->secondLevel = $secondLevel;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getFirstLevel() {
        return $this->firstLevel;
    }

    public function getSecondLevel() {
        return $this->secondLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePath(MediaMedia $media) {
        // $config = $this->serviceLocator->getServiceLocator()->get('Config');
        // $path_upload = $config['settings']['webapp']['media']['path_upload'];

        $rep_first_level = (int) ($media->getId() / $this->firstLevel);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $this->firstLevel)) / $this->secondLevel);

        return sprintf('%s/%04s/%02s', $media->getContext(), $rep_first_level + 1, $rep_second_level + 1, $media->getProviderReference());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl() {
        /** do something here * */
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl() {
        /** do something here * */
    }

}
