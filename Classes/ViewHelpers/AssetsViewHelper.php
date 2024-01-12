<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension t3s_swiper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3S\T3sSwiper\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class AssetsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('settings', 'array', 'The slider settings.', true);
        $this->registerArgument('uid', 'integer', 'Slider ID');
    }

    /**
     * Render the URI to the resource. The filename is used from child content.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): void
    {
        $uid = $arguments['uid'];
        $settings = $arguments['settings'];
        if ($settings['effectType'] !== 'slide') {
            $settings['slidesPerView'] = 1;
            $settings['slidesPerGroup'] = 1;
            $settings['spaceBetween'] = 0;
            $settings['useBreakpoints'] = 0;
        }
        $swiperIdClass = '.swiper-'.$uid;
        $js = "    // T3sSwiper (id=".$uid.") - AssetsViewHelper.php \n";
        $css = '';
        $loop = true;

        // Set global CSS variables
        /************************************************************************************/
        $css .= self::setGlobalSwiperVariables($settings, $swiperIdClass);


        // Autoplay with Progress Circle
        /************************************************************************************/
        if (!empty($settings['autoplayEnable']) && !empty($settings['autoplayProgressCircle'])) {
            $js .="    var progressCircle = document.querySelector('".$swiperIdClass." .autoplay-progress svg'); \n";
            $js .="    var progressContent = document.querySelector('".$swiperIdClass." .autoplay-progress span'); \n";
        }

        // Initialize Thumbnails Swiper
        /************************************************************************************/
        if (!empty($settings['thumbnailsEnable'])) {
            $js .= self::initThumbnailsSwiper($settings, $uid);
        }

        // Initialize Swiper
        /************************************************************************************/
        $js .= "    var swiper".$uid." = new Swiper('".$swiperIdClass."', {";

        // Initial Slide
        /************************************************************************************/
        if (!empty($settings['initialSlide'])) {
            $js .= "initialSlide:".$settings['initialSlide'].",";
        }

        // Speed
        /************************************************************************************/
        if (!empty($settings['speed']) && $settings['speed'] !== '300') {
            $js .= "speed:".$settings['speed'].",";
        }

        // Grab Cursor
        /************************************************************************************/
        if (!empty($settings['grabCursor'])) {
            $js .= "grabCursor:true,";
        }

        // Direction
        /************************************************************************************/
        if (!empty($settings['slidedirection']) && $settings['slidedirection'] === 'vertical') {
            $js .= "direction:'vertical',";
            $js .= "autoHeight:true,";
        }

        // Slide Rows
        /************************************************************************************/
        if (!empty($settings['slideRows']) && (int)$settings['slideRows'] > 1) {
            // rows > 1 is currently not compatible with loop mode (loop: true)
            $loop = false;
            $js .= "grid:{rows:".(int)$settings['slideRows'].",},";
            if (!empty($settings['spaceBetween'])) {
                $spaceAmount = (int)$settings['slideRows'] - 1;
                $spaceBetween = (int)$settings['spaceBetween'] * $spaceAmount.'px';
                $css .= $swiperIdClass." .swiper-slide {height:calc((100% - ".$spaceBetween.") / ".(int)$settings['slideRows'].' ) !important;';
            } else {
                $css .= $swiperIdClass." .swiper-slide {height:calc(100% / ".(int)$settings['slideRows'].' ) !important;';
            }
            $settings['useBreakpoints'] = 0;
        }

        // Centered Slide
        /************************************************************************************/
        if (!empty($settings['centeredSlides'])) {
            $js .= "centeredSlides:true,";
        }

        // Autoplay
        /************************************************************************************/
        if (!empty($settings['autoplayEnable'])) {
            $delay = !empty($settings['autoplayDelay']) ? $settings['autoplayDelay'] : 3000;
            $disableOnInteraction = empty($settings['autoplayDisableOnInteraction']) ? 'disableOnInteraction:false,' : '';
            $pauseOnMouseEnter = !empty($settings['autoplayPauseOnMouseEnter']) ? 'pauseOnMouseEnter:true' : '';
            $js .= "autoplay:{delay:".$delay.",".$disableOnInteraction.$pauseOnMouseEnter."},";
            if (!empty($settings['autoplayProgressCircle'])) {
                $js .= ' on: {autoplayTimeLeft(s, time, progress) {progressCircle.style.setProperty("--progress", 1 - progress);progressContent.textContent = `${Math.ceil(time / 1000)}s`;}},';
                $css .= $swiperIdClass.' .autoplay-progress{position:absolute;right:16px;bottom:16px;z-index:10;width:48px;height:48px;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--swiper-theme-color)}'. $swiperIdClass.' .autoplay-progress svg{--progress:0;position:absolute;left:0;top:0;z-index:10;width:100%;height:100%;stroke-width:4px;stroke:var(--swiper-theme-color);fill:none;stroke-dashoffset:calc(125.6 * (1 - var(--progress)));stroke-dasharray:125.6;transform:rotate(-90deg)}';
            }
        }

        // Keyboard
        /************************************************************************************/
        if (!empty($settings['keyboardEnable']) && (int)$settings['slideRows'] < 2) {
            $js .= "keyboard:{enabled: true,},";
        }

        // Effects
        /************************************************************************************/
        if (!empty($settings['effectType'])) {
            $crossFade = '';
            // Fade
            if ($settings['effectType'] === 'fade') {
                # flexform option is missing
                $crossFade = 'fadeEffect: {crossFade: true},';
                # Note that crossFade should be set to true in order to avoid seeing content behind or underneath.
            }
            // Slide - default
            if ($settings['effectType'] !== 'slide') {
                $js .= "effect:'".$settings['effectType']."',".$crossFade;
            }
            // Flip
            if ($settings['effectType'] == 'flip') {
                $js .= "flipEffect:{slideShadows:false},";
            }
            // Creative
            if ($settings['effectType'] == 'creative') {
                $js .= self::getCreativeEffect($settings, $swiperIdClass)['js'];
                $css .= self::getCreativeEffect($settings, $swiperIdClass)['css'];
            }
        }

        // Scrollbar
        /************************************************************************************/
        if (!empty($settings['scrollbarEnable'])) {
            $js .= "scrollbar:{el:'.swiper-scrollbar', draggable:true,},";
        }

        // Pagination
        /************************************************************************************/
        if (!empty($settings['paginationEnable'])) {
            $js .= self::getPaginationAssets($uid, $settings, $swiperIdClass)['js'];
            $css .= self::getPaginationAssets($uid, $settings, $swiperIdClass)['css'];
        }

        // Navigation
        /************************************************************************************/
        if (!empty($settings['navigationEnable'])) {
            $js .= self::getNavigationAssets($settings, $swiperIdClass)['js'];
            $css .= self::getNavigationAssets($settings, $swiperIdClass)['css'];
        }

        // Breakpoints
        /************************************************************************************/
        if (!empty($settings['useBreakpoints']) && $settings['effectType'] === 'slide') {
            $js .= self::getBreakpointsAssets($settings, $swiperIdClass)['js'];
            $css .= self::getBreakpointsAssets($settings, $swiperIdClass)['css'];
        } else {
            if ($settings['slidesPerView'] > 1) {
                $js .= "slidesPerView:". $settings['slidesPerView'].",";
            }
            if (!empty($settings['slidesPerGroup']) && $settings['slidesPerGroup'] > 1) {
                $js .= "slidesPerGroup:". $settings['slidesPerGroup'].",";
            }
        }

        // Space Between
        /************************************************************************************/
        if (!empty($settings['spaceBetween'])) {
            $js .= "spaceBetween:". $settings['spaceBetween'].",";
        }

        // Thumbnails
        /************************************************************************************/
        if (!empty($settings['thumbnailsEnable'])) {
            $js .= "thumbs:{swiper:swiperThumb".$uid.",},";
        }

        // Loop
        /************************************************************************************/
        if (!empty($loop) && !empty($settings['loop'])) {
            $js .= "loop:true,";
        }

        // Custom
        /************************************************************************************/
        if (!empty($settings['customScript'])) {
            $js .= trim($settings['customScript']);
        }

        if (!empty($settings['customCss'])) {
            //$js .= preg_replace("/\s+/", "", $settings['customCss']);
            $css .= trim($settings['customCss']);
        }

        // CLOSE Initialize Swiper
        /************************************************************************************/
        $js .= "});";

        // add assets
        /************************************************************************************/
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addInlineJavaScript('vanilla_t3s_swiper-'.$uid, $js);
        if (!empty($css)) {
            $assetCollector->addInlineStyleSheet('t3s_swiper-'.$uid, $css);
        }
    }


    /**
    * Get navigation
    */
    protected static function getNavigationAssets(array $settings, string $swiperIdClass): array
    {
        $navigationAssets = [];
        $navigationAssets['js'] = '';
        $navigationAssets['css'] = '';

        $navigationAssets['js'] .= "navigation:{nextEl:'.swiper-button-next', prevEl:'.swiper-button-prev',},";
        if (!empty($settings['navigationColor'])) {
            $navigationAssets['css'] .= $swiperIdClass.' .swiper-button-next, '.$swiperIdClass.' .swiper-button-prev {color: '.$settings['navigationColor'].'}';
        }

        return $navigationAssets;
    }


    /**
    * Get pagination
    */
    protected static function getPaginationAssets(int $uid, array $settings, string $swiperIdClass): array
    {
        $paginationAssets = [];
        $paginationAssets['js'] = '';
        $paginationAssets['css'] = '';
        $dynamicBullets = '';
        $clickable = '';
        $type = '';

        if ($settings['paginationType'] === 'bullets') {
            $clickable = !empty($settings['paginationClickable']) ? 'clickable:true,' : '';
            if (!empty($settings['paginationDynamicBullets'])) {
                $dynamicBullets = 'dynamicBullets:true';
            }
        } else {
            $type = 'type:\''.$settings['paginationType'].'\',';
        }
        $paginationAssets['js'] .= "pagination:{el:'.swiper-pagination',".$type.$clickable.$dynamicBullets."},";

        return $paginationAssets;
    }


    /**
    * Get Creative Effect
    */
    protected static function getCreativeEffect(array $settings, string $swiperIdClass): array
    {
        $creativAssets = [];
        $creativAssets['js'] = '';
        $creativAssets['css'] = '';

        if (!empty($settings['creativePresets']) && $settings['creativePresets'] === '1') {
            $creativAssets['js'] .= "creativeEffect: {prev: {shadow: true,translate: [0, 0, -400],},next: {translate: ['100%', 0, 0],},},";
        }
        if (!empty($settings['creativePresets']) && $settings['creativePresets'] === '2') {
            $creativAssets['js'] .= "creativeEffect: {prev: {shadow: true,translate: ['-120%', 0, -500],},next: {shadow: true,translate: ['120%', 0, -500],},},";
        }
        if (!empty($settings['creativePresets']) && $settings['creativePresets'] === '3') {
            $creativAssets['js'] .= "creativeEffect: {prev: {shadow: true,translate: ['-20%', 0, -1],},next: {translate: ['100%', 0, 0],},},";
        }
        if (!empty($settings['creativePresets']) && $settings['creativePresets'] === '4') {
            $creativAssets['js'] .= "creativeEffect: {prev: {shadow: true,translate: [0, 0, -800],rotate: [180, 0, 0],},next: {shadow: true,translate: [0, 0, -800],rotate: [-180, 0, 0],},},";
        }
        if (!empty($settings['creativePresets']) && $settings['creativePresets'] === '5') {
            $creativAssets['js'] .= "creativeEffect: {prev: {shadow: true,translate: ['-125%', 0, -800],rotate: [0, 0, -90],},next: {shadow: true,translate: ['125%', 0, -800],rotate: [0, 0, 90],},},";
        }
        if (!empty($settings['creativePresets']) && $settings['creativePresets'] === '6') {
            $creativAssets['js'] .= "creativeEffect: {prev: {shadow: true,origin: 'left center',translate: ['-5%', 0, -200],rotate: [0, 100, 0],},next: {origin: 'right center',translate: ['5%', 0, -200],rotate: [0, -100, 0],},},";
        }

        return $creativAssets;
    }


    /**
    * Get breakpoints
    */
    protected static function getBreakpointsAssets(array $settings, string $swiperIdClass): array
    {
        $breakpointsAssets = [];
        $breakpointsAssets['js'] = '';
        $breakpointsAssets['css'] = '';
        $breakpoints = [];
        $keyArr = [];

        foreach ($settings as $key=>$setting) {
            if (str_starts_with($key, 'bp_')) {
                $breakpoints[substr($key, 3)] = $setting;
            }
        }

        $breakpointsAssets['js'] .= 'breakpoints: {';
        $i = 1;
        foreach ($breakpoints as $key=>$breakpoint) {
            $keyArr = explode('_', $key);
            if ($i === 3) {
                $i = 1;
            }
            $slidesPerView = $settings['bp_'.$keyArr[0].'_slidesPerView'];
            $slidesPerGroup = $settings['bp_'.$keyArr[0].'_slidesPerGroup'];
            if ($i === 1) {
                $breakpointsAssets['js'] .= $keyArr[0].":{";
                $breakpointsAssets['js'] .= "slidesPerView:".$slidesPerView.", slidesPerGroup:".$slidesPerGroup.",";
            }
            if ($i === 2) {
                $breakpointsAssets['js'] .= "},";
            }
            $i++;
        }
        $breakpointsAssets['js'] .= '},';

        return $breakpointsAssets;
    }


    /**
     * generate CSS
     */
    protected static function setGlobalSwiperVariables(array $settings, string $swiperIdClass): string
    {
        $css = '';
        $themeColor = '';

        if (!empty($settings['themeColor'])) {
            $themeColor .= '--swiper-theme-color: '.$settings['themeColor'].';';
            $themeColorOpacity = !empty($settings['themeColorOpacity']) ? $settings['themeColorOpacity'] : '0.4';
            $themeColor .= '--swiper-pagination-progressbar-bg-color: rgba('.self::hex2RGB($settings['themeColor']).','.$themeColorOpacity.');';
            $themeColor .= '--swiper-pagination-bullet-color:'.$settings['themeColor'].';';
            $themeColor .= '--swiper-pagination-bullet-inactive-color:'.$settings['themeColor'].';';
            $themeColor .= '--swiper-pagination-bullet-inactive-opacity:'.$themeColorOpacity.';';
            $themeColor .= '--swiper-pagination-fraction-color:'.$settings['themeColor'].';';
            $themeColor .= '--swiper-scrollbar-drag-bg-color: rgba('.self::hex2RGB($settings['themeColor']).','.$themeColorOpacity.');';
        }

        $css .= ':root {'.$themeColor.'}';
        if (!empty($settings["disableCaption"])) {
            $mediaQuery = $settings["disableCaption"];
            $css .= '@media (max-width: '.$mediaQuery.'px){'.$swiperIdClass.' .swiper-slide-content{display:none !important}}';
        }

        return $css;
    }


    /**
     * initialize ThumbnailsSwiper
     */
    protected static function initThumbnailsSwiper(array $settings, int $uid): string
    {
        $js = '';

        $thumbloop = '';
        if (!empty($settings['loop'])) {
            $thumbloop = 'loop:true,';
        }
        $js .= "    var swiperThumb".$uid." = new Swiper('.swiper-thumb-".$uid."', {";

        $spaceBetween = '';
        if (!empty($settings['thumbnailsSpaceBetween'])) {
            $spaceBetween = 'spaceBetween:'.(int)$settings['thumbnailsSpaceBetween'].',';
        }
        $slidesPerView = '';
        if (!empty($settings['thumbnailsSlidesPerView'])) {
            $slidesPerView = 'slidesPerView:'. (int)$settings['thumbnailsSlidesPerView'].',';
        }

        $js .= $thumbloop.$spaceBetween.$slidesPerView."freeMode:true,watchSlidesProgress: true,";
        $js .= "}); \n";

        return $js;
    }


    /**
    * Convert a hexa decimal color code to its RGB equivalent
    */
    protected static function hex2RGB(string $hexStr, string $seperator = ','): string
    {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
        $rgbArray = array();
        if (strlen($hexStr) == 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) {
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false;
        }

        return implode($seperator, $rgbArray);
    }
}
