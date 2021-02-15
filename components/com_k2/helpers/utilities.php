<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2020 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

class K2HelperUtilities
{
    // Get user avatar
    public static function getAvatar($userID, $email = null, $width = 50)
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.application.component.model');
        $app = JFactory::getApplication();
        $params = K2HelperUtilities::getParams('com_k2');
        $template = JRequest::getCmd('template');

        // Check for placeholder overrides
        if (isset($template) && JFile::exists(JPATH_SITE.'/templates/'.$template.'/images/placeholder/user.png')) {
            $avatarPath = 'templates/'.$template.'/images/placeholder/user.png';
        } elseif (JFile::exists(JPATH_SITE.'/templates/'.$app->getTemplate().'/images/placeholder/user.png')) {
            $avatarPath = 'templates/'.$app->getTemplate().'/images/placeholder/user.png';
        } else {
            $avatarPath = 'components/com_k2/images/placeholder/user.png';
        }

        // Continue with default K2 avatar determination
        if ($userID == 'alias') {
            $avatar = JURI::root(true).'/'.$avatarPath;
        } elseif ($userID == 0) {
            if ($params->get('gravatar') && !is_null($email)) {
                $avatar = 'https://secure.gravatar.com/avatar/'.md5($email).'?s='.$width.'&amp;default='.urlencode(JURI::root().$avatarPath);
            } else {
                $avatar = JURI::root(true).'/'.$avatarPath;
            }
        } elseif (is_numeric($userID) && $userID > 0) {
            K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
            $model = K2Model::getInstance('Item', 'K2Model');
            $profile = $model->getUserProfile($userID);
            $avatar = (is_null($profile)) ? '' : $profile->image;
            if (empty($avatar)) {
                if ($params->get('gravatar') && !is_null($email)) {
                    $avatar = 'https://secure.gravatar.com/avatar/'.md5($email).'?s='.$width.'&amp;default='.urlencode(JURI::root().$avatarPath);
                } else {
                    $avatar = JURI::root(true).'/'.$avatarPath;
                }
            } else {
                $avatar = JURI::root(true).'/media/k2/users/'.$avatar;
            }
        }

        if (!$params->get('userImageDefault') && $avatar == JURI::root(true).'/'.$avatarPath) {
            $avatar = '';
        }

