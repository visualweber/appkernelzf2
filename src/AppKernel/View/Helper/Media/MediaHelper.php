<?php

/**
 * @purpose using for files pdf,docx,doc, ....
 */
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

namespace AppKernel\View\Helper\Media;

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

class MediaHelper extends AbstractHelper implements ServiceLocatorAwareInterface {

    /**
     * 
     * @return type
     * @throws \RuntimeException
     * 
     * @call You can get the value from "view" by: $this->Config('settings', 'webapp', 'xxx', 'yyy', 'zzz');
     */
    protected $firstLevel = 100000;
    protected $secondLevel = 1000;

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function __invoke() {
        $args = func_get_args();
        if (!isset($args[0])) {
            throw new \RuntimeException("Media can not empty");
        }
        return call_user_func_array([$this, 'generatePath'], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePath($media) {
        $config = $this->serviceLocator->getServiceLocator()->get('Config');
        $path_upload = $config['settings']['webapp']['media']['path_upload'];

        if (is_object($media) && method_exists($media, 'getId') && $mediaId = $media->getId()):
            // $mediaId = $media->getId();
            // $media = $this->serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default')->getRepository('AppEntity\MediaMedia')->find($mediaId);

            $rep_first_level = (int) ( $mediaId / $this->firstLevel);
            $rep_second_level = (int) (($mediaId - ($rep_first_level * $this->firstLevel)) / $this->secondLevel);
            $file = sprintf('%s/%s/%04s/%02s/%s', $path_upload, $media->getContext(), $rep_first_level + 1, $rep_second_level + 1, $media->getProviderReference());
        elseif (is_array($media)):
            $rep_first_level = (int) ($media[0] / $this->firstLevel);
            $rep_second_level = (int) (($media[0] - ($rep_first_level * $this->firstLevel)) / $this->secondLevel);
            $file = sprintf('%s/%s/%04s/%02s/%s', $path_upload, $media[1], $rep_first_level + 1, $rep_second_level + 1, $media[2]);
        elseif (is_string($media)):
            $file = sprintf('%s/%s', $path_upload, $media);
        else:
            return false;
        endif;

        if (!is_file(PATH_ROOT . DS . $file)):
            return false;
        endif;

        return $this->view->basePath('../../../' . $file);
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
