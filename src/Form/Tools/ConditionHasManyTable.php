<?php

namespace Exceedone\Exment\Form\Tools;

use Exceedone\Exment\Enums\FilterKind;
use Exceedone\Exment\ConditionItems\ConditionItemBase;

/**
 * ConditionHasManyTable
 */
class ConditionHasManyTable
{
    /**
     * laravel-admin form
     *
     * @var \Encore\Admin\Form
     */
    protected $form;

    /**
     * Ajax Url if column select
     *
     * @var string
     */
    protected $ajax;

    /**
     * Linkage url
     *
     * @var string
     */
    protected $linkage;
    protected $targetOptions;
    protected $name;

    /**
     * Target custom table
     *
     * @var CustomTable
     */
    protected $custom_table;
    protected $label;
    protected $useJoinOptions = true;
    protected $useJoinOptionAttribute;

    protected $callbackField;

    /**
     * Condition target name. Condition target is This column is used to narrow down this value.
     *
     * @var string
     */
    protected $condition_target_name = 'condition_target';

    /**
     * Condition key name. Condition key is how to filter value, ex. eq le lt gt....
     *
     * @var string
     */
    protected $condition_key_name = 'condition_key';

    /**
     * Condition value name. Condition value is for database query filter.
     *
     * @var string
     */
    protected $condition_value_name = 'condition_value';
    
    /**
     * Condition target label.
     *
     * @var string
     */
    protected $condition_target_label;

    /**
     * Condition key label
     *
     * @var string
     */
    protected $condition_key_label;

    /**
     * Condition value label
     *
     * @var string
     */
    protected $condition_value_label;
    
    protected $condition_join_name = 'condition_join';

    /**
     * This condition's type.
     *
     * @var FilterKind
     */
    protected $filterKind = FilterKind::VIEW;

    /**
     * Whether shoing condition key.
     *
     * @var boolean
     */
    protected $showConditionKey = true;

    public function __construct(&$form, $options = [])
    {
        $this->form = $form;
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $defaults = [
            'label' => "custom_view.custom_view_filters",
            'condition_target_label' => "condition.condition_target",
            'condition_key_label' => "condition.condition_key",
            'condition_value_label' => "condition.condition_value",
        ];
        foreach ($defaults as $k => $def) {
            if (!array_has($options, $k)) {
                $this->{$k} = exmtrans($def);
            }
        }
    }

    public function render()
    {
        // get key name
        $condition_target_name = $this->condition_target_name;
        $condition_key_name = $this->condition_key_name;
        $condition_value_name = $this->condition_value_name;
        $filterKind = $this->filterKind;

        $field = $this->form->hasManyTable($this->name, $this->label, function ($form) use ($condition_target_name, $condition_key_name, $condition_value_name, $filterKind) {
            $field = $form->select($condition_target_name, $this->condition_target_label)->required()
                ->options($this->targetOptions);
            if (isset($this->linkage)) {
                $field->attribute([
                    'data-linkage' => $this->linkage,
                    'data-change_field_target' => $condition_target_name,
                ]);
            }

            if ($this->showConditionKey) {
                $form->select($condition_key_name, $this->condition_key_label)->required()
                ->options(function ($val, $select) use ($condition_target_name, $condition_key_name, $condition_value_name, $filterKind) {
                    if (!isset($val)) {
                        return [];
                    }

                    $data = $select->data();
                    $condition_target = array_get($data, $condition_target_name);

                    $item = ConditionItemBase::getItem($this->custom_table, $condition_target);
                    if (!isset($item)) {
                        return null;
                    }
                    $item->filterKind($filterKind);

                    return $item->getFilterCondition()->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['text']];
                    });
                });
            }

            $label = $this->condition_value_label;
            $form->changeField($condition_value_name, $label)
                ->filterKind($filterKind)
                ->ajax($this->ajax)
                ->setEventTrigger("select.$condition_key_name")
                ->setEventTarget("select.$condition_target_name")
                ->replaceSearch($condition_key_name)
                ->replaceWord($condition_value_name)
                ->showConditionKey($this->showConditionKey)
                ->adminField(function ($data, $field) use ($label, $condition_target_name, $condition_key_name, $condition_value_name) {
                    if (is_null($data)) {
                        return null;
                    }
                    $item = ConditionItemBase::getItem($this->custom_table, array_get($data, $condition_target_name));
                    $item->filterKind($this->filterKind);

                    $item->setElement($field->getElementName(), $condition_value_name, $label);

                    return $item->getChangeField(array_get($data, $condition_key_name), $this->showConditionKey);
                });
            ;
        })->setTableWidth(10, 1);

        if ($this->showConditionKey) {
            $field->setTableColumnWidth(4, 4, 3, 1);
        } else {
            $field->setTableColumnWidth(6, 5, 1);
        }

        if (isset($this->callbackField)) {
            $func = $this->callbackField;
            $func($field);
        }
    }

    public function callbackField($callbackField)
    {
        $this->callbackField = $callbackField;

        return $this;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render()->render() ?? '';
    }
}
