<?php
/**
 * Created by:
 * User: svetlanakartysh
 * Date: 24.04.2020
 */

namespace App\PortalBundle\Form\Filter;
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Forms;


class RevisionFilter extends AbstractType
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
			->add('startFrom', DateType::class, [
				'label'    => 'от',
				'required' => false,
			])
			->add('startTill', DateType::class, [
				'label'    => 'до',
				'required' => false,
			])
			->add('endFrom', DateType::class, [
				'label'    => 'от',
				'required' => false,
			])
			->add('endTill', DateType::class, [
				'label'    => 'до',
				'required' => false,
			])
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'revision_filter';
	}
}