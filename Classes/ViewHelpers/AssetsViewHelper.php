<?php
declare(strict_types=1);

namespace T3S\T3sSwiper\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\ConsumableNonce;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class AssetsViewHelper extends AbstractViewHelper
{

    public function initializeArguments(): void
    {
        $this->registerArgument('settings', 'array', 'The slider settings.', true);
        $this->registerArgument('uid', 'integer', 'Slider ID');
    }

    public function render(): void
    {
        $uid      = (int)$this->arguments['uid'];
        $settings = $this->arguments['settings']->toArray();
        $settings = $this->normalizeSettings($settings);

        $swiperId = '.swiper-' . $uid;
        $js       = "    // T3sSwiper (id={$uid}) - AssetsViewHelper.php\n";
        $css      = '';
        $loop     = true;

        $css .= self::setGlobalSwiperVariables($settings, $swiperId);

        if (!empty($settings['autoplay']['autoplayEnable'])
            && !empty($settings['autoplay']['autoplayProgressCircle'])
        ) {
            $js .= "    var progressCircle = document.querySelector('{$swiperId} .autoplay-progress svg');\n";
            $js .= "    var progressContent = document.querySelector('{$swiperId} .autoplay-progress span');\n";
        }

        if (!empty($settings['thumbnails']['thumbnailsEnable'])) {
            $js .= self::initThumbnailsSwiper($settings, $uid);
        }

        $js .= "    var swiper{$uid} = new Swiper('{$swiperId}', {";

        $js .= $this->buildInitialSlide($settings);
        $js .= $this->buildSpeed($settings);
        $js .= $this->buildGrabCursor($settings);
        $js .= $this->buildDirection($settings);

        [$rowsJs, $rowsCss, $loop] = $this->buildSlideRows($settings, $swiperId);
        $js  .= $rowsJs;
        $css .= $rowsCss;

        $js .= $this->buildCenteredSlides($settings);

        [$autoplayJs, $autoplayCss] = $this->buildAutoplay($settings, $swiperId);
        $js  .= $autoplayJs;
        $css .= $autoplayCss;

        $js .= $this->buildKeyboard($settings);

        [$effectJs, $effectCss] = $this->buildEffects($settings, $swiperId);
        $js  .= $effectJs;
        $css .= $effectCss;

        $js .= $this->buildScrollbar($settings);

        if (!empty($settings['pagination']['paginationEnable'])) {
            $pagination = self::getPaginationAssets($uid, $settings, $swiperId);
            $js  .= $pagination['js'];
            $css .= $pagination['css'];
        }

        if (!empty($settings['navigation']['navigationEnable'])) {
            $navigation = self::getNavigationAssets($settings, $swiperId);
            $js  .= $navigation['js'];
            $css .= $navigation['css'];
        }

        if (!empty($settings['breakpoints']['useBreakpoints'])
            && $settings['effects']['effectType'] === 'slide'
        ) {
            $breakpoints = self::getBreakpointsAssets($settings, $swiperId);
            $js  .= $breakpoints['js'];
            $css .= $breakpoints['css'];
        } else {
            if ($settings['parameter']['slidesPerView'] > 1) {
                $js .= 'slidesPerView:' . $settings['parameter']['slidesPerView'] . ',';
            }
            if (!empty($settings['parameter']['slidesPerGroup'])
                && $settings['parameter']['slidesPerGroup'] > 1
            ) {
                $js .= 'slidesPerGroup:' . $settings['parameter']['slidesPerGroup'] . ',';
            }
        }

        if (!empty($settings['parameter']['spaceBetween'])) {
            $js .= 'spaceBetween:' . $settings['parameter']['spaceBetween'] . ',';
        }

        if (!empty($settings['thumbnails']['thumbnailsEnable'])) {
            $js .= "thumbs:{swiper:swiperThumb{$uid},},";
        }

        if (!empty($loop) && !empty($settings['parameter']['loop'])) {
            $js .= 'loop:true,';
        }

        if (!empty($settings['customscript']['customScript'])) {
            $js .= trim($settings['customscript']['customScript']);
        }
        if (!empty($settings['customscript']['customCss'])) {
            $css .= trim($settings['customscript']['customCss']);
        }

        $js .= '});';


        if ( $settings['effects']['slidedirection'] === 'vertical' ) {
            $ratio = str_replace(':', '/', $settings['main']['ratio']);
            $css .= '.swiper-vertical{width:100%;aspect-ratio:'.$ratio.';height:auto;max-height:none!important;overflow:hidden}.swiper-vertical .swiper-slide{height:100%!important;width:100%}.swiper-vertical .swiper-slide-image{width:100%;height:100%;max-width:100%;max-height:100%;object-fit:cover;display:block}';
        }
        
        if ( $settings['effects']['effectType'] === 'cube' || $settings['effects']['effectType'] === 'cards' ) {
            $css .= '@media (max-width: 992px){.swiper.swiper-cards{max-width:calc(100vw - 100px)!important;margin:0 auto}.swiper.swiper-cube{max-width:calc(100vw - 100px)!important;margin:0 auto}#swiper-2334,#swiper-2335{overflow-x:hidden!important}}';
        }

        $nonce = $this->resolveNonce();
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addInlineJavaScript(
            'vanilla_t3s_swiper-' . $uid, $js, ['nonce' => $nonce]
        );
        if (!empty($css)) {
            $assetCollector->addInlineStyleSheet('t3s_swiper-' . $uid, $css);
        }
    }

