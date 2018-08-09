<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 10/07/2018
 * Time: 11:41
 */

namespace AppBundle\Service;


class Search
{

    public function __construct($finder)
    {
        $this->finder = $finder;
    }

    public function searchOffer($searchParam)
    {
        $searchParam = (array)json_decode($searchParam);
        $boolQuery = new \Elastica\Query\BoolQuery();

        if($searchParam['keywords'] != ''){
            $keywordBool = new \Elastica\Query\BoolQuery();
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('title', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('description', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('location', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('proposer.name', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $boolQuery->addMust($keywordBool);
        }

        if($searchParam['location'] != ''){
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('location', $searchParam['location']);
            $boolQuery->addMust($fieldQuery);
        }

        if($searchParam['proposer'] != '' and $searchParam['proposer'] != (string)0){
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('proposer.name', $searchParam['proposer']);
            $boolQuery->addMust($fieldQuery);
        }

        if(isset($searchParam['tags'])){
            $newBool = new \Elastica\Query\BoolQuery();

            foreach($searchParam['tags'] as $tag){

                $tagQuery = new \Elastica\Query\Match();
                $tagQuery->setFieldQuery('tag.name', $tag);
                $newBool->addShould($tagQuery);
            }

            $boolQuery->addMust($newBool);
        }

        if(isset($searchParam['type'])){
            $categoryQuery = new \Elastica\Query\Terms();
            $categoryQuery->setTerms('contractType.name', $searchParam['type']);
            $boolQuery->addMust($categoryQuery);
        }

        $fieldQuery = new \Elastica\Query\Match();
        $fieldQuery->setFieldQuery('archived', false);
        $boolQuery->addMust($fieldQuery);

        $boolQuery->addMust($fieldQuery);

        $query = new \Elastica\Query($boolQuery);


        $query->setSort(array('updateDate' => 'desc'));
        $data = $this->finder->find($query,3000);

        return $data;
    }

}