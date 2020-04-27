<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 25.04.2020
 */

namespace App\PortalBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BaseController extends AbstractController
{

	protected function getEntityName()
	{
		$div = explode(':', $this->name);

		return $div[count($div) - 1];
	}


	/**
	 *
	 * @return string
	 */
	protected function getPathName()
	{
		return strtolower(preg_replace('#^(.+?)Bundle:(.+?)$#', '$1_$2', $this->name));
	}


	protected function getBundleName()
	{
		$div = explode('\\', get_class($this));

		return $div[1];
	}

	protected function _newAction(Request $request, $item)
	{
		$form = $this->createForm('\App\\'. $this->getBundleName(). '\Form\\'. $this->getEntityName() .'Type', $item);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->get('doctrine')->getManager();
			$em->persist($item);
			$em->flush($item);

			return $this->redirectToRoute( $this->getPathName() );
		}

		return $this->render('@'. str_replace([':', 'Bundle'], ['/', ''], $this->name) .'/new.html.twig', [
			'item' => $item,
			'data' => (method_exists($this, 'newData')) ? $this->newData() : null,
			'form' => $form->createView(),
		]);
	}


	public function editAction(Request $request)
	{
		$item = $this->get('doctrine')->getRepository($this->name)->find($request->get('id'));

		$editForm = $this->createForm('\App\\'. $this->getBundleName() .'\Form\\'. $this->getEntityName() .'Type', $item);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {

			$this->get('doctrine')->getManager()->flush();

			return $this->redirectToRoute( $this->getPathName() );
		}

		return $this->render('@'. str_replace([':', 'Bundle'], ['/', ''], $this->name) .'/edit.html.twig', [
			'item'      => $item,
			'data'      => (method_exists($this, 'editData')) ? $this->editData() : null,
			'edit_form' => $editForm->createView()
		]);
	}


	public function deleteAction(Request $request)
	{
		$item = $this->get('doctrine')->getRepository($this->name)->find($request->get('id'));

		if (!$item) {
			throw new NotFoundHttpException('Элемент не найден');
		}

		$em = $this->get('doctrine')->getManager();
		$em->remove($item);
		$em->flush($item);

		return $this->redirectToRoute($this->getPathName());
	}

	protected function getFilters()
	{
		return $this
			->get('request_stack')->getCurrentRequest()
			->getSession()
			->get('filter_'. $this->getPathName(), null);
	}

	/**
	 * Парсит заголовок XLSX-файла в массив - общий метод для всех импортов
	 * array:2 [▼
	 *  "category" => 1
	 *  "email" => 2
	 * ]
	 *
	 * @param $pd
	 * @return array
	 */
	protected function importHeader($pd, $fields = null)
	{
		$header = [];

		if (!$fields) {
			$fields = $this->fields;
		}

		while ($line = fgetcsv($pd)) {
			if (!count($line)) {
				continue;
			}

			for ($i = 0; $i < count($line); $i++) {
				if (isset($fields[$line[$i]])) {
					$header[$fields[$line[$i]]] = $i;
				}
			}

			if (count($header) != count($fields)) {
				throw new BadRequestHttpException('Неверный формат импорт-файла');
			}

			return $header;
		}

		throw new BadRequestHttpException('Неверный формат импорт-файла');
	}
}