    // ─── Settings normalisieren ───────────────────────────────────────────────

    private function normalizeSettings(array $settings): array
    {
        if ($settings['effects']['effectType'] !== 'slide') {
            $settings['parameter']['slidesPerView']        = 1;
            $settings['parameter']['slidesPerGroup']       = 1;
            $settings['parameter']['spaceBetween']         = 0;
            $settings['breakpoints']['useBreakpoints']     = 0;
        }

        return $settings;
    }

    // ─── JS-Bausteine ─────────────────────────────────────────────────────────

    private function buildInitialSlide(array $settings): string
    {
        return !empty($settings['parameter']['initialSlide'])
            ? 'initialSlide:' . $settings['parameter']['initialSlide'] . ','
            : '';
    }

    private function buildSpeed(array $settings): string
    {
        return (!empty($settings['parameter']['speed']) && $settings['parameter']['speed'] !== '300')
            ? 'speed:' . $settings['parameter']['speed'] . ','
            : '';
    }

    private function buildGrabCursor(array $settings): string
    {
        return !empty($settings['parameter']['grabCursor']) ? 'grabCursor:true,' : '';
    }

    private function buildDirection(array $settings): string
    {
        return (!empty($settings['effects']['slidedirection'])
            && $settings['effects']['slidedirection'] === 'vertical')
            ? "direction:'vertical',"
            : '';
    }

    private function buildCenteredSlides(array $settings): string
    {
        return !empty($settings['parameter']['centeredSlides']) ? 'centeredSlides:true,' : '';
    }

    private function buildScrollbar(array $settings): string
    {
        return !empty($settings['navigation']['scrollbarEnable'])
            ? "scrollbar:{el:'.swiper-scrollbar',draggable:true,},"
            : '';
    }

    private function buildKeyboard(array $settings): string
    {
        return (!empty($settings['navigation']['keyboardEnable'])
            && (int)($settings['effects']['slideRows'] ?? 0) < 2)
            ? 'keyboard:{enabled:true,},'
            : '';
    }

    /**
     * @return array{0: string, 1: string, 2: bool}  [js, css, loop]
     */
    private function buildSlideRows(array $settings, string $swiperId): array
    {
        $rows = (int)($settings['effects']['slideRows'] ?? 0);
        if ($rows <= 1) {
            return ['', '', true];
        }

        $js   = "grid:{rows:{$rows},},";
        $space = !empty($settings['parameter']['spaceBetween'])
            ? ((int)$settings['parameter']['spaceBetween'] * ($rows - 1)) . 'px'
            : '0px';
        $css  = $space !== '0px'
            ? "{$swiperId} .swiper-slide {height:calc((100% - {$space}) / {$rows}) !important;"
            : "{$swiperId} .swiper-slide {height:calc(100% / {$rows}) !important;";

        return [$js, $css, false];
    }

