=== VK VWS Plugin Beta Tester ===
Contributors: vektor-inc
Tags: beta, testing, vk blocks, lightning
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable beta channel for VWS plugins to receive and test beta versions before stable release.

== Description ==

**VK VWS Plugin Beta Tester** allows you to receive beta versions of VWS (Vektor WordPress Solutions) plugins such as VK Blocks Pro, Lightning Pro, and others.

= Features =

* Receive beta versions of VK Blocks Pro
* Simple activation - no configuration needed
* Future support for other VWS plugins (Lightning Pro, etc.)
* Safe to deactivate - will switch back to stable versions

= Supported Plugins =

* VK Blocks Pro
* (More plugins coming soon)

= How it Works =

When this plugin is activated, it modifies the update check queries for supported VWS plugins to request beta versions instead of stable releases. The license server will then provide beta versions (if available) instead of stable versions.

= Important Notes =

* **Beta versions may contain bugs or unfinished features**
* **Do not use beta versions in production environments**
* When you deactivate this plugin, your plugins will switch back to stable versions when the next stable version is released
* Beta versions are provided for testing purposes only

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/vk-vws-plugin-beta-tester` directory, or install the plugin through VWS My Page
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Check for updates in the 'Plugins' screen to receive beta versions

== Frequently Asked Questions ==

= What happens when I deactivate this plugin? =

When you deactivate the beta tester plugin, your VWS plugins will stop receiving beta versions. They will switch back to stable versions when the next stable version is released. WordPress does not automatically downgrade plugins, so you may stay on the beta version until a newer stable version is available.

= Which plugins are supported? =

Currently, VK Blocks Pro is supported. More VWS plugins (Lightning Pro, etc.) will be added in the future.

= How do I report bugs found in beta versions? =

Please report issues through:
- GitHub Issues (preferred)
- VWS Support Forum
- Discord Community

= Can I choose which plugins receive beta versions? =

In the current version (0.1.0), all supported VWS plugins will receive beta versions when this plugin is active. Future versions may include individual plugin selection.

== Changelog ==

= 0.1.0 =
* Initial release
* Support for VK Blocks Pro beta channel
* Simple on/off functionality

== Upgrade Notice ==

= 0.1.0 =
Initial release of VK VWS Plugin Beta Tester.
