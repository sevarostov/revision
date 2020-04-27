<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 23.04.2020
 */

namespace App\PortalBundle\Entity\Repo;
use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{

	/**
	 * Дополняет запрос всякими условиями на основании criteria
	 *
	 * @param $q DQL
	 * @param array $criteria
	 */
	protected function criteria($q, array $criteria)
	{
		$join = [];
		$number = 1;

		foreach ($criteria as $field => $value) {
			if (preg_match('/^([^.]+)\.([^.]+)\.(.+?)$/', $field, $out)) {
				$join[ $out[1] ] = 'z.'. $out[1];
				$join[ $out[2] ] = $out[1] .'.'. $out[2];

				$prefix = '';

				$field = $out[2] .'.'. $out[3];
			}
			elseif (preg_match('/^([^.]+)\.(.+?)$/', $field, $out)) {
				$join[ '_'. $out[1] ] = 'z.'. $out[1];
				$prefix = '_';
			}
			else {
				$prefix = 'z.';
			}

			if (is_array($value) and isset($value['not null'])) {
				$q->andWhere($prefix . $field .' is not null');
			}
			elseif (is_array($value) and isset($value['in'])) {
				$q->andWhere($prefix .$field .' in (' . $value['in']. ' )');
			}
			elseif (is_array($value) and isset($value['like'])) {
				$q->andWhere($prefix . $field .' like :f'. $number);
				$q->setParameter('f'. $number, $value['like']);
			}
			elseif (is_array($value) and (count($value) == 2)) {
				if (($value[0] !== null) or ($value[1] !== null)) {
					$q->andWhere($prefix . $field .' between :f'. $number .'_from AND :f'. $number .'_till' );
					$q->setParameter('f'. $number .'_from', $value[0]);
					$q->setParameter('f'. $number .'_till', $value[1]);
				}
			}
			elseif ($value === null) {
				$q->andWhere($prefix . $field .' is null');
			}
			else {
				$q->andWhere($prefix . $field .' = :f'. $number);
				$q->setParameter('f'. $number, $value);
			}

			$number++;
		}

		if (count($join)) {
			foreach ($join as $alias => $table) {
				$q->join($table, $alias);
			}
		}
	}


	/**
	 * Расширенный findBy - добавляет:
	 * - between при задании array[2] значения criteria.value
	 * - like при задании criteria.value['like'] = значение
	 * - is null при === null
	 * - поиск по связанным таблицам (many to one)
	 *
	 * @param array $criteria
	 * @param array|NULL $orderBy
	 * @param null $limit
	 * @param null $offset
	 * @return mixed
	 */
	public function findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		$q = $this->createQueryBuilder('z');

		$this->criteria($q, $criteria);

		if ($limit) {
			$q->setMaxResults($limit);
		}

		if ($offset) {
			$q->setFirstResult($offset);
		}

		if (is_array($orderBy) and count($orderBy)) {
			foreach ($orderBy as $field => $dir) {
				$q->orderBy('z.'. $field, $dir);
			}
		}

		return $q->getQuery()
			->getResult();
	}

}