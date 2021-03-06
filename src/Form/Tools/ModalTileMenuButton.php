<?php

namespace Exceedone\Exment\Form\Tools;

/**
 * Modal menu button.
 */
class ModalTileMenuButton extends ModalMenuButton
{
    protected $groups = [];

    public function __construct($options = [])
    {
        parent::__construct(null, $options);
    }

    /**
     * (array)
     * 	   header
     * 	   items
     * 	       icon
     *    	   href
     *         header
     * 		   description
     * 	       buttons
     * 			   icon
     * 			   href
     * 			   label
     *
     * @param [type] $items
     * @return void
     */
    public function groups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Get tile html
     *
     * @return string
     */
    public function html()
    {
        return view('exment::tools.modal-tile-menu', [
            'groups' => $this->groups
        ])->render();
    }

    public function render()
    {
        $this->html = $this->html();

        return parent::render();
    }
}
