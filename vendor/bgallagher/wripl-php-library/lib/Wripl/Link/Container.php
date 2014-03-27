<?php

/**
 * Allows "list items" and "item lists" to add and iterate items.
 *
 * @author brian
 */
abstract class Wripl_Link_Container implements RecursiveIterator, Countable
{

    private $_index = array();

    public function current()
    {
        return current($this->_index);
    }

    public function key()
    {
        return key($this->_index);
    }

    public function next()
    {
        next($this->_index);
    }

    public function rewind()
    {
        reset($this->_index);
    }

    public function valid()
    {
        $key = key($this->_index);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    public function hasChildren()
    {
        $result = count($this->_index) > 0;
        return $result;
    }

    public function getChildren()
    {

        return $this->_index[$this->key()];
    }

    public function count()
    {
        return count($this->_index);
    }

    public function addLink(Wripl_Link_Item $item)
    {
        $hash = md5($item->getUri());

        /**
         * Don't add if item already exists.
         */
        if (!isset($this->_index[$hash]))
        {
            $this->_index[$hash] = $item;
            $this->link_collection[] = $item;
        }

        $this->_rebuild();

        return $this;
    }

    /**
     *
     * @param array $items An array of Wripl_Link_Items
     */
    public function addItems(array $items)
    {
        foreach ($items as $item)
        {
            $this->addLink($item);
        }
    }

    public function removeItem($uri)
    {

        $hash = md5($uri);

        if (isset($this->_index[$hash]))
        {
            unset($this->_index[$hash]);
            $this->_rebuild();
            return true;
        }

        return false;
    }

    public function findItem($uri)
    {
        $givenHash = md5($uri);

        $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item)
        {
            $hash = md5($item->getUri());

            if ($givenHash === $hash)
            {
                return $item;
            }
        }
        return false;
    }

    private function _rebuild()
    {
        $this->link_collection = array();

        foreach ($this->_index as $item)
        {
            if ($item->hasChildren())
            {
                $item->_rebuild();
            }

            $this->link_collection[] = $item;
        }

        return $this;
    }


    public function toJson()
    {
        if(empty($this->link_collection))
        {
            return '[]';
        }
        return json_encode($this->link_collection);
    }

}