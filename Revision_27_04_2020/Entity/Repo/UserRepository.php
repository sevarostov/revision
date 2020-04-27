<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 23.04.2020
 */

namespace App\PortalBundle\Entity\Repo;
use App\PortalBundle\Entity\Repo\BaseRepository;
use App\PortalBundle\Entity\User;

class UserRepository extends BaseRepository
{
	/**
	 * https://github.com/lifo101/typeahead-bundle
	 *
	 * [
	 *  { id: 1, value: 'Displayed Text 1' },
	 *  { id: 2, value: 'Displayed Text 2' }
	 * ]
	 *
	 * @param $query string строка поиска
	 * @param $limit string кол-во результатов поиска
	 * @param string $field поле, по которому ищем (name | extId | email | post)
	 * @param $active boolean является-ли юзер активным
	 * @return array
	 */
	public function getListTypeahead($query, $limit, $field = 'name', $revisor = false)
	{
		$ret = [];

		$q = $this
			->createQueryBuilder('u')
			->select('u')
		;

		if ($revisor) {
			$q
				->andWhere('u.revisor = true');
		} else {
			$q
				->andWhere('u.revisor = false');
		}

		switch ($field) {
			case 'name':
				$q
					->andWhere('u.'. $field .' like :query')
					->setParameter('query', '%'. $query .'%');
				break;

			default:
				return [];
		}

		/** @var User $user */
		foreach (
			$q
				->orderBy('u.name', 'asc')
				->setMaxResults($limit)
				->getQuery()
				->getResult()
			as $user
		) {

			$ret[] = [
				'id'    => $user->getId(),
				'value' => $user->getName(),
			];
		}

		return $ret;
	}
}