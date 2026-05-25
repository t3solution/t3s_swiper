# TYPO3 Extension `t3s_swiper`

[![Latest Stable Version](https://poser.pugx.org/t3s/t3s-swiper/v/stable)](https://packagist.org/packages/t3s/t3s-swiper)
[![Monthly Downloads](https://poser.pugx.org/t3s/t3s-swiper/d/monthly)](https://packagist.org/packages/t3s/t3s-swiper)
[![Total Downloads](https://poser.pugx.org/t3s/t3s-swiper/downloads)](https://packagist.org/packages/t3s/t3s-swiper)
[![License](https://poser.pugx.org/t3s/t3s-swiper/license)](https://packagist.org/packages/t3s/t3s-swiper)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/t3sbootstrap)

A TYPO3 integration of the [Swiper](https://swiperjs.com/) slider — the most modern free and open-source mobile touch slider — built on top of the [Content Blocks](https://github.com/FriendsOfTYPO3/content-blocks) API.

This extension registers a ready-to-use **"Swiper Slider"** content element, so editors can create fully responsive, touch-enabled sliders directly from the TYPO3 backend — without writing a single line of code.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Via Composer (recommended)](#via-composer-recommended)
  - [Via TYPO3 Extension Repository (TER)](#via-typo3-extension-repository-ter)
- [Usage](#usage)
- [Configuration & Customization](#configuration--customization)
- [Demos & Documentation](#demos--documentation)
- [Support the Project](#support-the-project)
- [License](#license)

---

## Features

- **Modern Swiper slider** — hardware-accelerated transitions, true 1:1 touch movement, responsive out of the box
- **Built on Content Blocks** — clean, structured content modeling using the official TYPO3 Content Types API
- **No code required** — editors get a dedicated *"Swiper Slider"* content element in the backend
- **Mobile-first** — optimized for touch devices, but works just as well on desktop
- **Easily customizable** — override Fluid templates, partials and assets in your own site package
- **Works standalone or with t3sbootstrap** — designed to integrate seamlessly with the [t3sbootstrap](https://www.t3sbootstrap.de/) ecosystem, but not required

## Requirements

| Component        | Version       |
|------------------|---------------|
| TYPO3            | `>= 14.3`     |
| `content_blocks` | `>= 2.3.5`   |
| PHP              | as required by your TYPO3 version |

> **Note on TYPO3 v14:** Content Blocks `2.x` is required for TYPO3 v14. Make sure your `content_blocks` constraint matches your Core version.

## Installation

### Via Composer (recommended)

In your Composer-based TYPO3 project root, run:

```bash
composer require t3s/t3s-swiper
```

Then activate the extension and flush the caches:

```bash
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

### Via TYPO3 Extension Repository (TER)

1. Make sure the dependency [`content_blocks`](https://extensions.typo3.org/extension/content_blocks) is installed first.
2. Download and install `t3s_swiper` via the **Extension Manager** in the TYPO3 backend.
3. Flush all caches.

## Usage

Once the extension is installed and activated, a new content element **"Swiper Slider"** becomes available in the *"New Content Element"* wizard.

1. Open the desired page in the backend.
2. Click **"Create new content element"** and choose **Swiper Slider**.
3. Add your slides, configure the slider options, and save.
4. View the result in the frontend — the slider is rendered automatically with all required JS and CSS assets.

## Configuration & Customization

Because `t3s_swiper` is built on **Content Blocks**, all customization follows the standard Content Blocks conventions:

- **Templates** — Override Fluid templates by copying them from `EXT:t3s_swiper/ContentBlocks/.../templates/` into your own site package.
- **Assets** — CSS and JS are registered through the Content Blocks asset mechanism and can be replaced or extended.
- **Fields** — Extend the YAML definition in your own Content Block to add fields, or fork the element for project-specific needs.

Refer to the [Content Blocks documentation](https://docs.typo3.org/p/friendsoftypo3/content-blocks/main/en-us/) for details on overriding and extending content blocks.

## Demos & Documentation

- **Live demos & full documentation:** <https://www.t3sbootstrap.de/extensions/swiper-slider>
- **Packagist:** <https://packagist.org/packages/t3s/t3s-swiper>
- **About Swiper:** <https://swiperjs.com/>

## Support the Project

If you find this extension useful, please consider supporting its development:

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/t3sbootstrap)

Bug reports, feature requests and pull requests are very welcome via the issue tracker.

## License

This extension is released under the **GPL-2.0-or-later** license, in line with the TYPO3 Core. See [LICENSE](LICENSE) for details.

Swiper is © [Vladimir Kharlampidi](https://github.com/nolimits4web) and licensed under the MIT license.
