<?php
/**
 * Plugin Name: Shortcode Widget Tools
 * Version: 1.1.0
 * Description: ウィジェットをショートコード化して使用できます。ウィジェット数は 設定 -> 表示設定 から可能です。[widget_shortcode_0 ws=0] (0の部分は1〜5の数字)で埋め込みできます。
 * Author: DAI
 * Author URI: https://etbs.jp
 * Plugin URI: https://etbs.jp/product-category/wordpress-tools/
 * Requires at least: 6.7
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: widget-shortcode-tools
 * @package widget-shortcode-tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WST_PLUGIN_FILE', __FILE__ );

require_once( dirname( __FILE__ ) . '/inc/func.php' );

/*-------------------------------------------*/
/*  プラグインのアップデートチェック
/*-------------------------------------------*/
require 'inc/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/etbsjp/widget-shortcode-tools/',
	__FILE__,
	'widget-shortcode-tools'
);
$myUpdateChecker->setBranch( 'dist' );
