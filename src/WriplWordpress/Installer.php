<?php

class WriplWordpress_Installer
{
    public static function registerHooks()
    {
        $plugin = WriplWordpress_Plugin::$instance;
        $pathToPluginFile = $plugin->getPathToPluginFile();

        register_activation_hook($pathToPluginFile, array(__CLASS__, 'install'));
        register_deactivation_hook($pathToPluginFile, array(__CLASS__, 'uninstall'));
    }

    public static function install()
    {
        WriplWordpress_Table_IndexQueue::createTable();
    }

    public static function uninstall()
    {
        delete_option('wripl_settings');
        delete_option('wripl_feature_settings');

        $queuedItems = WriplWordpress_Table_IndexQueue::getAllQueuedItems();

        foreach ($queuedItems as $queuedItem) {
            wp_clear_scheduled_hook('wripl_index_content', array($queuedItem->id, $queuedItem->type));
        }

        WriplWordpress_Table_IndexQueue::dropTable();
    }
} 