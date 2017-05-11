<?php
/**
 * @version     2.8.x
 * @package     K2
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_BASE') or die ;

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR.'/components/com_finder/helpers/indexer/adapter.php';

class plgFinderK2 extends FinderIndexerAdapter
{

    protected $context = 'K2';

    protected $extension = 'com_k2';

    protected $layout = 'item';

    protected $type_title = 'K2 Item';

    protected $table = '#__k2_items';

    protected $state_field = 'published';

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        if (PHP_SAPI === 'cli')
        {
          JPluginHelper::importPlugin('system', 'k2');
          JEventDispatcher::getInstance()->trigger('onAfterInitialise');
        }
        $this->loadLanguage();
    }

    public function onFinderAfterDelete($context, $table)
    {
        if ($context == 'com_k2.item')
        {
            $id = $table->id;
        }
        elseif ($context == 'com_finder.index')
        {
            $id = $table->link_id;
        }
        else
        {
            return true;
        }
        // Remove the items.
        return $this->remove($id);
    }

    public function onFinderAfterSave($context, $row, $isNew)
    {
        // We only want to handle articles here
        if ($context == 'com_k2.item')
        {
            // Check if the access levels are different
            if (!$isNew && $this->old_access != $row->access)
            {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $this->reindex($row->id);
        }

        // Check for access changes in the category
        if ($context == 'com_k2.category')
        {
            // Update the state
            $this->categoryStateChange(array($row->id), $row->published);

            // Check if the access levels are different
            if (!$isNew && $this->old_cataccess != $row->access)
            {
                $this->categoryAccessChange($row);
            }
        }

        return true;
    }

    public function onFinderBeforeSave($context, $row, $isNew)
    {
        // We only want to handle articles here
        if ($context == 'com_k2.item')
        {
            // Query the database for the old access level if the item isn't new
            if (!$isNew)
            {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category
        if ($context == 'com_k2.category')
        {
            // Query the database for the old access level if the item isn't new
            if (!$isNew)
            {
                $this->checkCategoryAccess($row);
            }
        }

        return true;
    }

    public function onFinderChangeState($context, $pks, $value)
    {
        // Items
        if ($context == 'com_k2.item')
        {
            $this->itemStateChange($pks, $value);
        }
        // Categories
        if ($context == 'com_k2.category')
        {
            $this->categoryStateChange($pks, $value);
        }

    }

    protected function index(FinderIndexerResult $item, $format = 'html')
    {
        // Check if the extension is enabled
        if (JComponentHelper::isEnabled($this->extension) == false)
        {
            return;
        }

        // Initialize the item parameters.
        $registry = new JRegistry;
        $registry->loadString($item->params);
        $item->params = JComponentHelper::getParams('com_k2', true);
        $item->params->merge($registry);

        $registry = new JRegistry;
        $registry->loadString($item->metadata);
        $item->metadata = $registry;

        // Trigger the onContentPrepare event.
        $item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);
        $item->body = FinderIndexerHelper::prepareContent($item->body, $item->params);

        // Build the necessary route and path information.
        $item->url = $this->getURL($item->id, $this->extension, $this->layout);
        $item->route = K2HelperRoute::getItemRoute($item->slug, $item->catslug);
        $item->path = FinderIndexerHelper::getContentPath($item->route);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true))
        {
            $item->title = $title;
        }

        // Add the meta-author.
        $item->metaauthor = $item->metadata->get('author');

        // Add the meta-data processing instructions.
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'extra_fields_search');

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Translate the trash state. Articles should only be accesible if the category is accessible.
        if ($item->trash || $item->cat_trash)
        {
            $item->state = 0;
        }

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'K2 Item');

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias))
        {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
        }

        // Add the category taxonomy data.
        $item->addTaxonomy('K2 Category', $item->category, $item->cat_state, $item->cat_access);

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

        // Add the extra_fields data.
        $item->addTaxonomy('Extra fields', $item->extra_fields);

        // Get content extras.
        FinderIndexerHelper::getContentExtras($item);

        // Index the item.
        if (method_exists('FinderIndexer', 'getInstance'))
        {
            FinderIndexer::getInstance()->index($item);
        }
        else
        {
            FinderIndexer::index($item);
        }
    }

    protected function setup()
    {
        // Load dependent classes.
        include_once JPATH_SITE.'/components/com_k2/helpers/route.php';

        return true;
    }

    protected function getListQuery($sql = null)
    {
        $db = JFactory::getDbo();
        // Check if we can use the supplied SQL query.
        $sql = is_a($sql, 'JDatabaseQuery') ? $sql : $db->getQuery(true);
        $sql->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body');
        $sql->select('a.published as state, a.catid, a.created AS start_date, a.created_by');
        $sql->select('a.created_by_alias, a.modified, a.modified_by, a.params');
        $sql->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.ordering');
        $sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
        $sql->select('a.trash, c.trash AS cat_trash');
        $sql->select('c.name AS category, c.published AS cat_state, c.access AS cat_access');
        $sql->select('a.extra_fields_search, a.extra_fields');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $sql->charLength('a.alias');
        $case_when_item_alias .= ' THEN ';
        $a_id = $sql->castAsChar('a.id');
        $case_when_item_alias .= $sql->concatenate(array($a_id, 'a.alias'), ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id.' END as slug';
        $sql->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $sql->charLength('c.alias');
        $case_when_category_alias .= ' THEN ';
        $c_id = $sql->castAsChar('c.id');
        $case_when_category_alias .= $sql->concatenate(array($c_id, 'c.alias'), ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id.' END as catslug';
        $sql->select($case_when_category_alias);

        $sql->select('u.name AS author');
        $sql->from('#__k2_items AS a');
        $sql->join('LEFT', '#__k2_categories AS c ON c.id = a.catid');
        $sql->join('LEFT', '#__users AS u ON u.id = a.created_by');

        return $sql;
    }

    protected function checkCategoryAccess($row)
    {
        $query = $this->db->getQuery(true);
        $query->select($this->db->quoteName('access'));
        $query->from($this->db->quoteName('#__k2_categories'));
        $query->where($this->db->quoteName('id').' = '.(int)$row->id);
        $this->db->setQuery($query);

        // Store the access level to determine if it changes
        $this->old_cataccess = $this->db->loadResult();
    }

    protected function categoryAccessChange($row)
    {
        $sql = clone($this->getStateQuery());
        $sql->where('c.id = '.(int)$row->id);

        // Get the access level.
        $this->db->setQuery($sql);
        $items = $this->db->loadObjectList();

        // Adjust the access level for each item within the category.
        foreach ($items as $item)
        {
            // Set the access level.
            $temp = max($item->access, $row->access);

            // Update the item.
            $this->change((int)$item->id, 'access', $temp);

            // Reindex the item
            $this->reindex($item->id);
        }
    }

}
