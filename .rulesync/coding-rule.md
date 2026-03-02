---
root: true
targets: ["*"]
description: "VK VWS Plugin Beta Tester - Coding guidelines"
globs: ["**/*"]
---

# VK VWS Plugin Beta Tester コーディングルール

## 1. プロジェクト概要

VWS（Vektor WordPress Solutions）プラグインのベータ版を受け取るためのテスター用プラグイン。VK Blocks Pro、Lightning Proなどの対応プラグインの更新チェッククエリに`channel=beta`パラメータを追加する。

## 2. 共通ルール

- WordPressのコーディング規約とベストプラクティスに準拠
- 対応環境: PHP 7.4+、WordPress 6.5+
- インデントは常にタブ（スペース禁止）
- フロント出力は必ずi18n関数（`__()`, `_e()`など）を通す
- 名前空間は使用しない
- クラス名/関数名のプレフィックス: `VK_VWS_Plugin_Beta_Tester` / `vk_vws_`
- ドキュメント/コミュニケーションは原則日本語

## 3. PHP コーディング規約

### 基本方針
- シンプルさを保つ（このプラグインは小規模なユーティリティ）
- 拡張性を考慮（将来的に他のVWSプラグインにも対応）
- WordPressコーディング規約に厳密に従う

### クラス設計
```php
class VK_VWS_Plugin_Beta_Tester {
	// 対応プラグイン設定を配列で保持
	private $supported_plugins = array();

	// コンストラクタでフックを登録
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	// 初期化処理
	public function init() {
		// 対応プラグインごとにフィルターフックを登録
	}
}
```

### フィルターフック規約
対応プラグインのフィルター名は以下の形式に従う：
```
{プラグインスラッグ}_vws_update_check_query_args
```

例:
- VK Blocks Pro: `vk_blocks_pro_vws_update_check_query_args`
- Lightning Pro: `lightning_pro_vws_update_check_query_args`

### エスケープ/サニタイゼーション
- 管理画面出力: `esc_html()`, `esc_attr()`, `esc_url()`
- 翻訳: `esc_html__()`, `esc_html_e()`
- 配列キー: 信頼できるハードコードされた値のみ使用

## 4. ファイル構成

```
vk-vws-plugin-beta-tester/
├── vk-vws-plugin-beta-tester.php  # メインファイル（全機能を含む）
├── readme.txt                      # WordPress.org用説明
├── README.md                       # GitHub用ドキュメント
├── .gitignore                      # Git管理除外設定
├── .editorconfig                   # エディタ設定
├── .github/
│   └── PULL_REQUEST_TEMPLATE.md   # PRテンプレート
└── .rulesync/
    └── coding-rule.md             # このファイル
```

## 5. 新しいプラグインへの対応追加手順

1. `$supported_plugins`配列に追加:
```php
'plugin-slug' => array(
	'name'        => 'Plugin Name',
	'filter_hook' => 'plugin_slug_vws_update_check_query_args',
),
```

2. 対応プラグイン側でフィルターフックを実装:
```php
$query_args = apply_filters( 'plugin_slug_vws_update_check_query_args', $query_args );
```

3. ドキュメント（README.md、readme.txt）を更新

4. テスト実施（手動テスト必須）

## 6. テスト

### 手動テスト手順
1. プラグインを有効化
2. 対応プラグインがインストールされていることを確認
3. 更新チェックを実行（プラグイン画面で「更新を確認」）
4. デバッグログで`channel=beta`が送信されていることを確認
5. ライセンスサーバーからベータ版情報が返されることを確認

### デバッグ方法
対応プラグインの更新チェック関数内に以下を追加:
```php
error_log( print_r( $query_args, true ) );
```

## 7. リリース手順

1. バージョン番号を更新:
   - `vk-vws-plugin-beta-tester.php`のヘッダー
   - `readme.txt`の`Stable tag`

2. `readme.txt`のChangelogを更新

3. `README.md`に変更内容を追記（必要に応じて）

4. 手動テストを実施

5. GitHubでタグを作成（例: `v0.2.0`）

6. VWSマイページに配布用zipをアップロード

## 8. セキュリティ

### 基本方針
- ユーザー入力を受け付けない設計（設定画面なし）
- クエリパラメータ`channel=beta`は公開情報（秘密情報ではない）
- ライセンスサーバー側で認証を実施
- XSS/SQLインジェクション等の一般的な脆弱性に注意

### 管理画面通知
- 出力は`esc_html()`でエスケープ
- プラグイン名などのハードコードされた値のみ表示
- ユーザー入力は含まない

## 9. 国際化（i18n）

- テキストドメイン: `vk-vws-plugin-beta-tester`
- 翻訳ファイルは将来的に追加予定
- 現在は英語と日本語のみサポート

## 10. ベストプラクティス

### DRY原則
- 対応プラグイン設定を配列で一元管理
- ループ処理でフィルターフック登録

### 拡張性
- 新しいプラグイン追加時は配列に1エントリ追加するだけ
- コアロジック（`add_beta_channel()`）は変更不要

### シンプルさ
- 設定画面なし（有効化=ベータ希望）
- 外部依存なし（純粋なPHPのみ）
- データベース使用なし

## 11. 禁止事項

- ❌ 設定画面の追加（初版では不要、将来のバージョンで検討）
- ❌ データベースへの書き込み
- ❌ 外部ライブラリの導入
- ❌ JavaScriptの使用（管理画面通知はPHP/HTMLのみ）
- ❌ ユーザー入力の受け付け

## 12. 関連ドキュメント

- [VK_VWS_BETA_TESTER_SPEC.md](../../../../VK_VWS_BETA_TESTER_SPEC.md) - 仕様書
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