    /**
     * @return array{0: string, 1: string}  [js, css]
     */
    private function buildAutoplay(array $settings, string $swiperId): array
    {
        if (empty($settings['autoplay']['autoplayEnable'])) {
            return ['', ''];
        }

        $delay                = $settings['autoplay']['autoplayDelay'] ?? 3000;
        $disableOnInteraction = empty($settings['autoplay']['autoplayDisableOnInteraction'])
            ? 'disableOnInteraction:false,' : '';
        $pauseOnMouseEnter    = !empty($settings['autoplay']['autoplayPauseOnMouseEnter'])
            ? 'pauseOnMouseEnter:true' : '';

        $js  = "autoplay:{delay:{$delay},{$disableOnInteraction}{$pauseOnMouseEnter}},";
        $css = '';

        if (!empty($settings['autoplay']['autoplayProgressCircle'])) {
            $js  .= ' on: {autoplayTimeLeft(s, time, progress) {'
                . 'progressCircle.style.setProperty("--progress", 1 - progress);'
                . 'progressContent.textContent = `${Math.ceil(time / 1000)}s`;}},';
            $css .= $swiperId . ' .autoplay-progress{position:absolute;right:16px;bottom:16px;'
                . 'z-index:10;width:48px;height:48px;display:flex;align-items:center;'
                . 'justify-content:center;font-weight:700;color:var(--swiper-theme-color)}'
                . $swiperId . ' .autoplay-progress svg{--progress:0;position:absolute;left:0;'
                . 'top:0;z-index:10;width:100%;height:100%;stroke-width:4px;'
                . 'stroke:var(--swiper-theme-color);fill:none;'
                . 'stroke-dashoffset:calc(125.6 * (1 - var(--progress)));'
                . 'stroke-dasharray:125.6;transform:rotate(-90deg)}';
        }

        return [$js, $css];
    }

    /**
     * @return array{0: string, 1: string}  [js, css]
     */
    private function buildEffects(array $settings, string $swiperId): array
    {
        if (empty($settings['effects']['effectType'])) {
            return ['', ''];
        }

        $effectType = $settings['effects']['effectType'];
        $js         = '';
        $css        = '';

        if ($effectType !== 'slide') {
            $crossFade = $effectType === 'fade' ? 'fadeEffect:{crossFade:true},' : '';
            $js .= "effect:'{$effectType}',{$crossFade}";
        }

        if ($effectType === 'flip') {
            $js .= 'flipEffect:{slideShadows:false},';
        }

        if ($effectType === 'creative') {
            $creative = self::getCreativeEffect($settings, $swiperId);
            $js      .= $creative['js'];
            $css     .= $creative['css'];
        }

        return [$js, $css];
    }

    // ─── Nonce ────────────────────────────────────────────────────────────────

    private function resolveNonce(): string
    {
        $request = $this->getRequest();
        if ($request === null) {
            return '';
        }

        $nonce = $request->getAttribute('nonce');

        return $nonce instanceof ConsumableNonce ? $nonce->consume() : '';
    }

    private function getRequest(): ?ServerRequestInterface
    {
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }

