# widget-shortcode-tools

etbs が配布する WordPress プラグイン。共通ルールの正本は `~/.claude/etbs-plugin-rules.md`。

## レビュー工程に大（シニアエンジニア）を追加する

このリポジトリでは、安藤（`vk-code-reviewer`）のレビューのあと、**PR を作成する前に**
大（`etbs-senior-wp`）の監査を必ず通すこと。大は etbs の申し送りと過去に踏んだ罠に照らして
「リリースできる形になっているか」を見る担当で、安藤の一般的なコード品質レビューとは層が違う。

- `Agent` ツールで `subagent_type: etbs-senior-wp`、`name: etbs-senior-wp`、
  **`run_in_background: false`** で起動する
- **`isolation: "worktree"` は使えるなら付ける**（付けないと起動応答は「成功」と返るのに
  一度も作業せず待機状態に入ることがある）。ただし ★★ **作業ディレクトリが git リポジトリでないと使えない。**
  その場合は **isolation なしで起動してよい**（2026-08-19 実績あり）。
  **見分け方は起動応答の形**——`output_file` 付きの正常形なら動いている
- prompt には対象リポジトリ・ブランチ・差分（または PR 番号）を渡す
- 大には **出力の末尾に `監査結果: PASS` または `監査結果: FAIL` を必ず書くよう指示する**
  （★ 大の定義ファイルには出力形式の指定が無いため、指示しないと合否を機械判定できない）
- `監査結果: PASS` を受け取るまで PR を作成しない。`FAIL` なら和田へ差し戻して再監査する

★ 大は vk-agents のメンバー表に登録されていないため、指示が無いと**永久に呼ばれない**。

## 検証環境

Local の `order-memo`（`ordermemo.etbs.lc`）。**シンボリックリンク設置でよい**
（このプラグインは `dirname( __FILE__, N )` を使っていない）。

CLI 検証では Local の php.ini を `-c` で渡すこと。渡さないと「データベース接続確立エラー」になり、
**サイトが停止しているように見える**（実際は動いている）。`<runId>` は
`ls -d ~/Library/Application\ Support/Local/run/*/mysql/mysqld.sock` で特定する。

## アンインストール

★ `uninstall.php` の方針は**案A**（task-queue #108）。判定は3分類。

| 利用者が作ったコンテンツ（投稿・投稿メタ） | 利用者が設定した値（オプション） | 一時状態・自分が仕掛けた cron |
|---|---|---|
| **消さない** | **消さない** | **消す** |

理由は害の非対称性。消さないことの害は「DB に少量のレコードが残る」だけだが、消すことの害は
復旧不可能。迷ったら残す側に倒す。

このプラグインでの当てはめ:

- **残す** … オプション `widget_num_field`（`inc/func.php:26` の `register_setting( 'reading', ... )`。
  利用者が設定した「デフォルト数」。この値をもとにウィジェットエリアを動的に登録しているため、
  消すと配置済みのウィジェットが行き場を失う）
- **消す** … 該当なし（独自テーブルも cron も持たない）

★ このオプション名には接頭辞が無く、他プラグイン・テーマと衝突しうる。ただし改名すると既存
インストールの設定値が失われるため、移行を伴う別 issue で扱う（#108 の範囲外の参考所見）。

★ 配布8本すべてがこの3分類で説明できる状態にしてある。テーブルと cron を持つのは editlock だけ、
一時状態のオプションを持つのは pageguard だけで、そこだけが「消す」に該当する。
**他のプラグインで「何も消していない」のは判断の結果であって書き忘れではない。**
横並びで「消す」側へ揃えにこないこと。

## 版数

版数は**ヘッダの `Version:` 1箇所のみ**（`wp_enqueue_*` を使っていないためキャッシュバスターは無い）。
`readme.txt` は無く `readme.md` のみなので、PUC は本体ヘッダを読む
（`Requires` 系が readme に上書きされる罠は起きない）。

★★ **配布4本（woo-modal-block / woo-checkout-colorbox / woo-hit-orderlist / widget-shortcode-tools）は
版数を揃える。単独で上げない。**

## CI（2026-08-27 導入・task-queue #153 / issue #2）

PR ごとに GitHub Actions で2つ走る。定義は `.github/workflows/ci.yml`。

| ジョブ | 中身 |
|---|---|
| `php -l (PHP 7.4)` / `(PHP 8.3)` | 追跡している全 `.php` の構文チェック |
| `PHPCS (WordPress-Extra, changed lines)` | WPCS を **PR の差分行だけ**に適用（`sirbrillig/phpcs-changed`） |

### 決定済み（変えないこと）

- ★★ **既存コードの指摘は直さない。** 2026-08-27 実測で `WordPress-Extra` に対し **20 ERROR / 2 WARNING** ある。
  `dist` は PUC の配信先なので、整形のための版数上げ＝配信を起こしたくない。
  だから `phpcs-changed` で**変更行のみ**を必須にしている
