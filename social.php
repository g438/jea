<?php
/**
 *
 * @package        Joomla
 * @subpackage    JEA
 * @copyright    Copyright (C) 2011 Roberto Segura. All rights reserved.
 * @license        GNU/GPL, see LICENSE.txt
 * Joomla Estate Agency is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 *
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');
JPlugin::loadLanguage('plg_jea_fields', JPATH_ADMINISTRATOR);

/**
 * Plugin JEA Social
 *
 * @package        Joomla
 * @subpackage    JEA
 * @since         1.5
 */
class plgJeaSocial extends JPlugin
{
    var $_params = null;
    var $_position = 1;

    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param object $params  The object that holds the plugin parameters
     * @since 1.5
     */
    function plgJeaSocial( &$subject, $params )
    {
        parent::__construct( $subject, $params );
        global $mainframe;
        $plugin =& JPluginHelper::getPlugin('jea', 'social');
        $like = '';  $retweet = '';  $buzz = '';  $digg = '';
        $this->_params = new JParameter( $plugin->params );
        $this->_position = ($this->_params->get('position'));
    }



    /**
     * onBeforeShowDescription method
     * Called in the default_item.php tpl
     *
     * @param stdClass $row
     */
    function onBeforeShowDescription(&$row)
    {
        if($this->_position == 1 || $this->_position == 2) {
            $this->showSocialBar($row);
        }
    }


    /**
     * onAfterShowDescription method
     * Called in the default_item.php tpl
     *
     * @param stdClass $row
     */
    function onAfterShowDescription(&$row)
    {
        if($this->_position == 0 || $this->_position == 2) {
            $this->showSocialBar($row);
        }
    }


    function showSocialBar(&$row) {

        // ensure that constructor got the plugin params
        if (!is_null($this->_params) && !empty($row->id)) {
            // initialize empty vars
            $like = '';  $retweet = '';  $buzz = '';  $digg = '';

            // build the property url
            $uri =& JFactory::getURI();
            $url = $uri->toString(array('scheme','host', 'port')).JRoute::_('index.php?option=com_jea&view=properties&id='.$row->slug);

            $url2 = urlencode($url);
            // facebook             
            if ($this->_params->get('like')) {
                $like = '<div class="faceandtweet_like" style="float:'.$this->_params->get('float').'; width:'.$this->_params->get('like_width').'px; height:'.$this->_params->get('like_height').'px;"><iframe src="http://www.facebook.com/plugins/like.php?href='.$url2 .'&amp;layout='.$this->_params->get('like_style').'&amp;width='.$this->_params->get('like_width').'&amp;show_faces=false&amp;action='.$this->_params->get('like_verb').'&amp;colorscheme='.$this->_params->get('like_color_scheme').'&amp;height='.$this->_params->get('like_height').'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.$this->_params->get('like_width').'px; height:'.$this->_params->get('like_height').'px;" allowTransparency="true"></iframe></div>';                
            }
            // twitter
            if ($this->_params->get('retweet')) {
                $retweet = '<div class="faceandtweet_retweet" style="float:'.$this->_params->get('float').'; width:'.$this->_params->get('count-width').'px;"><a href="http://twitter.com/share?url='.$url2.'" class="twitter-share-button" data-text="'.$row->title.':" data-count="'.$this->_params->get('count').'" data-via="'.$this->_params->get('twitter_account').'" data-related="'.$this->_params->get('twitter_account2').'">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div>';
            }
            // buzz
            if ($this->_params->get('buzz')) {
                $buzz = '<div class="faceandtweet_retweet" style="float:'.$this->_params->get('float').'; width:110px;"><a title="'.$row->title.'" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="small-count"></a><script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script></div>';  
            }
            // digg
            if ($this->_params->get('digg')) {
                $digg = '<div class="faceandtweet_retweet" style="float:'.$this->_params->get('float').'; width:110px;">';
                $digg .= '<script type="text/javascript">
                                (function() {
                                var s = document.createElement("SCRIPT"), s1 = document.getElementsByTagName("SCRIPT")[0];
                                s.type = "text/javascript";
                                s.async = true;
                                s.src = "http://widgets.digg.com/buttons.js";
                                s1.parentNode.insertBefore(s, s1);
                                })();
                            </script>';
                $digg .= '<a class="DiggThisButton DiggCompact"
            href="http://digg.com/submit?url='.$url2.'&amp;title='.$row->title.'"></a></div>';
            }
            // complete content
            $bar = $retweet . $like . $buzz . $digg;
            // format content
            $bar = '<div class="faceandtweet">'.$bar.'<div style="clear:both;"></div></div>';
            echo $bar;
        } 
    }
}
