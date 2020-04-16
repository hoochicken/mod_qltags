<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_qltags
 *
 * @since  3.1
 */
abstract class ModQltagsHelper
{

    /** @var jDatabase */
    private static $objDb;

    /**
     * Get list of popular tags
     *
     * @param \Joomla\Registry\Registry  &$params module parameters
     *
     * @return  mixed
     *
     * @throws Exception
     * @since   3.1
     */
    public static function getList(&$params)
    {

        self::$objDb = JFactory::getDbo();
        $objUser = JFactory::getUser();
        $groups = implode(',', $objUser->getAuthorisedViewLevels());
        $timeframe = $params->get('timeframe', 'alltime');
        $maximum = $params->get('maximum', 5);
        $order_value = $params->get('order_value', 'title');
        $nowDate = JFactory::getDate()->toSql();
        $nullDate = self::$objDb->quote(self::$objDb->getNullDate());

        $query = self::buildQuery($params, $groups, $timeframe, $nowDate, $nullDate, $order_value, $maximum);

        self::$objDb->setQuery($query, 0, $maximum);
        try {
            $results = self::$objDb->loadObjectList();
        } catch (RuntimeException $e) {
            $results = array();
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $results = self::enrichTags($results);


        return $results;
    }

    /**
     * @param $arrTagsPre
     * @return mixed
     */
    public static function enrichTags($arrTagsPre)
    {
        $arrIds = array_column($arrTagsPre, 'tag_id');
        $arrTags = self::getTags($arrIds);
        $arrTagsMapped = self::arrayMap($arrTags, 'id');
        foreach ($arrTagsPre as $numKey => $item) {
            $numTagId = isset($item->tag_id) ? $item->tag_id : 0;
            $arrTagsPre[$numKey]->link = JRoute::_(TagsHelperRoute::getTagRoute($numTagId . ':' . $item->alias));

            if (!isset($arrTagsMapped[$numTagId])) {
                continue;
            }
            foreach ($arrTagsMapped[$numTagId] as $strKeyMerge => $valueMerge) {
                $arrTagsPre[$numKey]->$strKeyMerge = $valueMerge;
            }
        }
        return $arrTagsPre;
    }

    /**
     * @param $array
     * @param $mappingKey
     * @return array
     */
    public static function arrayMap($array, $mappingKey)
    {
        $arrReturn = [];
        foreach ($array as $value) {
            $keyNew = isset($value->$mappingKey) ? $value->$mappingKey : 0;
            $arrReturn[$keyNew] = $value;
        }
        return $arrReturn;
    }

    /**
     * @param $arrIds
     * @return
     */
    public static function getTags($arrIds)
    {
        self::$objDb;
        $objQuery = self::$objDb->getQuery(true);
        $objQuery->select('*');
        $objQuery->from(self::$objDb->quoteName('#__tags'));
        foreach ($arrIds as $numKey => $numId) {
            $arrIds[$numKey] = self::$objDb->escape($numId);
        }
        $objQuery->where('id IN(' . implode(',', $arrIds) . ')');
        self::$objDb->setQuery($objQuery);
        $arrResult = self::$objDb->loadObjectList();
        return $arrResult;
    }

    /**
     * @param $params
     * @param $groups
     * @param $timeframe
     * @param $nowDate
     * @param $nullDate
     * @param $order_value
     * @param $maximum
     * @return mixed
     */
    private static function buildQuery($params, $groups, $timeframe, $nowDate, $nullDate, $order_value, $maximum)
    {
        $query = self::$objDb->getQuery(true)
            ->select(
                array(
                    'MAX(' . self::$objDb->quoteName('tag_id') . ') AS tag_id',
                    ' COUNT(*) AS count', 'MAX(t.title) AS title',
                    'MAX(' . self::$objDb->quoteName('t.access') . ') AS access',
                    'MAX(' . self::$objDb->quoteName('t.alias') . ') AS alias',
                    'MAX(' . self::$objDb->quoteName('t.params') . ') AS params',
                )
            )
            ->group(self::$objDb->quoteName(array('tag_id', 'title', 'access', 'alias')))
            ->from(self::$objDb->quoteName('#__contentitem_tag_map', 'm'))
            ->where(self::$objDb->quoteName('t.access') . ' IN (' . $groups . ')');

        // Only return published tags
        $query->where(self::$objDb->quoteName('t.published') . ' = 1 ');

        // Filter by Parent Tag
        $parentTags = $params->get('parentTag', array());

        if ($parentTags) {
            $query->where(self::$objDb->quoteName('t.parent_id') . ' IN (' . implode(',', $parentTags) . ')');
        }

        // Optionally filter on language
        $language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

        if ($language !== 'all') {
            if ($language === 'current_language') {
                $language = JHelperContent::getCurrentLanguage();
            }

            $query->where(self::$objDb->quoteName('t.language') . ' IN (' . self::$objDb->quote($language) . ', ' . self::$objDb->quote('*') . ')');
        }

        if ($timeframe !== 'alltime') {
            $query->where(self::$objDb->quoteName('tag_date') . ' > ' . $query->dateAdd(self::$objDb->quote($nowDate), '-1', strtoupper($timeframe)));
        }

        $query->join('INNER', self::$objDb->quoteName('#__tags', 't') . ' ON ' . self::$objDb->quoteName('tag_id') . ' = t.id')
            ->join('INNER', self::$objDb->qn('#__ucm_content', 'c') . ' ON ' . self::$objDb->qn('m.core_content_id') . ' = ' . self::$objDb->qn('c.core_content_id'));

        $query->where(self::$objDb->quoteName('m.type_alias') . ' = ' . self::$objDb->quoteName('c.core_type_alias'));

        // Only return tags connected to published and authorised items
        $query->where(self::$objDb->quoteName('c.core_state') . ' = 1')
            ->where('(' . self::$objDb->quoteName('c.core_access') . ' IN (' . $groups . ') OR ' . self::$objDb->quoteName('c.core_access') . ' = 0)')
            ->where('(' . self::$objDb->quoteName('c.core_publish_up') . ' = ' . $nullDate
                . ' OR ' . self::$objDb->quoteName('c.core_publish_up') . ' <= ' . self::$objDb->quote($nowDate) . ')')
            ->where('(' . self::$objDb->quoteName('c.core_publish_down') . ' = ' . $nullDate
                . ' OR  ' . self::$objDb->quoteName('c.core_publish_down') . ' >= ' . self::$objDb->quote($nowDate) . ')');

        // Set query depending on order_value param
        if ('rand' === $order_value) {
            $query->order($query->Rand());
        } else {
            $order_value = self::$objDb->quoteName($order_value);
            $order_direction = $params->get('order_direction', 1) ? 'DESC' : 'ASC';

            if ($params->get('order_value', 'title') === 'title') {
                $query->setLimit($maximum);
                $query->order('count DESC');
                $equery = self::$objDb->getQuery(true)
                    ->select(
                        array(
                            'a.tag_id',
                            'a.count',
                            'a.title',
                            'a.access',
                            'a.alias',
                        )
                    )
                    ->from('(' . (string)$query . ') AS a')
                    ->order('a.title' . ' ' . $order_direction);

                $query = $equery;
            } else {
                $query->order($order_value . ' ' . $order_direction);
            }
        }
        // die($query);
        return $query;
    }

}