        return $avatar;
    }

    public static function getCategoryImage($image, $params)
    {
        jimport('joomla.filesystem.file');
        $app = JFactory::getApplication();
        $categoryImage = null;
        if (!empty($image)) {
            $categoryImage = JURI::root(true).'/media/k2/categories/'.$image;
        } else {
            if ($params->get('catImageDefault')) {
                if (JFile::exists(JPATH_SITE.'/templates/'.$app->getTemplate().'/images/placeholder/category.png')) {
                    $categoryImage = JURI::root(true).'/templates/'.$app->getTemplate().'/images/placeholder/category.png';
                } else {
                    $categoryImage = JURI::root(true).'/components/com_k2/images/placeholder/category.png';
                }
            }
        }
        return $categoryImage;
    }

    // Word limit
    public static function wordLimit($str, $limit = 100, $end_char = '&#8230;')
    {
        if (JString::trim($str) == '') {
            return $str;
        }

        // always strip tags for text
        $str = strip_tags($str);

        $find = array("/\r|\n/u", "/\t/u", "/\s\s+/u");
        $replace = array(" ", " ", " ");
        $str = preg_replace($find, $replace, $str);

        preg_match('/\s*(?:\S*\s*){'.(int)$limit.'}/u', $str, $matches);
        if (JString::strlen($matches[0]) == JString::strlen($str)) {
            $end_char = '';
        }
        return JString::rtrim($matches[0]).$end_char;
    }

    // Character limit
    public static function characterLimit($str, $limit = 150, $end_char = '...')
    {
        if (JString::trim($str) == '') {
            return $str;
        }

        // always strip tags for text
        $str = strip_tags(JString::trim($str));

        $find = array("/\r|\n/u", "/\t/u", "/\s\s+/u");
        $replace = array(" ", " ", " ");
        $str = preg_replace($find, $replace, $str);

        if (JString::strlen($str) > $limit) {
            $str = JString::substr($str, 0, $limit);
            return JString::rtrim($str).$end_char;
        } else {
            return $str;
        }
    }

    // Cleanup HTML entities
    public static function cleanHtml($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    // Gender
    public static function writtenBy($gender)
    {
        if (empty($gender) || $gender == 'n') {
            return JText::_('K2_WRITTEN_BY');
        }
        if ($gender == 'm') {
            return JText::_('K2_WRITTEN_BY_MALE');
        }
        if ($gender == 'f') {
            return JText::_('K2_WRITTEN_BY_FEMALE');
        }
    }

    public static function setDefaultImage(&$item, $view, $params = null)
    {
        if ($view == 'item') {
            $image = 'image'.$item->params->get('itemImgSize');
            $item->image = $item->$image;
            switch ($item->params->get('itemImgSize')) {
                case 'XSmall':
                    $item->imageWidth = $item->params->get('itemImageXS');
                    break;
                case 'Small':
                    $item->imageWidth = $item->params->get('itemImageS');
                    break;
                case 'Medium':
                    $item->imageWidth = $item->params->get('itemImageM');
                    break;
                case 'Large':
                    $item->imageWidth = $item->params->get('itemImageL');
                    break;
                case 'XLarge':
                    $item->imageWidth = $item->params->get('itemImageXL');
                    break;
            }
        }

        if ($view == 'itemlist') {
            $image = 'image'.$params->get($item->itemGroup.'ImgSize');
            $item->image = isset($item->$image) ? $item->$image : '';
            switch ($params->get($item->itemGroup.'ImgSize')) {
                case 'XSmall':
                    $item->imageWidth = $item->params->get('itemImageXS');
                    break;
                case 'Small':
                    $item->imageWidth = $item->params->get('itemImageS');
                    break;
                case 'Medium':
                    $item->imageWidth = $item->params->get('itemImageM');
                    break;
                case 'Large':
                    $item->imageWidth = $item->params->get('itemImageL');
                    break;
                case 'XLarge':
                    $item->imageWidth = $item->params->get('itemImageXL');
                    break;
            }
        }

        if ($view == 'latest') {
            $image = 'image'.$params->get('latestItemImageSize');
            $item->image = $item->$image;
            switch ($params->get('latestItemImageSize')) {
                case 'XSmall':
                    $item->imageWidth = $item->params->get('itemImageXS');
                    break;
                case 'Small':
                    $item->imageWidth = $item->params->get('itemImageS');
                    break;
                case 'Medium':
                    $item->imageWidth = $item->params->get('itemImageM');
                    break;
                case 'Large':
                    $item->imageWidth = $item->params->get('itemImageL');
                    break;
                case 'XLarge':
                    $item->imageWidth = $item->params->get('itemImageXL');
                    break;
            }
        }

        if ($view == 'relatedByTag' && $params->get('itemRelatedImageSize')) {
            $image = 'image'.$params->get('itemRelatedImageSize');
            $item->image = $item->$image;
            switch ($params->get('itemRelatedImageSize')) {
                case 'XSmall':
                    $item->imageWidth = $item->params->get('itemImageXS');
                    break;
                case 'Small':
                    $item->imageWidth = $item->params->get('itemImageS');
                    break;
                case 'Medium':
                    $item->imageWidth = $item->params->get('itemImageM');
                    break;
                case 'Large':
                    $item->imageWidth = $item->params->get('itemImageL');
                    break;
                case 'XLarge':
                    $item->imageWidth = $item->params->get('itemImageXL');
                    break;
            }
        }
    }

    public static function getParams($option)
    {
        if (K2_JVERSION != '15') {
            $app = JFactory::getApplication();
            if ($app->isSite()) {
                $params = $app->getParams($option);
            } else {
                $params = JComponentHelper::getParams($option);
            }
        } else {
            $params = JComponentHelper::getParams($option);
        }
        return $params;
    }

    public static function cleanTags($string, $allowed_tags)
    {
        $allowed_htmltags = array();
        foreach ($allowed_tags as $tag) {
            $allowed_htmltags[] .= "<".$tag.">";
        }
        $allowed_htmltags = implode("", $allowed_htmltags);
        $string = strip_tags($string, $allowed_htmltags);
        return $string;
    }

    // Clean HTML Tag Attributes
    // e.g. cleanupAttributes($string,"img,hr,h1,h2,h3,h4","style,width,height,hspace,vspace,border,class,id");
    public static function cleanAttributes($string, $tag_array, $attr_array)
    {
        $attr = implode("|", $attr_array);
        foreach ($tag_array as $tag) {
            preg_match_all("#<($tag) .+?>#", $string, $matches, PREG_PATTERN_ORDER);
            foreach ($matches[0] as $match) {
                preg_match_all('/('.$attr.')=([\\"\\\']).+?([\\"\\\'])/', $match, $matchesAttr, PREG_PATTERN_ORDER);
                foreach ($matchesAttr[0] as $attrToClean) {
                    $string = str_replace($attrToClean, '', $string);
                    $string = preg_replace('|  +|', ' ', $string);
                    $string = str_replace(' >', '>', $string);
                }
            }
        }
        return $string;
    }

    public static function verifyRecaptcha()
    {
        $params = JComponentHelper::getParams('com_k2');
        $vars = array();
        $vars['secret'] = $params->get('recaptcha_private_key');
        $vars['response'] = $_POST['g-recaptcha-response'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($vars, '', '&'));
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $response = json_decode($result);
        if ($result && $info['http_code'] == 200 && is_object($response) && isset($response->success) && $response->success == true) {
            return true;
        } else {
            return false;
        }
    }
}
