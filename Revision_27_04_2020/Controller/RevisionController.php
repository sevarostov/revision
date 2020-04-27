<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 23.04.2020
 */

namespace App\PortalBundle\Controller;

use App\PortalBundle\Entity\Revision;
use App\PortalBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/revision")
 */
class RevisionController extends BaseController
{
	public $name = 'PortalBundle:Revision';

	protected $fields = [
		'Проверяемый СМП'         => 'user',
		'Контролирующий орган'    => 'revisor',
		'Дата начала проверки'    => 'startDate',
		'Дата окончания проверки' => 'endDate',
		'Плановая длительность'   => 'length',
	];

	/**
	 * @Route("/filter", name="portal_revision_filter")
	 * @Method("POST")
	 */
	public function filterAction(Request $request)
	{
		$session = $request->getSession();
		$filters = $session->get('revision_filter', []);

		$form = $this->createForm('\App\PortalBundle\Form\Filter\RevisionFilter');

		foreach ($form->all() as $item) {
			if (isset($_POST['revision_filter'][$item->getName()])) {
				$filters[$item->getName()] = $_POST['revision_filter'][$item->getName()];
			} else {
				unset($filters[$item->getName()]);
			}
		}

		$session->set('revision_filter', $filters);

		return $this->redirectToRoute('revision');
	}


