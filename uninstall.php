<?php
/**
 * アンインストール処理。
 *
 * Shortcode Widget Tools は「デフォルト数」の設定値（オプション `widget_num_field`）をもとに
 * ウィジェットエリアを動的に登録している。アンインストール時に設定を削除すると、利用者が
 * 配置済みのウィジェットが行き場を失う可能性があるため、このプラグインは削除時にデータを
 * 一切消さない（task-queue #108 の案A決定）。
 *
 * 独自テーブルも cron も持たないため、案Aに従うと「何もしない」が正しい実装になる。
 *
 * @package widget-shortcode-tools
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// 意図的に何もしない。
