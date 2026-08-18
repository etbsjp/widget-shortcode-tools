<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/*-------------------------------------------*/
/*  追加するウィジェット数
/*-------------------------------------------*/
// セッティングAPI
if ( ! function_exists( 'etbs_widget_shortcode_settings' ) ) {
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
	add_action( 'admin_init', 'etbs_widget_shortcode_settings' );
}
// セクション用の関数
if ( ! function_exists( 'etbs_add_settings_section' ) ) {
	function etbs_add_settings_section() {
		_e( 'ショートコード化するウィジェットの数', 'widget-shortcode-generator' );
	}
}
// widget_num_field用の関数
if ( ! function_exists( 'etbs_widget_num_field' ) ) {
	function etbs_widget_num_field() {
		?>
		<input id="widget_num_field" name="widget_num_field" type="number" step="1" min="1" max="5" value="<?php form_option('widget_num_field'); ?>" class="small-text" />
		<?php
	}
}

/*-------------------------------------------*/
/*  ウィジェットを追加
/*  [widget_shortcode_0 ws=0](0の部分は1〜5の数字)
/*-------------------------------------------*/
class Widget_Shortcode {

	public static function init() {
		$num = get_option( 'widget_num_field' );
		for ( $i = 1; $i <= $num; $i++ ) {
			register_sidebar( array(
				'name'          => '[widget_shortcode_' . $i . ' ws=' . $i . ']',
				'id'            => 'shortcode_widget_' . $i,
				'before_widget' => '<div class="widget shortcode_widget_' . $i . '">',
				'after_widget'  => '</div>',
			) );
			add_shortcode( 'widget_shortcode_' . $i, array( __CLASS__, 'make_shortcode' ) );
		}
	}

	public static function make_shortcode( $ws ) {
		extract( shortcode_atts( array(
			'ws' => 1,
		), $ws ) );
		ob_start();
		dynamic_sidebar( 'shortcode_widget_' . $ws );
		return ob_get_clean();
	}

}
Widget_Shortcode::init();

/*-------------------------------------------*/
/* サポート導線（ダッシュボードウィジェット）
/*-------------------------------------------*/
if ( ! function_exists( 'etbs_widget_shortcode_add_dashboard_widget' ) ) {
	function etbs_widget_shortcode_add_dashboard_widget() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		wp_add_dashboard_widget(
			'etbs_widget_shortcode_dashboard_widget',
			'Shortcode Widget Tools',
			'etbs_widget_shortcode_render_dashboard_widget'
		);
	}
	add_action( 'wp_dashboard_setup', 'etbs_widget_shortcode_add_dashboard_widget' );
}

if ( ! function_exists( 'etbs_widget_shortcode_render_dashboard_widget' ) ) {
	function etbs_widget_shortcode_render_dashboard_widget() {
		$reading_url = admin_url( 'options-reading.php' );
		?>
		<p>ウィジェットをショートコード化して、投稿本文や固定ページに埋め込めます。</p>

		<strong>使い方</strong>
		<ul style="margin:6px 0 12px 1.2em;list-style:disc;">
			<li><strong>設定 &gt; 表示設定</strong>の「デフォルト数」で、ショートコード化するウィジェットの数（1〜5）を指定します。</li>
			<li>数を保存すると、<strong>外観 &gt; ウィジェット</strong>に <code>[widget_shortcode_1 ws=1]</code> 〜 その数までのウィジェットエリアが追加されます。</li>
			<li>各エリアにウィジェットを配置し、対応するショートコード（例：<code>[widget_shortcode_1 ws=1]</code>）を投稿・固定ページ本文に貼り付けると、そのウィジェットが表示されます。</li>
		</ul>

		<strong>注意事項</strong>
		<ul style="margin:6px 0 12px 1.2em;list-style:disc;">
			<li>「デフォルト数」を減らすと、それを超える番号のウィジェットエリアは表示されなくなります（配置済みウィジェットの設定自体は削除されません）。</li>
		</ul>

		<strong>サポート</strong>
		<p style="margin:6px 0 12px;">有償サポートやカスタマイズは<a href="https://etbs.jp/product-category/wordpress-tools/?utm_source=widget-shortcode-tools&utm_medium=plugin" target="_blank" rel="noopener noreferrer">こちらのページ</a>からお問い合わせください。開発の継続は<a href="https://etbs.jp/product/donate/?utm_source=widget-shortcode-tools&utm_medium=plugin" target="_blank" rel="noopener noreferrer">ご支援</a>で応援いただけます。</p>

		<a href="<?php echo esc_url( $reading_url ); ?>" class="button button-primary">表示設定を開く</a>
		<?php
	}
}

/*-------------------------------------------*/
/* サポート導線（プラグイン一覧の行）
/*-------------------------------------------*/
if ( ! function_exists( 'etbs_widget_shortcode_plugin_row_meta' ) ) {
	function etbs_widget_shortcode_plugin_row_meta( $links, $file ) {
		if ( plugin_basename( WST_PLUGIN_FILE ) !== $file ) { return $links; }
		$links[] = '<a href="https://etbs.jp/product/donate/?utm_source=widget-shortcode-tools&utm_medium=plugin" target="_blank" rel="noopener noreferrer">'
			. esc_html__( '開発を支援', 'widget-shortcode-tools' ) . '</a>';
		$links[] = '<a href="https://etbs.jp/product-category/wordpress-tools/?utm_source=widget-shortcode-tools&utm_medium=plugin" target="_blank" rel="noopener noreferrer">'
			. esc_html__( '開発のご依頼', 'widget-shortcode-tools' ) . '</a>';
		return $links;
	}
	add_filter( 'plugin_row_meta', 'etbs_widget_shortcode_plugin_row_meta', 10, 2 );
}

/*-------------------------------------------*/
/* サポート導線（設定 → 表示設定 画面のフッター）
/*-------------------------------------------*/
if ( ! function_exists( 'etbs_widget_shortcode_admin_footer_text' ) ) {
	function etbs_widget_shortcode_admin_footer_text( $text ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'options-reading' !== $screen->id ) { return $text; }
		return 'Shortcode Widget Toolsが役に立ったら <a href="https://etbs.jp/product/donate/?utm_source=widget-shortcode-tools&utm_medium=plugin" target="_blank" rel="noopener noreferrer">開発を支援</a>、カスタマイズは <a href="https://etbs.jp/product-category/wordpress-tools/?utm_source=widget-shortcode-tools&utm_medium=plugin" target="_blank" rel="noopener noreferrer">開発のご依頼</a> からどうぞ。';
	}
	add_filter( 'admin_footer_text', 'etbs_widget_shortcode_admin_footer_text' );
}
