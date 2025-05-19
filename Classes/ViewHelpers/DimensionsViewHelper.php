<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension t3s_swiper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3S\T3sSwiper\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DimensionsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('settings', 'array', 'Swiper settings.', true);
    }

    /**
     * Render the URI to the resource. The filename is used from child content.
     */
    public function render(): array
    {
        $settings = $this->arguments['settings']->toArray();
        $sliderwidth = !empty($settings['main']['width']) ? (int)$settings['main']['width'] : 1300;
        $slidesPerView = (int)$settings['parameter']['slidesPerView'];
        $spaceBetween = (int)$settings['parameter']['spaceBetween'];
        $ratio = !empty($settings['main']['ratio']) ? $settings['main']['ratio'] : '16:9';

        $dimensions['sliderwidth'] = $sliderwidth;
        $dimensions['width'] = $sliderwidth;

        if ($settings['effects']['effectType'] === 'slide' && $slidesPerView > 1) {
            $sliderwidth = $sliderwidth - ($slidesPerView - 1) * $spaceBetween;
            $dimensions['width'] = ceil($sliderwidth / $slidesPerView);
        }

        $dimensions['thumbnails']['thumbnailwidth'] = $sliderwidth;
        if ($settings['thumbnails']['thumbnailsSlidesPerView'] > 1) {
            $spaceBetween = (int)$settings['thumbnails']['thumbnailsSpaceBetween'];
            $sliderwidth = $sliderwidth - ($settings['thumbnails']['thumbnailsSlidesPerView'] - 1) * $spaceBetween;
            $dimensions['thumbnailwidth'] = $sliderwidth / $settings['thumbnails']['thumbnailsSlidesPerView'];
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
