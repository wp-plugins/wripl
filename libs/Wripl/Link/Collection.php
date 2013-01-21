<?php

/**
 * A collection of list items.
 *
 * @author brian
 */
class Wripl_Link_Collection extends Wripl_Link_Container
{

    public function __construct(array $items = null)
    {
        if (null != $items)
        {
            foreach ($items as $item)
            {
                $this->addLink(new Wripl_Link_Item($item));
            }
        }
    }

}