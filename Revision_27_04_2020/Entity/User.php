<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 23.04.2020
 */

namespace App\PortalBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="User", indexes={
 *    @ORM\Index(columns={"name"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="App\PortalBundle\Entity\Repo\UserRepository")
 */
class User
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=512)
	 */
	private $name;

	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	private $revisor;


	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return bool
	 */
	public function isRevisor()
	{
		return $this->revisor;
	}

	/**
	 * @param bool $revisor
	 */
	public function setRevisor($revisor)
	{
		$this->revisor = $revisor;
	}
}