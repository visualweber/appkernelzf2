<?php

/**
 * http://ebanshi.cc/questions/2577908/how-to-integrate-elasticsearch-in-zend-framework-2-using-doctrine-2
 * http://stackoverflow.com/questions/25242126/how-to-integrate-elasticsearch-in-zend-framework-2-using-doctrine-2
 * 
 */

namespace AppKernel\Traits;

trait ElasticSearchAwareTrait {

    protected $elasticSearchClient = null;

    public function getElasticSearchClient() {
        return $this->elasticSearchClient;
    }

    public function setElasticSearchClient(\Elasticsearch\Client $client) {
        $this->elasticSearchClient = $client;
        return $this;
    }

    /**
     * 
     * @param type $type
     * @param type $entityIds
     * @return boolean
     */
    public function elasticsearchDeleteIndex($type = '', $entityIds = array()) {
        if (!is_array($this->config)):
            $this->config = $this->getServiceLocator()->get('Config');
        endif;
        //
        $elConfig = $this->config['settings']['elasticsearch'];
        
        if ($type && !empty($entityIds)):
            $params = [
                'index' => $elConfig['index_name'],
                'type' => $type,
                'body' => [
                    'query' => [
                        'filtered' => [
                            'query' => [
                                "ids" => [
                                    "values" => $entityIds
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $this->elasticSearchClient->deleteByQuery($params);
        endif;
        
        return true;
    }

    /**
     * update or new
     * @return boolean
     */
    public function elasticsearchMakeIndex($entity) {
        $class = explode('\\', get_class($entity));
        $type = $class[1];
        $elConfig = $this->config['settings']['elasticsearch'];
        //
        if (isset($elConfig['types'][$type]) && $elConfig['types'][$type]):
            $entityId = $entity->getId(); // hard code cai Function nay the nay ko hay.
            $this->elasticsearchDeleteIndex($type, (array) $entityId);
            $params = [];
            $params['body'][] = [
                'index' => [
                    '_index' => $elConfig['index_name'],
                    '_type' => $type,
                    '_id' => $entityId,
                ]
            ];
            $body = [];
            //
            foreach ($elConfig['types'][$type] as $field => $nest):
                if (is_string($nest)): //Fields thuoc entity dang danh index
                    $method = 'get' . ucfirst($nest);
                    if (method_exists($entity, $method)):
                        $_entt = $entity->$method();
                        // neu la kieu ngay thang => lay timestamp
                        if ($_entt):
                            if ($_entt instanceof \DateTime):
                                $body[$nest] = (int) $_entt->getTimestamp();
                            // neu la kieu anh => doi ra duong dan
                            elseif ($_entt instanceof \AppEntity\MediaMedia):
                                $body[$nest] = $_entt->__toString();
                            else:
                                $body[$nest] = $entity->$method();
                            endif;
                        else:
                            $body[$nest] = null;
                        endif;
                    endif;
                else:
                    // fields thuoc entity trong entity dang danh index
                    $method = 'get' . ucfirst($field);
                    if (method_exists($entity, $method)):
                        // sub entity
                        $sub = $entity->$method();
                        if ($sub):
                            $nestData = [];
                            if (method_exists($sub, 'getId')):// single endtity - quan he 1-1 hoac n-1
                                foreach ($nest as $f):
                                    $method = 'get' . ucfirst($f);
                                    if (method_exists($sub, $method)):
                                        $_entity = $sub->$method();
                                        $_setData = '';
                                        if (is_object($_entity)):
                                            if (($_entity) && $_entity instanceof \AppEntity\MediaMedia):
                                                $_setData = $_entity->__toString();
                                            elseif (($_entity) && $_entity instanceof \AppEntity\ViwebCompany):
                                                $_setData = array(
                                                    'alias' => $_entity->getAlias(),
                                                    'name' => $_entity->getName()
                                                );
                                            elseif (($_entity) && $_entity instanceof \DateTime):
                                                $_setData = (int) $_entity->getTimestamp();
                                            elseif (($_entity) && method_exists($_entity, 'getName')):
                                                $_setData = $_entity->getName();
                                            else:
                                                $_setData = $_entity->getId();
                                            endif;

                                        else:
                                            $_setData = $_entity;
                                        endif;
                                        if ($f == 'salary'):
                                            $_setData = (int) $_setData;
                                        endif;
                                        $nestData[$f] = $_setData;
                                    endif;
                                endforeach;
                            else: // quan he n-n
                                $j = 0;
                                foreach ($sub as $subNest):
                                    foreach ($nest as $f):
                                        $method = 'get' . ucfirst($f);
                                        if (method_exists($subNest, $method)):
                                            $tmp = $subNest->$method();
                                            if (is_object($tmp)):
                                                if (($tmp) && $tmp instanceof \AppEntity\ViwebCompany):
                                                    $nestData[$j][$f] = array(
                                                        'alias' => $tmp->getAlias(),
                                                        'name' => $tmp->getName()
                                                    );
                                                elseif (($tmp) && $tmp instanceof \DateTime):
                                                    $nestData[$j][$f] = $tmp->getTimestamp();
                                                elseif (($tmp) && method_exists($tmp, 'getName')):
                                                    $nestData[$j][$f] = $tmp->getName();
                                                else:
                                                    $nestData[$j][$f] = $tmp->getId();
                                                endif;
                                            else:
                                                $nestData[$j][$f] = $tmp;
                                            endif;
                                        endif;
                                    endforeach;
                                    $j++;
                                endforeach;

                            endif;
                        else:
                            $nestData = null;
                        endif;
                        $body[$field] = $nestData;
                    endif;
                endif;
            endforeach;

            $params['body'][] = $body;
            // $config['types'][$data['index_type']]
            $responses = $this->elasticSearchClient->bulk($params);
            if ($responses['errors']):
                return false;
            else:
                return true;
            endif;
        endif;
        return false;
    }

}
