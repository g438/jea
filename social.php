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
//JPlugin::loadLanguage('plg_jea_fields', JPATH_ADMINISTRATOR);

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
        // load plugin parameters
        $plugin = JPluginHelper::getPlugin('jea', 'social');
        $this->_params = new JRegistry($params['params']);
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
            $like = '';  $twitter = '';  $gplus = '';  $digg = ''; $linkedin = '';

            // build the property url
            $uri = JFactory::getURI();
            $row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;
            $url = $uri->toString(array('scheme','host', 'port')).JRoute::_('index.php?option=com_jea&view=properties&id='.$row->slug);

            $url2 = urlencode($url);
            // facebook
            if ($this->_params->get('like')) {
                $like = '<div id="fb-root"></div>';
                $document = JFactory::getDocument();
                // Add Javascript
                $document->addScriptDeclaration('
                (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
                fjs.parentNode.insertBefore(js, fjs);
                }(document, \'script\', \'facebook-jssdk\'));
                ');
                
                $like = '<div data-href='.$url.' class="fb-like jeasocial_button jeasocial_facebook" style="float:'.$this->_params->get('float').'; width:'.$this->_params->get('like_width').'px;" data-width="'.$this->_params->get('like_width').'" data-layout="'.$this->_params->get('like_style').'" data-action="'.$this->_params->get('like_verb').'" data-show-faces="false" data-share="'.$this->_params->get('include_share').'" data-colorscheme="'.$this->_params->get('like_color_scheme').'">';
                $like .= '</div>';
            }
            // twitter
            if ($this->_params->get('twitter_active')) {
                // assign div with based on button style
                if ( $this->_params->get('twitter_style') == 'horizontal') {
                    $twitter_div_width = 110;
                } else {
                    $twitter_div_width = 70;
                }
                $twitter = '<div class="jeasocial_button jeasocial_twitter" style="float:'.$this->_params->get('float').'; width:'.$twitter_div_width.'px;">';
                    $twitter .= '<a href="http://twitter.com/share?url='.$url2.'" class="twitter-share-button" data-text="'.$row->title.':" data-count="'.$this->_params->get('twitter_style').'" data-via="'.$this->_params->get('twitter_account').'" data-related="'.$this->_params->get('twitter_account2').'">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
                $twitter .= '</div>';
            }
            // google+
            if ( $this->_params->get('gplus_active') ) {
                // include js callback in head section
                $document = JFactory::getDocument();
                $document->addScript('http://apis.google.com/js/plusone.js');
                // beta: automatic gplus language
                $lang = JFactory::getLanguage();
                if ( $langTag = explode('-',$lang->getTag()) ) {
                    $langCode = 'window.___gcfg = {lang: "'.$langTag[0].'"};';
                } else {
                    $langCode = null;
                }
                $gplus = '<div class="jeasocial_button jeasocial_gplus" style="float:'.$this->_params->get('float').'; width:'.$this->_params->get('gplus_width').'px; ">';
                $gplus .= '<div id="plusone-div"></div>';
                $gplus .= '<script type="text/javascript">
                			'.$langCode.'
                            gapi.plusone.render("plusone-div",
                              {
                               "size": "'.$this->_params->get('gplus_style','medium').'",
                               "annotation": "'.$this->_params->get('gplus_annotation','bubble').'",
                               "expandTo": "'.$this->_params->get('gplus_expandto','bottom').'",
                               "width": "'.$this->_params->get('gplus_width','90').'",
                               "href": "'.$url2.'"
                            });
                        </script>';
                $gplus .= '</div>';

            }
            // digg
            if ($this->_params->get('digg_active')) {
                $digg = '<div class="jeasocial_button jeasocial_digg" style="float:'.$this->_params->get('float').'; width:90px;">';
                    $digg .= '<script type="text/javascript">
                                    (function() {
                                    var s = document.createElement("SCRIPT"), s1 = document.getElementsByTagName("SCRIPT")[0];
                                    s.type = "text/javascript";
                                    s.async = true;
                                    s.src = "http://widgets.digg.com/buttons.js";
                                    s1.parentNode.insertBefore(s, s1);
                                    })();
                                </script>';
                    $digg .= '<a class="DiggThisButton '.$this->_params->get('digg_style','DiggCompact').'" href="http://digg.com/submit?url='.$url2.'&amp;title='.$row->title.'"></a>';
                $digg .= '</div>';
            }
            // linkedin
            if ($this->_params->get('linkedin_active')) {
                if ( $this->_params->get('linkedin_style') == 'right') {
                    $linkedin_div_width = 110;
                } else {
                    $linkedin_div_width = 75;
                }
                $linkedin = '<div class="jeasocial_button jeasocial_linkedin" style="float:'.$this->_params->get('float').'; width: '.$linkedin_div_width.'px;">';
                $linkedin .= '<script src="http://platform.linkedin.com/in.js" type="text/javascript"></script>
										<script type="IN/Share" data-url="'.$url2.'" data-counter="'.$this->_params->get('linkedin_style').'"></script>
                						';
                $linkedin .= '</div>';
            }
            // pinterest
            if ($this->_params->get('pint_active')) {
                // assign div with based on button style
                if ( $this->_params->get('pint_style') == 'horizontal') {
                    $pint_div_width = 110;
                } else {
                    $pint_div_width = 70;
                }
                $pinterest = '<div class="jeasocial_button jeasocial_linkedin" style="float:'.$this->_params->get('float').'; width: '.$linkedin_div_width.'px;">';
                    $pinterest .= '<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>';
                    $pinterest .= '<a href="http://pinterest.com/pin/create/button/?url='.$url2.'" class="pin-it-button" count-layout="'.$this->_params->get('pint_style').'"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>';
                $pinterest .= '</div>';
                
            }
            // complete content
            $bar = $twitter . $like . $gplus . $digg . $linkedin . $pinterest;
            // format content
            $bar = '<div class="jeasocial">'.$bar.'<div style="clear:both;"></div></div>';
            echo $bar;
        }
    }
}
