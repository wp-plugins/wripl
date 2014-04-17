<?php

class WriplWordpress_Table_IndexQueue
{
    const ITEM_NEEDS_INDEXING = -1;
    const ITEM_QUEUED = 0;
    const ITEM_INDEXED = 1;

    public static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'wripl_index_queue';
    }

    public static function createTable()
    {
        $indexQueueTableName = self::getTableName();

        $createQuery = "CREATE TABLE $indexQueueTableName (
            id bigint NOT NULL,
            type tinytext NOT NULL,
            status int NOT NULL
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($createQuery);
    }

    public static function dropTable()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . self::getTableName());
    }

    public static function getAllQueuedItems()
    {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM ' . self::getTableName() . ' WHERE status = ' . self::ITEM_QUEUED);
    }


    public static function getTotalItemsInQueue()
    {
        global $wpdb;
        return $wpdb->get_var('SELECT COUNT(*) FROM ' . self::getTableName());
    }

    public static function getTotalItemsIndexed()
    {
        global $wpdb;
        return $wpdb->get_var('SELECT COUNT(*) FROM ' . self::getTableName() . ' where status = ' . self::ITEM_INDEXED);
    }

    public static function insert(array $data)
    {
        global $wpdb;
        return $wpdb->insert(self::getTableName(), $data);
    }

    public static function delete($id)
    {
        global $wpdb;
        return $wpdb->query('DELETE FROM ' .self::getTableName() . ' WHERE id = ' . $id);
    }

    public static function update($id, $data)
    {
        global $wpdb;
        return $wpdb->update(self::getTableName(), $data, array('id' => $id));
    }
} 