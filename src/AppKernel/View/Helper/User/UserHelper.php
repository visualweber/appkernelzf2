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

namespace AppKernel\View\Helper\User;

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

class UserHelper extends AbstractHelper implements ServiceLocatorAwareInterface {

    use \AppKernel\Traits\UsersAwareTrait; // Incl all methods from UsersAwareTrait's trait

    public function getDoctrineMongo() {
        return $this->serviceLocator->getServiceLocator()->get('doctrine.documentmanager.odm_default');
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
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
    public function getLastExp($args) {
        if (isset($args[1]) && (is_object($args[1]) && method_exists($args[1], 'getId')) || (int) $args[1]):

            if (!is_object($args[1])):
                $uid = $args[1];
                $em = $this->serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default');
                $args[1] = $em->getRepository('AppEntity\ViwebUsers')->find($uid);
            else:
                $uid = $args[1]->getId();
            endif;

            $this->cache = $this->serviceLocator->getServiceLocator()->get('cache');
            $response = $this->cache->getItem('lastexp_' . $uid);
            if (!$response):
                $response = [];
                if (!isset($em)):
                    $em = $this->serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default');
                endif;

                $lastexp = $em->getRepository('AppEntity\ViwebResumeExperiences')->findOneBy(array('isCurrent' => 1, 'user' => $uid), array('startTime' => 'DESC'));
                if ($lastexp):
                    $response['company'] = ($company = $lastexp->getCompany()) ? $company->getName() : $lastexp->getCompanyCustomize();
                    $response['position'] = ($position = $lastexp->getPosition()) ? $position->getName() : $lastexp->getPositionCustomize();
                    $response['company_alias'] = ($company) ? $company->getAlias() : '';

                else:
                    $qbexp = $em->getRepository('AppEntity\ViwebResumeExperiences')->createQueryBuilder('exp');
                    $lastexp = $qbexp->where('exp.user = ' . (int) $uid)
                            ->andWhere('exp.endTime IS NOT NULL')
                            ->setMaxResults(1)
                            ->orderBy('exp.endTime', 'DESC')
                            ->getQuery()
                            ->getOneOrNullResult();
                    if ($lastexp):
                        $response['company'] = ($company = $lastexp->getCompany()) ? $company->getName() : $lastexp->getCompanyCustomize();
                        $response['position'] = ($position = $lastexp->getPosition()) ? $position->getName() : $lastexp->getPositionCustomize();
                        $response['company_alias'] = ($company) ? $company->getAlias() : '';
                    endif;
                endif;
                $this->cache->setItem('lastexp_' . $uid, $response);
            endif;
            return $response;
        else:
            return [];
        endif;
    }

    public function getLastEdu($args) {
        if (isset($args[1]) && method_exists($args[1], 'getId')):
            $uid = $args[1]->getId();
            $response = [];
            $em = $this->serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            // da tot nghiep gan day nhat
            $lastedu = $em->getRepository('AppEntity\ViwebResumeEducations')->findOneBy(array('eduGraduated' => 1, 'user' => $uid), array('timeend' => 'DESC'));
            if ($lastedu):
                $response['school'] = ($school = $lastedu->getSchool()) ? $school->getName() : $lastedu->getSchoolCustomize();
                $response['graduated'] = 1;
            else:
                $qbedu = $em->getRepository('AppEntity\ViwebResumeEducations')->createQueryBuilder('edu');
                $lastedu = $qbedu->where('edu.user = ' . (int) $uid)
                        ->setMaxResults(1)
                        ->orderBy('edu.timeend', 'DESC')
                        ->getQuery()
                        ->getOneOrNullResult();
                if ($lastedu):
                    $response['school'] = ($school = $lastedu->getSchool()) ? $school->getName() : $lastedu->getSchoolCustomize();
                    $response['graduated'] = 0;
                endif;
            endif;
            return $response;
        else:
            return [];
        endif;
    }

    public function getCountJobToday() {
        $config = $this->serviceLocator->getServiceLocator()->get('Config');
        $elasticsearch = $this->serviceLocator->getServiceLocator()->get('elasticsearch-client');
        $indexname = $config['settings']['elasticsearch']['index_name'];
        $countToday = 0;
        if ($elasticsearch->indices()->exists(array('index' => $indexname))):
            $params = [
                'index' => $indexname,
                'type' => 'ViwebJobs',
                "from" => 0,
                "size" => 0,
                'body' => [
                    'query' => [
                        'range' =>
                        [
                            "jobCreated" => [
                                "gte" => strtotime(date('Y-m-d 00:00:00')),
                                "lte" => strtotime(date('Y-m-d 23:59:59'))
                            ]
                        ]
                    ]
                ]
            ];

            $results = $elasticsearch->search($params);
            $countToday = $results['hits']['total'];
        endif;
        return $countToday;
    }

    /**
     * https://stackoverflow.com/questions/40746355/how-to-group-by-field-count-in-mongodb-querybuilder
     * http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/reference/query-builder-api.html#group-queries
     * 
     * @param type $args
     * @return int
     */
    public function getFollowers($args) {
        if (isset($args['user_id']) && (int) ($args['user_id'])):
            $dm = $this->getDoctrineMongo();
            $cnt = $dm->createQueryBuilder('AppDocument\UsersVisitor')
                            // ->hydrate(false)
                            ->group(['user' => true, 'visitor' => true], array('visited' => 0))
                            ->reduce('function (obj, prev) {
                                prev["visited"]++;
                            }')
                            ->field('user')->equals((int) $args['user_id'])
                            ->field('visitor')->notIn([$args['user_id']])
                            ->where("function() { return this.visitor > 0; }")
                            ->getQuery()->execute()->count();
            return $cnt ? $cnt : 0;
        endif;
        return 0;
    }

}
