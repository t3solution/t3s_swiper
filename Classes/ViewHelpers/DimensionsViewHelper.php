<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension t3s_swiper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3S\T3sSwiper\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class DimensionsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('settings', 'array', 'Swiper settings.', true);
    }

    /**
     * Render the URI to the resource. The filename is used from child content.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $settings = $arguments['settings'];
        $sliderwidth = !empty($settings['width']) ? (int)$settings['width'] : 1300;
        $slidesPerView = (int)$settings['slidesPerView'];
        $spaceBetween = (int)$settings['spaceBetween'];
        $ratio = !empty($settings['ratio']) ? $settings['ratio'] : '16:9';

        $dimensions['sliderwidth'] = $sliderwidth;
        $dimensions['width'] = $sliderwidth;

        if ($settings['effectType'] === 'slide' && $slidesPerView > 1) {
            $sliderwidth = $sliderwidth - ($slidesPerView - 1) * $spaceBetween;
            $dimensions['width'] = ceil($sliderwidth / $slidesPerView);
        }

        $dimensions['thumbnailwidth'] = $sliderwidth;
        if ($settings['thumbnailsSlidesPerView'] > 1) {
            $spaceBetween = (int)$settings['thumbnailsSpaceBetween'];
            $sliderwidth = $sliderwidth - ($settings['thumbnailsSlidesPerView'] - 1) * $spaceBetween;
            $dimensions['thumbnailwidth'] = $sliderwidth / $settings['thumbnailsSlidesPerView'];
        }

        $ratio_multiplier = 9/16;
        if (str_contains($ratio, ':')) {
            $ratioArr = explode(':', $ratio);
            $ratio_multiplier = $ratioArr[1] / $ratioArr[0];
        }

        $dimensions['sliderheight'] = ceil($dimensions['width'] * $ratio_multiplier);
        $dimensions['height'] = ceil($dimensions['width'] * $ratio_multiplier);
        $dimensions['thumbnailheight'] = ceil($dimensions['thumbnailwidth'] * $ratio_multiplier);

        return $dimensions;
    }
}