	public function filterField_startFrom($request, $val)
	{
		return $this->filterField_start($request);
	}
	public function filterField_startTill($request, $val)
	{
		return $this->filterField_start($request);
	}
	private function filterField_start($request)
	{
		$filters = $request->get('revision_filter', []);

		$from = $this->dateToSql($filters['startFrom']['year'] . '-' . str_pad($filters['startFrom']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($filters['startFrom']['day'], 2, '0', STR_PAD_LEFT), '00:00:00');
		$till = $this->dateToSql($filters['startTill']['year'] . '-' . str_pad($filters['startTill']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($filters['startTill']['day'], 2, '0', STR_PAD_LEFT), '23:59:59');

		return [
			'field'  => 'startDate',
			'filter' => [
				$from ? $from : '1970-01-01 00:00:00',
				$till ? $till : '2999-01-01 00:00:00',
			],
		];
	}


	public function filterField_endFrom($request, $val)
	{
		return $this->filterField_end($request);
	}
	public function filterField_endTill($request, $val)
	{
		return $this->filterField_end($request);
	}
	private function filterField_end($request)
	{
		$filters = $request->get('revision_filter', []);

		$from = $this->dateToSql($filters['endFrom']['year'] . '-' . str_pad($filters['endFrom']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($filters['endFrom']['day'], 2, '0', STR_PAD_LEFT), '00:00:00');
		$till = $this->dateToSql($filters['endTill']['year'] . '-' . str_pad($filters['endTill']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($filters['endTill']['day'], 2, '0', STR_PAD_LEFT), '23:59:59');

		return [
			'field'  => 'endDate',
			'filter' => [
				$from ? $from : '1970-01-01 00:00:00',
				$till ? $till : '2999-01-01 00:00:00',
			],
		];
	}


	private function dateToSql($date, $time = '00:00:00')
	{
		if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $date)) {
			return null;
		}

		return $date .' '. $time;
	}


	/**
	 *
	 * @Route("/", name="portal_revision")
	 */
	public function indexAction(Request $request)
	{
		$revisionsRepo = $this->get('doctrine')->getRepository('PortalBundle:Revision');

		$filterForm = $this->createForm('\App\PortalBundle\Form\Filter\RevisionFilter');
		$findBy     = isset($this->filter) ? $this->filter : [];

		$filters  = $request->get('revision_filter');
		$doctrine = $this->get('doctrine');
		$userRepo = $doctrine->getRepository('PortalBundle:User');

		if (is_array($filters) and count($filters)) {
			foreach ($filters as $field => $filter) {

				if ($filterForm->has($field) and $filter) {

					$item = null;

					switch ($field) {
						case 'user':

							$item = $userRepo->find($filter);
							$findBy['user'] = $filter;
							break;

						case 'revisor':

							$item = $userRepo->find($filter);
							$findBy['revisor'] = $filter;
							break;

						case 'startFrom':

							$findBy['startDate'] = $this->filterField_startFrom($request, $filter)['filter'];
							break;

						case 'startTill':

							$findBy['startDate'] = $this->filterField_startFrom($request, $filter)['filter'];
							break;

						case 'endFrom':

							$findBy['endDate'] = $this->filterField_endFrom($request, $filter)['filter'];
							break;

						case 'endTill':

							$findBy['endDate'] = $this->filterField_endFrom($request, $filter)['filter'];
							break;
					}

					if ($item) {
						$filterForm->get($field)->setData($item);
					}
				}
			}
		}

		return $this->render('@Portal/Revision/index.html.twig', [
			'revisions' => $revisionsRepo->findBy($findBy),
			'filter'    => $filterForm->createView(),
		]);
	}


	/**
	 * AJAX autoComplete полей поиска юзеров
	 *
	 * @Route("/user/typeahead", name="portal_revision_user_typeahead")
	 * @Method("POST")
	 *
	 * POST:
	 * - query: ва
	 * - limit: 10
	 * - property: id
	 * - render: name
	 */
	public function userTypeaheadAction(Request $request)
	{
		return new JsonResponse(
			$this->get('doctrine')->getRepository('PortalBundle:User')->getListTypeahead(
				$request->get('query'),
				$request->get('limit'),
				'name',
				false
			)
		);
	}

	/**
	 * AJAX autoComplete полей поиска юзеров
	 *
	 * @Route("/revisor/typeahead", name="portal_revision_revisor_typeahead")
	 * @Method("POST")
	 *
	 * POST:
	 * - query: ва
	 * - limit: 10
	 * - property: id
	 * - render: name
	 */
	public function revisorTypeaheadAction(Request $request)
	{
		return new JsonResponse(
			$this->get('doctrine')->getRepository('PortalBundle:User')->getListTypeahead(
				$request->get('query'),
				$request->get('limit'),
				'name',
				true
			)
		);
	}


	/**
	 * @Route("/import", name="portal_revision_import")
	 * @Method("POST")
	 */
	public function importAction(Request $request)
	{
		if (!isset($_FILES['file']['error']) or $_FILES['file']['error']) {
			throw new BadRequestHttpException('Неверные входные параметры');
		}

		$pd = popen(sprintf('/usr/bin/xlsx2csv -s %d "%s"', 1, $_FILES['file']['tmp_name']), 'r');

		$this->body(
			$this->importHeader($pd),
			$pd
		);

		return $this->redirectToRoute('portal_revision');
	}


	/**
	 *
	 * @param $header
	 * @param $pd
	 *
	 * @return bool
	 */
	private function body($header, $pd)
	{
		$done = 0;

		$doctrine = $this->get('doctrine');
		$em       = $doctrine->getManager();

		while ($line = fgetcsv($pd)) {
			if (!count($line)) {
				continue;
			}

			/** @var User $user */
			$user = $doctrine->getRepository('PortalBundle:User')->findOneBy(
				['name'    => $line[$header['user']],
				 'revisor' => false,
				]
			);

			if (!$user) {

				$user = new User();
				$user->setName($line[$header['user']]);
				$user->setRevisor(false);

				$em->persist($user);
				$em->flush($user);
			}

			/** @var User $revisor */
			$revisor = $doctrine->getRepository('PortalBundle:User')->findOneBy(
				['name'    => $line[$header['revisor']],
				 'revisor' => true,
				]
			);

			if (!$revisor) {

				$revisor = new User();
				$revisor->setName($line[$header['revisor']]);
				$revisor->setRevisor(true);

				$em->persist($revisor);
				$em->flush($revisor);
			}

			if (!floatval($line[$header['length']])) {
				continue;
			}

			$revision = $doctrine->getRepository('PortalBundle:Revision')->findBy([
				'user'      => $user->getId(),
				'revisor'   => $revisor->getId(),
				'startDate' => new \DateTime($line[$header['startDate']] . ' 00:00:00'),
				'endDate'   => new \DateTime($line[$header['endDate']] . ' 00:00:00'),
				'length'    => $line[$header['length']],
			]);

			if ($revision) {
				continue;
			}

			/** @var Revision $revision */
			$revision = new Revision();
			$revision->setUser($user);
			$revision->setRevisor($revisor);
			$revision->setStartDate(new \DateTime($line[$header['startDate']] . ' 00:00:00'));
			$revision->setEndDate(new \DateTime($line[$header['endDate']] . ' 00:00:00'));
			$revision->setLength($line[$header['length']]);

			$em->persist($revision);
			$em->flush($revision);
			$done++;
		}

		return $done;
	}

	/**
	 * @Route("/export", name="portal_revision_export")
	 * @Method("GET")
	 */
	public function exportAction(Request $request)
	{
		$writer = new \XLSXWriter();

		$header = [];

		foreach (array_flip($this->fields) as $field => $value) {
			$header[$value] = 'string';
		}

		$sheet = 'reestr';

		$writer->writeSheetHeader($sheet, $header,
			['auto_filter' => true, 'widths' => [40, 40, 15, 15]]
		);

		$writer->setTitle('Реестр');
		$writer->setSubject('Реестр');
		$writer->setAuthor('Светлана Картыш');
		$writer->setCompany('Светлана Картыш');
		$writer->setKeywords(['Реестр']);

		$items = $this->get('doctrine')->getRepository('PortalBundle:Revision')->findAll();

		foreach ($items as $revision) {
			if (!$revision->getUser()) {
				continue;
			}

			$line = [];

			/** @var Revision $revision */
			$line[] = $revision->getUser()->getName();
			$line[] = $revision->getRevisor()->getName();
			$line[] = $revision->getStartDate()->format('d.m.Y');
			$line[] = $revision->getEndDate()->format('d.m.Y');
			$line[] = $revision->getLength();

			$writer->writeSheetRow($sheet, $line);

		}

		header('Content-Disposition: attachment; filename="Реестр плановых проверок.xlsx"');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		$writer->writeToStdOut();

		exit();
	}

	/**
	 * @Route("/new", name="portal_revision_new")
	 * @Method({"GET", "POST"})
	 */
	public function newAction(Request $request)
	{
		$item = new Revision();

		return parent::_newAction($request, $item);
	}

	/**
	 * @Route("/{id}", name="portal_revision_show", requirements={"id"="\d+"})
	 */
	public function showAction(Request $request)
	{
		$item = $this->get('doctrine')->getRepository($this->name)->find($request->get('id'));

		if (!$item) {
			throw new NotFoundHttpException('Объект не найден');
		}

		return $this->render('@Portal/Revision/show.html.twig', [
			'item' => $item,
		]);
	}


	/**
	 * @Route("/{id}/edit", name="portal_revision_edit", requirements={"id"="\d+"})
	 * @Method({"GET", "POST"})
	 */
	public function editAction(Request $request)
	{
		return parent::editAction($request);
	}

	/**
	 * @Route("/{id}/delete", name="portal_revision_delete", requirements={"id"="\d+"})
	 * @Method("GET")
	 */
	public function deleteAction(Request $request)
	{
		return parent::deleteAction($request);
	}
}