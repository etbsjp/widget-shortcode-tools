<?php
/**
 * Plugin Name: Shortcode Widget Tools
 * Version: 1.0.0
 * Description: ウィジェットをショートコード化して使用できます。ウィジェット数は 設定 -> 表示設定 から可能です。[widget_shortcode_0 ws=0] (0の部分は1〜5の数字)で埋め込みできます。
 * Author: DAI
 * Author URI: https://etbs.jp
 * Plugin URI: https://etbs.jp/product-category/wordpress-tools/
 * Text Domain: widget-shortcode-tools
 * Domain Path: /languages
 * @package widget-shortcode-tools
 */

/*-------------------------------------------*/
/*  追加するウィジェット数
/*-------------------------------------------*/
// セッティングAPI
if ( ! function_exists( 'etbs_widget_shortcode_settings' ) ){
	function etbs_widget_shortcode_settings() {
		// セクションを登録
		add_settings_section(
			'widget_shortcode_settings',
			__( 'Widget Shortcode Settings', 'widget-shortcode-generator' ),
			'etbs_add_settings_section',
			'reading'
			);
		// フィールドを登録
		add_settings_field(
			'widget_num_field',
			__( 'デフォルト数', 'widget-shortcode-generator' ),
			'etbs_widget_num_field',
			'reading',
			'widget_shortcode_settings'
			);
		// 登録して保存されるようにする
		register_setting( 'reading', 'widget_num_field', 'intval' );
	}
}
// セクション用の関数
if ( ! function_exists( 'etbs_add_settings_section' ) ){
	function etbs_add_settings_section() {
		_e( 'ショートコード化するウィジェットの数', 'widget-shortcode-generator' );
	}
}
// widget_num_field用の関数
if ( ! function_exists( 'etbs_widget_num_field' ) ){
	function etbs_widget_num_field() {
		?>
		<input id="widget_num_field" name="widget_num_field" type="number" step="1" min="1" max="5" value="<?php form_option('widget_num_field'); ?>" class="small-text" />
		<?php 
	}
	add_action( 'admin_init', 'etbs_widget_shortcode_settings' );
}

/*-------------------------------------------*/
/*  アンインストール
/*-------------------------------------------*/
// プラグインが有効となったときにアンインストール処理をフックする
if ( ! function_exists( 'etbs_widget_shortcode_activate' ) ){
	function etbs_widget_shortcode_activate() {
		register_uninstall_hook( __FILE__, 'etbs_widget_shortcode_uninstall' );
	}
	register_activation_hook( __FILE__, 'etbs_widget_shortcode_activate' );
}
// アンインストール処理
if ( ! function_exists( 'etbs_widget_shortcode_uninstall' ) ){
	function etbs_widget_shortcode_uninstall() {
		///delete_option('widget_num_field');
	}
}

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


/*-------------------------------------------*/
/*  ウィジェットを追加
/*  [widget_shortcode_0 ws=0](0の部分は1〜5の数字)
/*-------------------------------------------*/
class Widget_Shortcode {

	public static function init() {
		$num = get_option('widget_num_field');
		for($i = 1; $i <= $num; $i++){
			register_sidebar(array(
				'name' => '[widget_shortcode_' . $i . ' ws=' . $i . ']' ,
				'id' => 'shortcode_widget_' . $i ,
				'before_widget' => '<div class="widget shortcode_widget_' . $i . '">',
				'after_widget' => '</div>',
			));
			add_shortcode('widget_shortcode_' . $i , array(__CLASS__, 'make_shortcode') );
		}
	}

	public static function make_shortcode($ws) {
		extract(shortcode_atts(array(
			'ws' => 1,
		), $ws));
		ob_start();
		dynamic_sidebar('shortcode_widget_' . $ws);
		return ob_get_clean();
	}

}
Widget_Shortcode::init();

?>