        return null;
    }

    // ─── Static Helpers ───────────────────────────────────────────────────────

    protected static function getNavigationAssets(array $settings, string $swiperId): array
    {
        $js  = "navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev',},";
        $css = !empty($settings['main']['themeColor'])
            ? "{$swiperId} .swiper-button-next, {$swiperId} .swiper-button-prev"
              . " {color:{$settings['main']['themeColor']}}"
            : '';

        return ['js' => $js, 'css' => $css];
    }

    protected static function getPaginationAssets(int $uid, array $settings, string $swiperId): array
    {
        $type          = '';
        $clickable     = '';
        $dynamicBullets = '';

        if ($settings['pagination']['paginationType'] === 'bullets') {
            $clickable      = !empty($settings['pagination']['paginationClickable']) ? 'clickable:true,' : '';
            $dynamicBullets = !empty($settings['pagination']['paginationDynamicBullets']) ? 'dynamicBullets:true' : '';
        } else {
            $type = "type:'{$settings['pagination']['paginationType']}',";
        }

        return [
            'js'  => "pagination:{el:'.swiper-pagination',{$type}{$clickable}{$dynamicBullets}},",
            'css' => '',
        ];
    }

    protected static function getCreativeEffect(array $settings, string $swiperId): array
    {
        $preset = $settings['effects']['creativePresets'] ?? '';

        $js = match ($preset) {
            '1' => "creativeEffect:{prev:{shadow:true,translate:[0,0,-400],},next:{translate:['100%',0,0],},},",
            '2' => "creativeEffect:{prev:{shadow:true,translate:['-120%',0,-500],},next:{shadow:true,translate:['120%',0,-500],},},",
            '3' => "creativeEffect:{prev:{shadow:true,translate:['-20%',0,-1],},next:{translate:['100%',0,0],},},",
            '4' => "creativeEffect:{prev:{shadow:true,translate:[0,0,-800],rotate:[180,0,0],},next:{shadow:true,translate:[0,0,-800],rotate:[-180,0,0],},},",
            '5' => "creativeEffect:{prev:{shadow:true,translate:['-125%',0,-800],rotate:[0,0,-90],},next:{shadow:true,translate:['125%',0,-800],rotate:[0,0,90],},},",
            '6' => "creativeEffect:{prev:{shadow:true,origin:'left center',translate:['-5%',0,-200],rotate:[0,100,0],},next:{origin:'right center',translate:['5%',0,-200],rotate:[0,-100,0],},},",
            default => '',
        };

        return ['js' => $js, 'css' => ''];
    }

    protected static function getBreakpointsAssets(array $settings, string $swiperId): array
    {
        $breakpoints = [];
        foreach ($settings['breakpoints'] as $key => $setting) {
            if (str_starts_with($key, 'bp_')) {
                $breakpoints[substr($key, 3)] = $setting;
            }
        }

        $js      = 'breakpoints:{';
        $current = null;

        foreach ($breakpoints as $key => $breakpoint) {
            $parts = explode('_', $key);
            $px    = $parts[0];
            $field = $parts[1] ?? '';

            if ($field === 'slidesPerView') {
                $current = $px;
                $spv     = $settings['breakpoints']['bp_' . $px . '_slidesPerView'];
                $spg     = $settings['breakpoints']['bp_' . $px . '_slidesPerGroup'];
                $js     .= "{$px}:{slidesPerView:{$spv},slidesPerGroup:{$spg},},";
            }
        }

        $js .= '},';

        return ['js' => $js, 'css' => ''];
    }

    protected static function setGlobalSwiperVariables(array $settings, string $swiperId): string
    {
        $themeColor = '';

        if (!empty($settings['main']['themeColor'])) {
            $color   = $settings['main']['themeColor'];
            $opacity = $settings['main']['themeColorOpacity'] ?? '0.4';
            $rgb     = self::hex2RGB($color);

            $themeColor  = "--swiper-theme-color:{$color};";
            $themeColor .= "--swiper-pagination-progressbar-bg-color:rgba({$rgb},{$opacity});";
            $themeColor .= "--swiper-pagination-bullet-color:{$color};";
            $themeColor .= "--swiper-pagination-bullet-inactive-color:{$color};";
            $themeColor .= "--swiper-pagination-bullet-inactive-opacity:{$opacity};";
            $themeColor .= "--swiper-pagination-fraction-color:{$color};";
            $themeColor .= "--swiper-scrollbar-drag-bg-color:rgba({$rgb},{$opacity});";
        }

        $css = ":root {{$themeColor}}";

        if (!empty($settings['caption']['disableCaption'])) {
            $mq   = $settings['caption']['disableCaption'];
            $css .= "@media (max-width:{$mq}px){{$swiperId} .swiper-slide-content{display:none !important}}";
        }

        return $css;
    }

    protected static function initThumbnailsSwiper(array $settings, int $uid): string
    {
        $loop          = !empty($settings['parameter']['loop']) ? 'loop:true,' : '';
        $spaceBetween  = !empty($settings['parameter']['thumbnailsSpaceBetween'])
            ? 'spaceBetween:' . (int)$settings['parameter']['thumbnailsSpaceBetween'] . ','
            : '';
        $slidesPerView = !empty($settings['parameter']['thumbnailsSlidesPerView'])
            ? 'slidesPerView:' . (int)$settings['parameter']['thumbnailsSlidesPerView'] . ','
            : '';

        return "    var swiperThumb{$uid} = new Swiper('.swiper-thumb-{$uid}', {"
            . "{$loop}{$spaceBetween}{$slidesPerView}freeMode:true,watchSlidesProgress:true,"
            . "});\n";
    }

    protected static function hex2RGB(string $hexStr, string $separator = ','): string
    {
        $hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr);
        $len    = strlen($hexStr);

        if ($len === 6) {
            $val = hexdec($hexStr);
            return implode($separator, [
                0xFF & ($val >> 0x10),
                0xFF & ($val >> 0x8),
                0xFF & $val,
            ]);
        }

        if ($len === 3) {
            return implode($separator, [
                hexdec(str_repeat($hexStr[0], 2)),
                hexdec(str_repeat($hexStr[1], 2)),
                hexdec(str_repeat($hexStr[2], 2)),
            ]);
        }

        return '';
    }
}