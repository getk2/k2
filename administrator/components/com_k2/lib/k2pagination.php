<?php
/**
 * @version    2.x (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.html.pagination');

/**
 * K2 Pagination Class
 * Extends JPagination to provide custom limit box options
 */
class K2Pagination extends JPagination
{
    /**
     * Create and return the pagination page list string, with custom K2 limit values.
     *
     * @return  string  Pagination page list string.
     */
    public function getLimitBox()
    {
        $app = JFactory::getApplication();

        $limits = [];

        $limits[] = JHtml::_('select.option', '20', '20');
        $limits[] = JHtml::_('select.option', '50', '50');
        $limits[] = JHtml::_('select.option', '100', '100');
        $limits[] = JHtml::_('select.option', '200', '200');
        $limits[] = JHtml::_('select.option', '300', '300');
        $limits[] = JHtml::_('select.option', '400', '400');
        $limits[] = JHtml::_('select.option', '500', '500');
        $limits[] = JHtml::_('select.option', '1000', '1000');

        // Expose option "ALL" to site administrators only.
        // This is to prevent browser crashes for less experienced users attempting to list thousands of items in one go.
        $user    = JFactory::getUser();
        $isAdmin = (K2_JVERSION == '15') ? ($user->get('gid') >= 24) : $user->authorise('core.admin');
        if ($isAdmin) {
            $limits[] = JHtml::_('select.option', '0', JText::_('JALL'));
        }

        $selected = $this->viewall ? 0 : $this->limit;

        // Build the select list
        if ($app->isAdmin()) {
            $html = JHtml::_(
                'select.genericlist',
                $limits,
                $this->prefix . 'limit',
                'class="inputbox input-mini" size="1" onchange="Joomla.submitform();"',
                'value',
                'text',
                $selected
            );
        } else {
            $html = JHtml::_(
                'select.genericlist',
                $limits,
                $this->prefix . 'limit',
                'class="inputbox input-mini" size="1" onchange="this.form.submit()"',
                'value',
                'text',
                $selected
            );
        }

        return $html;
    }
}
