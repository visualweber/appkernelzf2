<?php

namespace AppKernel\Traits;

trait UsersAwareTrait {

    /**
     * @desc cac functions lien quan Elasticsearch, nen dat PREFIX la "Elastic_..."
     * @param type $alias
     * @return type
     */
    public function Elastic_createUserSlug($alias) {
        if (!is_array($this->config)):
            $this->config = $this->getServiceLocator()->get('Config');
        endif;
        $paramsEl = [
            'index' => $this->config['settings']['elasticsearch']['index_name'],
            'type' => 'ViwebUsers',
            'body' => [
                'query' => [
                    'match_phrase' => [
                        'userslug' => $alias
                    ],
                ],
            ],
        ];
        $results = $this->elasticSearchClient->search($paramsEl);
        if ($results['hits']['total']):
            $alias .= $this->random();
        endif;
        //
        return $this->createAlias($alias); // \AppKernel\Traits\Utils
    }

    /**
     * @desc cac functions lien quan Elasticsearch, nen dat PREFIX la "Elastic_..."
     * @param type $alias
     * @return type
     */
    public function Elastic_checkEmailIsExist($email) {
        if (!is_array($this->config)):
            $this->config = $this->getServiceLocator()->get('Config');
        endif;
        $paramsEl = [
            'index' => $this->config['settings']['elasticsearch']['index_name'],
            'type' => 'ViwebUsers',
            'body' => [
                'query' => [
                    'match_phrase' => [
                        'email' => $email
                    ]
                ]
            ]
        ];
        $results = $this->elasticSearchClient->search($paramsEl);
        return $results['hits']['total'] ? $results['hits']['hits'][0]['_source'] : FALSE;
    }

    public function Elastic_getCredit($args) {
        if (isset($args['user_id']) && (int) ($args['user_id'])):
            $dm = $this->getDoctrineMongo();
            $results = $dm->createQueryBuilder('AppDocument\CreditLogs')
                    ->hydrate(false)
                    ->field('user')->equals((int) $args['user_id'])
                    ->map('function() { emit(this.user, this.credit); }')
                    ->reduce('function(k, vals) {
                    var sum = 0;
                    for (var i in vals) {
                        sum +=  vals[i];
                    }
                    return sum;
                }')
                    ->getQuery()
                    ->toArray();
            if ($results):
                $result = array_shift($results);
                return (int) $result['value'];
            endif;
        endif;
        return 0;
    }

}
