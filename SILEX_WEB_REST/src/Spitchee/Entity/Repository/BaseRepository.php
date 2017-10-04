<?php

namespace Spitchee\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class BaseRepository extends EntityRepository
{
    /**
     * @param array $args
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder($args = array());

    protected function integrityCheck($args = array()) {
        if (0 == count($args)) {
            return;
        }

        $errors = array();
        foreach ($args as $key => $val) {
            if ($val !== null) {
                $errors[] = '[' . $key . ']' . '=>' . $val;
            }
        }

        if (count($errors)) {
            $error  = 'Integrety check failure!! Args non vide. { ';
            $error .= join(PHP_EOL, $errors);
            $error .= ' }';

            throw new \Exception($error);
        }
    }

    // TODO : Un build search qui prends un callback faisant la recherche

    protected function buildSearches($prefix, &$queryArgs, QueryBuilder &$qb, $mixed)
    {
        if (is_string($mixed)) $mixed = [$mixed];

        foreach ($mixed as $field) { // TODO : Sous le isset, si arg est array on va IN
            if (isset($queryArgs[$field])) {
                $qb->andWhere($prefix . '.' . $field . ' = :_' . $field);
                $qb->setParameter('_' . $field, $queryArgs[$field]);

                unset($queryArgs[$field]);
            }

            if (isset($queryArgs[$field . '%'])) {
                $arg = $queryArgs[$field . '%'];
                if (substr($arg, 0, 1) !== '%') $arg = '%' . $arg .'%';

                $qb->andWhere($prefix . '.' . $field . ' LIKE :__' . $field);
                $qb->setParameter('__' . $field, $arg);

                unset($queryArgs[$field . '%']);
            }
        }
    }

    protected function buildSorts($prefix, &$queryArgs, QueryBuilder &$qb, $mixed)
    {
        if (is_string($mixed)) $mixed = [$mixed];

        foreach ($mixed as $field) {
            if (isset($queryArgs['sort_' . $field])) {
                $qb->addOrderBy($prefix . '.' . $field, $queryArgs['sort_' . $field]);
            }
            unset($queryArgs['sort_' . $field]);
        }
    }
}