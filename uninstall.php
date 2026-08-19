<?php
/**
 * アンインストール処理。
 *
 * Shortcode Widget Tools は「デフォルト数」の設定値をもとにウィジェットエリアを
 * 動的に登録している。アンインストール時に設定を削除すると、利用者が配置済みの
 * ウィジェットが行き場を失う可能性があるため、このプラグインは削除時にデータを
 * 一切消さない。
 *
 * @package widget-shortcode-tools
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// 意図的に何もしない。
