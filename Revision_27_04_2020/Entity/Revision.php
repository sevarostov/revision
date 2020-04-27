<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 24.04.2020
 */

namespace App\PortalBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="Revision")
 * @ORM\Entity(repositoryClass="App\PortalBundle\Entity\Repo\RevisionRepository")
 */
class Revision
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="App\PortalBundle\Entity\User", inversedBy="id", cascade={"persist"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	private $user;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="App\PortalBundle\Entity\User", inversedBy="id", cascade={"persist"})
	 * @ORM\JoinColumn(name="revisor_user_id", referencedColumnName="id")
	 */
	private $revisor;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	private $startDate;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	private $endDate;

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	private $length;

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
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return User
	 */
	public function getRevisor()
	{
		return $this->revisor;
	}

	/**
	 * @param User $revisor
	 */
	public function setRevisor($revisor)
	{
		$this->revisor = $revisor;
	}

	/**
	 * @return \DateTime
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * @param \DateTime $startDate
	 */
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getEndDate()
	{
		return $this->endDate;
	}

	/**
	 * @param \DateTime $endDate
	 */
	public function setEndDate($endDate)
	{
		$this->endDate = $endDate;
	}

	/**
	 * @return int
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * @param int $length
	 */
	public function setLength($length)
	{
		$this->length = $length;
	}
}