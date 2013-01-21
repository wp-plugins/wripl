<?php

/**
 * A simple value class to represent an item.
 * Allows for simple serialization.
 *
 * @author brian
 */
class Wripl_Link_Item extends Wripl_Link_Container
{

    public $uri;
    //public $relevance;

    /**
     * Takes a standard class and maps it to a Wripl_Link_Item.
     * @param stdClass $item
     */
    public function __construct(stdClass $item = null)
    {
        if (null != $item)
        {
            if(!isset ($item->uri))
            {
                throw new Wripl_Exception('Need at least a URI to create an item!');
            }

            $this->setUri($item->uri);

            if(isset($item->relevance))
            {
                $this->setRelevance($item->relevance);
            }

        }

        if (isset($item->link_collection) && is_array($item->link_collection) && !empty($item->link_collection))
        {
            foreach ($item->link_collection as $item)
            {
                $this->addLink(new Wripl_Link_Item($item));
            }
        }
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getRelevance()
    {
        if(isset ($this->relevance))
        {
            return $this->relevance;
        }
    }

    public function setRelevance($value)
    {
        $this->relevance = $value;
        return $this;
    }

}