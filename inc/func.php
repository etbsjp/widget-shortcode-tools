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
	/**
	 * ダッシュボードにサポート導線用のウィジェットを追加する。
	 *
	 * `manage_options` 権限を持つユーザーにのみ表示する。
	 *
	 * @return void
	 */
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
	/**
	 * ダッシュボードウィジェットの中身（使い方・注意事項・サポート導線）を出力する。
	 *
	 * @return void
	 */
	function etbs_widget_shortcode_render_dashboard_widget() {
		$reading_url = admin_url( 'options-reading.php' );

		// サポート導線のリンク（プラグイン一覧の行と同じ文言・URLで統一する）。
		$donate_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://etbs.jp/product/donate/?utm_source=widget-shortcode-tools&utm_medium=plugin' ),
			esc_html__( '開発を支援', 'widget-shortcode-tools' )
		);
		$request_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://etbs.jp/product-category/wordpress-tools/?utm_source=widget-shortcode-tools&utm_medium=plugin' ),
			esc_html__( '開発のご依頼', 'widget-shortcode-tools' )
		);
		?>
		<p><?php esc_html_e( 'ウィジェットをショートコード化して、投稿本文や固定ページに埋め込めます。', 'widget-shortcode-tools' ); ?></p>

		<strong><?php esc_html_e( '使い方', 'widget-shortcode-tools' ); ?></strong>
		<ol style="margin:6px 0 12px 1.2em;list-style:decimal;">
			<li><?php echo wp_kses_post( __( '<strong>設定 &gt; 表示設定</strong>の「デフォルト数」で、ショートコード化するウィジェットの数（1〜5）を指定します。', 'widget-shortcode-tools' ) ); ?></li>
			<li><?php echo wp_kses_post( __( '数を保存すると、<strong>外観 &gt; ウィジェット</strong>に <code>[widget_shortcode_1 ws=1]</code> 〜 その数までのウィジェットエリアが追加されます。', 'widget-shortcode-tools' ) ); ?></li>
			<li><?php echo wp_kses_post( __( '各エリアにウィジェットを配置し、対応するショートコード（例：<code>[widget_shortcode_1 ws=1]</code>）を投稿・固定ページ本文に貼り付けると、そのウィジェットが表示されます。', 'widget-shortcode-tools' ) ); ?></li>
		</ol>

		<strong><?php esc_html_e( '注意事項', 'widget-shortcode-tools' ); ?></strong>
		<ul style="margin:6px 0 12px 1.2em;list-style:disc;">
			<li><?php esc_html_e( '「デフォルト数」を減らすと、それを超える番号のウィジェットエリアは表示されなくなり、そこに配置していたウィジェットは「外観 > ウィジェット」を開いた時点で「使用停止中のウィジェット」へ移動します。中身は残りますが、数を戻しても自動では元の位置に戻りません。', 'widget-shortcode-tools' ); ?></li>
		</ul>

		<strong><?php esc_html_e( 'サポート', 'widget-shortcode-tools' ); ?></strong>
		<p style="margin:6px 0 12px;">
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %s: 「開発のご依頼」リンク */
				__( '有償サポートやカスタマイズは%sからお問い合わせください。', 'widget-shortcode-tools' ),
				$request_link
			)
		);
		echo ' ';
		echo wp_kses_post(
			sprintf(
				/* translators: %s: 「開発を支援」リンク */
				__( '開発の継続は%sで応援いただけます。', 'widget-shortcode-tools' ),
				$donate_link
			)
		);
		?>
		</p>

		<a href="<?php echo esc_url( $reading_url ); ?>" class="button button-primary"><?php esc_html_e( '表示設定を開く', 'widget-shortcode-tools' ); ?></a>
		<?php
	}
}

/*-------------------------------------------*/
/* サポート導線（プラグイン一覧の行）
/*-------------------------------------------*/
if ( ! function_exists( 'etbs_widget_shortcode_plugin_row_meta' ) ) {
	/**
	 * プラグイン一覧の行メタにサポート導線リンクを追加する。
	 *
	 * @param string[] $links プラグインの行メタリンク一覧。
	 * @param string   $file  対象プラグインのファイルパス（`plugin_basename()` 形式）。
	 * @return string[] このプラグイン行の場合はリンクを追加した配列、それ以外は元の配列。
	 */
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
	/**
	 * 表示設定画面のフッターにサポート導線を表示する。
	 *
	 * @param string $text 元のフッターテキスト。
	 * @return string 表示設定画面の場合はサポート導線を含むテキスト、それ以外は元のテキスト。
	 */
	function etbs_widget_shortcode_admin_footer_text( $text ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'options-reading' !== $screen->id ) { return $text; }

		$donate_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://etbs.jp/product/donate/?utm_source=widget-shortcode-tools&utm_medium=plugin' ),
			esc_html__( '開発を支援', 'widget-shortcode-tools' )
		);
		$request_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://etbs.jp/product-category/wordpress-tools/?utm_source=widget-shortcode-tools&utm_medium=plugin' ),
			esc_html__( '開発のご依頼', 'widget-shortcode-tools' )
		);

		// 「設定 → 表示設定」は WordPress コアの画面であり、このプラグインの専用画面ではない。
		// 元のフッター文言を消さないよう、置き換えずに追記する。
		$notice = wp_kses_post(
			sprintf(
				/* translators: 1: 「開発を支援」リンク, 2: 「開発のご依頼」リンク */
				__( 'Shortcode Widget Toolsが役に立ったら %1$s、カスタマイズは %2$s からどうぞ。', 'widget-shortcode-tools' ),
				$donate_link,
				$request_link
			)
		);

		return ( '' === $text ) ? $notice : $text . ' | ' . $notice;
	}
	add_filter( 'admin_footer_text', 'etbs_widget_shortcode_admin_footer_text' );
}
