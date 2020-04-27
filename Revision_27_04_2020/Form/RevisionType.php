<?php

namespace App\PortalBundle\Form;

use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class RevisionType extends AbstractType
{

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('user', TypeaheadType::class, [
				'label'    => 'Наименование СМП',
				'class'    => 'PortalBundle:User',
				'render'   => 'name',
				'route'    => 'portal_revision_user_typeahead',
				'required' => false,
			])
			->add('revisor', TypeaheadType::class, [
				'label'    => 'Контролирующий орган',
				'class'    => 'PortalBundle:User',
				'render'   => 'name',
				'route'    => 'portal_revision_revisor_typeahead',
				'required' => false,
			])
			->add('startDate', DateType::class, [
				'label'    => 'Дата начала проверки',
				'required' => true,
			])
			->add('endDate', DateType::class, [
				'label'    => 'Дата окончания проверки',
				'required' => true,
			])
			->add('length', IntegerType::class, [
				'label'    => 'Плановая длительность',
				'required' => true,
			])
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => 'App\PortalBundle\Entity\Revision'
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'revision';
	}

}
