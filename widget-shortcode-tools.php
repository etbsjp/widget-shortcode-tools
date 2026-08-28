<?php
/**
 * Plugin Name: Shortcode Widget Tools
 * Version: 1.1.1
 * Description: ウィジェットをショートコード化して、投稿本文や固定ページに埋め込めます。ウィジェット数は「設定 → 表示設定」で 1〜5 の範囲で指定し、[widget_shortcode_1 ws=1] のように埋め込みます（数字は1〜5）。
 * Author: ETBS (DAI)
 * Author URI: https://etbs.jp
 * Plugin URI: https://etbs.jp/product-category/wordpress-tools/
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
