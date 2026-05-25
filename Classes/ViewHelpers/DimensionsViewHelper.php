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
     * Calculates slider and thumbnail dimensions based on configured
     * width, slidesPerView, spaceBetween and aspect ratio.
     */
    public function render(): array
    {
        $settings = $this->arguments['settings']->toArray();

        $sliderwidth = !empty($settings['main']['width']) ? (int)$settings['main']['width'] : 1300;
        $slidesPerView = (int)($settings['parameter']['slidesPerView'] ?? 1);
        $spaceBetween = (int)($settings['parameter']['spaceBetween'] ?? 0);
        $ratio = !empty($settings['main']['ratio']) ? (string)$settings['main']['ratio'] : '16:9';
        $effectType = $settings['effects']['effectType'] ?? '';
        $thumbnailsSlidesPerView = (int)($settings['thumbnails']['thumbnailsSlidesPerView'] ?? 0);

        $dimensions = [
            'sliderwidth' => $sliderwidth > 695 ? $sliderwidth : 696,
            'width' => $sliderwidth,
            'thumbnailwidth' => $sliderwidth,
        ];

        // Slide effect with multiple slides per view: shrink slide width
        if ($effectType === 'slide' && $slidesPerView > 1) {
            $sliderwidth = $sliderwidth - ($slidesPerView - 1) * $spaceBetween;
            $sliderwidth = (int)ceil($sliderwidth / ($slidesPerView - 1));
            $dimensions['width'] = $sliderwidth > 695 ? $sliderwidth : 696;
        }
        
        if ($effectType === 'cube' || $effectType === 'cards') {
            
            $dimensions['width'] = $sliderwidth > 695 ? $sliderwidth : 696;
        }

        // Thumbnails: shrink per-thumbnail width
        if ($thumbnailsSlidesPerView > 1) {
            $thumbnailsSpaceBetween = (int)($settings['thumbnails']['thumbnailsSpaceBetween'] ?? 0);
            $thumbnailsArea = $sliderwidth - ($thumbnailsSlidesPerView - 1) * $thumbnailsSpaceBetween;
            $dimensions['thumbnailwidth'] = (int)floor($thumbnailsArea / $thumbnailsSlidesPerView);
        }

        // Aspect ratio (default 16:9 -> multiplier 9/16)
        $ratioMultiplier = 9 / 16;
        if (str_contains($ratio, ':')) {
            $ratioArr = explode(':', $ratio);
            $ratioWidth = (float)($ratioArr[0] ?? 0);
            $ratioHeight = (float)($ratioArr[1] ?? 0);
            if ($ratioWidth > 0) {
                $ratioMultiplier = $ratioHeight / $ratioWidth;
            }
        }

        $dimensions['sliderheight'] = (int)ceil($dimensions['width'] * $ratioMultiplier);
        $dimensions['height'] = $dimensions['sliderheight'];
        $dimensions['thumbnailheight'] = (int)ceil($dimensions['thumbnailwidth'] * $ratioMultiplier);

        return $dimensions;
    }
}
