<?php

require_once 'Wripl/Link/Collection.php';
require_once 'Wripl/Link/Item.php';
require_once 'Wripl/Link/Container.php';

/**
 * Description of AdaptableListDrupalMenuHelper
 *
 * @author brian
 */
class DrupalWriplMenuHelper
{

    /**
     * Recursive function for populating a Wripl_Link_Collection from a Drupal menu
     *
     * @param array $drupalMenu
     * @param Wripl_Link_Collection_Container $parentContainer
     * @return Wripl_Link_Collection
     */
    public static function buildWriplLinkCollection(array $drupalMenu, Wripl_Link_Container $parentContainer = null)
    {
        /**
         * On first pass create the list.
         */
        if (null === $parentContainer)
        {
            $parentContainer = new Wripl_Link_Collection();
        }

        foreach ($drupalMenu as $drupalMenuItem)
        {
            $linkItem = new Wripl_Link_Item();
            $linkItem->setUri($drupalMenuItem['link']['link_path']);

            /**
             * Recursively call the method to convert the leaf nodes.
             */
            if (!empty($drupalMenuItem['below']))
            {
                self::buildWriplLinkCollection($drupalMenuItem['below'], $linkItem);
            }

            $parentContainer->addLink($linkItem);
        }

        return $parentContainer;
    }

    public static function getAdaptedDrupalMenu(array &$drupalMenu, Wripl_Link_Collection $adaptedList)
    {
        foreach ($drupalMenu as &$drupalMenuItem)
        {
            $listItem = $adaptedList->findItem($drupalMenuItem['link']['link_path']);

            if ($listItem instanceof Wripl_Link_Item)
            {

                /**
                 * If there is a relivancy, then add a new class attribute in.
                 */
                if (is_int($listItem->getRelevance()))
                {
                    $drupalMenuItem['link']['localized_options']['attributes']['class'][] = 'relivancy-' . $listItem->getRelevance();
                }
            }

            if (!empty($drupalMenuItem['below']))
            {
                self::getAdaptedDrupalMenu($drupalMenuItem['below'], $adaptedList);
            }
        }
        return $drupalMenu;
    }

}