- ★★★ **`phpcbf` を走らせないこと。** 一度走ると自動修正が入り、上の前提が丸ごと壊れる
- **検査ツールは同梱しない。** `composer install` で入る（`composer.json` は `require-dev` のみ）。
  生成物 `vendor/` は `.gitignore` 済み・配布物からは `export-ignore` 済み
- **standard は `WordPress-Extra`。** `WordPress-Core` は未エスケープ出力（XSS）も nonce 未検証も
  **検出しない**（陽性対照で実測）。フル `WordPress` との差は docblock の書式だけなので採らない
- **起動条件は `pull_request` の無条件実行。** `run-ci` ラベル条件にしない。
  vk-agents の `ci.md` はエージェントが CI を起動しない運用だが、
  「リポジトリ側の設定で自動実行される場合」は例外。ラベル運用だと自動フローの PR で CI が一度も走らない
- `inc/plugin-update-checker/` は第三者コードなので `.phpcs.xml.dist` で検査対象から除外

### ★ CI に触るときの検証

**「Error 0 で緑」を成果にしないこと。** `.php` を変更しない PR では PHPCS は対象0件で自明に緑になる。
検査が動いていることは**陽性対照**でしか言えない ― 使い捨てブランチに `echo $_GET['probe'];` を1行足し、
`PHPCS` が赤くなり指摘がその行を指すことを確認する。

### ★ 現状の穴（未対応・展開前に判断）

- `on: pull_request` のみで `push:` が無い。`dist` の branch protection も未設定（Classic・Rulesets とも）なので、
  **版数上げの直 push は無検査**のまま利用者へ配信される
- CI の下限は PHP 7.4。一方この節の下の「宣言（Requires）の方針」は **7.3.5 で通ること**を
  無宣言の根拠にしている。7.3 は CI で検査していない

## 宣言（Requires）の方針

★★ `Requires at least` / `Requires PHP` は**実在する下限があるときだけ書く。無ければ書かない。**
**他のプラグインと横並びで揃えない。** 本体ヘッダだけでなく **`readme` にも書かない**
（2026-08-19、readme に `PHP 7.4 以上` と書いてしまい監査で差し戻された。
ヘッダと食い違うと、次に気付いた人が readme に合わせてヘッダへ足す方向に動く）。

- 過剰宣言は WordPress が **`validate_plugin_requirements()` で有効化そのものを拒否**する
- ★ **更新が止まる見え方は2つの宣言で違う。**
  - `Requires at least`：PUC は `requires` を更新トランジェントに入れない
    （`Puc/v5p5/Plugin/Update.php` に該当プロパティが無い）ため、**更新リンクは出る**。
    押した後に `Plugin_Upgrader::check_package()` が zip のヘッダを読んで
    `incompatible_wp_required_version` で止める＝**「押すと失敗する」**
  - `Requires PHP`：PUC は `requires_php` を**入れる**（同 `:20` / `:87`）ため、
    `wp_plugin_update_row()` が **更新リンクそのものを出さない**
- **`Requires at least` は 2026-08-19 に削除した。** 自前コードで最も新しい WP API は
  `get_current_screen()`（**WP 3.3**）、同梱 PUC を含めても `wp_doing_cron()`（**WP 4.8**）で、
  6.x 帯に下限が存在しない。6.7 は「他5本と揃える」という理由で 2026-08-18 に足したもので、
  API に紐づいた宣言ではなかった
- **`Requires PHP` は意図的に書かない**（2026-08-18 決定・2026-08-19 再確認）。
  同梱 PUC の `composer.json` は `>=5.6.20`、本体に PHP7 固有の構文も無く、実測で PHP 7.3.5 の
  `php -l` が通る。7.4 を足すと、7.4 未満で FTP 手動設置された個体に以後の修正が永久に届かなくなる

★★★ **この判断は `Requires PHP` にも及ぶ（無宣言のまま据え置く）。** 2026-08-25 に
task-queue #111 で「`Requires PHP` を書かない判断として合意した記録が無い」と読まれかけたため明記する。
節の見出しが `Requires at least` の話に見えるが、**両方のヘッダが対象**。

実測（2026-08-25）: PHP 7.4.30 で同梱 PUC 込みの全ファイル、PHP **7.3.5** で自前コード全ファイルが
`php -l` を通る。→ **「書かないと構文エラーで白画面」という害は実在しない。**
一方、新しく書けば PHP 7.3 以下の個体には**以後の更新が永久に届かなくなる**。
書かない側の害がゼロで、書く側の害が非可逆なので、無宣言を維持する。

★★ **「据え置き」と「新規に足す」は別問題**（2026-08-25 / task-queue #111 で再確認）。
既に宣言している版を据え置いても新たに締め出す個体は生まれないが、**無宣言のプラグインに
`Requires PHP` を新しく足すと、いま更新が届いている個体を以後届かなくする**。
`woo-checkout-colorbox` と `widget-shortcode-tools` が無宣言なのは、この理由による意図的な判断。
**8本で揃えにこないこと。**
