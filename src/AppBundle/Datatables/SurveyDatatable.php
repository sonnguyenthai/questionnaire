<?php

namespace AppBundle\Datatables;

use Sg\DatatablesBundle\Datatable\AbstractDatatable;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\DateTimeColumn;

/**
 * Class SurveyDatatable
 *
 * @package AppBundle\Datatables
 */
class SurveyDatatable extends AbstractDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable(array $options = array())
    {
        $this->language->set(array(
            'cdn_language_by_locale' => true
            //'language' => 'de'
        ));

        $this->ajax->set(array(
        ));

        $this->options->set(array(
            'classes' => 'table table-striped table-bordered table-hover',
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order_cells_top' => true,
        ));

        $this->features->set(array(
        ));

        $this->extensions->set(array(
            'responsive' => true
        ));

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'width' => '40px'
                ))
            ->add('name', Column::class, array(
                'title' => 'Name',
                ))
            ->add('description', Column::class, array(
                'title' => 'Description',
                ))
            ->add('created_date', DateTimeColumn::class, array(
                'title' => 'Created_date',
                ))
            ->add('modified_date', DateTimeColumn::class, array(
                'title' => 'Modified_date',
                ))
            ->add('user.username', Column::class, array(
                'title' => 'User',
                'width' => '100px'
                ))
            ->add(null, ActionColumn::class, array(
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => array(
                    array(
                        'route' => 'survey_view_public',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => $this->translator->trans('sg.datatables.actions.show'),
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => $this->translator->trans('sg.datatables.actions.show'),
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                    ),
                    array(
                        'route' => 'survey_edit',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => $this->translator->trans('sg.datatables.actions.edit'),
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => $this->translator->trans('sg.datatables.actions.edit'),
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                    ),
                    array(
                        'route' => 'survey_show',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Result',
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Result',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                    ),
                    array(
                        'route' => 'survey_delete',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Delete',
                        'icon' => 'glyphicon glyphicon-remove',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Delete',
                            'class' => 'btn btn-danger btn-xs',
                            'role' => 'button'
                        ),
                    )
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'AppBundle\Entity\Survey';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'survey_datatable';
    }
}
