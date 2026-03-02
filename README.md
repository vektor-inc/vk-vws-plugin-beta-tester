# VK VWS Plugin Beta Tester

VWS（Vektor WordPress Solutions）プラグインのベータ版を受け取るためのテスター用プラグイン。

[![License: GPL v2 or later](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 概要

**VK VWS Plugin Beta Tester**は、VK Blocks Pro、Lightning Proなどの VWS プラグインのベータ版を受け取り、テストするためのプラグインです。このプラグインを有効化すると、通常の安定版ではなく、ベータ版の更新を受け取ることができます。

## 機能

- VK Blocks Proのベータ版を受信
- シンプルな有効化のみで動作（設定不要）
- 将来的に他のVWSプラグイン（Lightning Proなど）にも対応予定
- 安全に無効化可能（次の安定版が出るまで現在のバージョンのまま）

## 対応プラグイン

- ✅ VK Blocks Pro
- 🔜 その他VWSプラグイン(もし追加する時があれば)

## インストール

### 方法1: VWSマイページからダウンロード（推奨）

1. [VWS マイページ](https://vws.vektor-inc.co.jp/my-page/)にログイン
2. ベータテスター用プラグインをダウンロード
3. WordPressの管理画面 → プラグイン → 新規追加 → プラグインのアップロード
4. ダウンロードしたzipファイルをアップロードして有効化

### 方法2: GitHubから手動インストール

```bash
cd wp-content/plugins/
git clone https://github.com/vektor-inc/vk-vws-plugin-beta-tester.git
```

WordPressの管理画面でプラグインを有効化してください。

## 使い方

1. プラグインを有効化
2. WordPress管理画面 → プラグイン で「更新を確認」をクリック
3. 対応プラグインのベータ版が利用可能な場合、更新通知が表示されます

### 安定版に戻す方法

このプラグインを無効化すると、次回の更新チェックから安定版の情報が返されます。ただし、WordPressは自動的にダウングレードしないため、次の安定版がリリースされるまで現在のバージョンのままです。

## 重要な注意事項

⚠️ **ベータ版にはバグや未完成の機能が含まれる可能性があります**
⚠️ **本番環境では使用しないでください**
⚠️ **ベータ版はテスト目的でのみ提供されます**

## 仕組み

このプラグインは、対応するVWSプラグインの更新チェッククエリに`channel=beta`パラメータを追加します。ライセンスサーバーはこのパラメータを検出すると、安定版ではなくベータ版の`download_url`を返します。

### 技術的な詳細

VWSプラグインは、更新チェック時に以下のフィルターフックを提供しています：

- VK Blocks Pro: `vk_blocks_pro_vws_update_check_query_args`
- Lightning Pro: `lightning_pro_vws_update_check_query_args`（今後対応）

このプラグインは、これらのフィルターにフックして`channel=beta`を追加します。

## 開発者向け情報

### システム要件

- WordPress 6.5以上
- PHP 7.4以上
- 対応するVWSプラグイン（VK Blocks Proなど）がインストール済み

### フィルター規約

他のVWSプラグインを対応させる場合、以下の規約に従ってフィルターフックを実装してください：

**フィルター名の形式:**
```php
{プラグインスラッグ}_vws_update_check_query_args
```

**例:**
```php
// VK Blocks Pro
apply_filters( 'vk_blocks_pro_vws_update_check_query_args', $query_args );

// Lightning Pro
apply_filters( 'lightning_pro_vws_update_check_query_args', $query_args );
```

### プラグインへの対応追加

`vk-vws-plugin-beta-tester.php`の`$supported_plugins`配列に追加してください：

```php
private $supported_plugins = array(
	'your-plugin-slug' => array(
		'name'        => 'Your Plugin Name',
		'filter_hook' => 'your_plugin_slug_vws_update_check_query_args',
	),
);
```

## バグ報告

ベータ版で問題を見つけた場合、以下の方法で報告してください：

- **GitHub Issues**（推奨）: [vk-blocks-pro/issues](https://github.com/vektor-inc/vk-blocks-pro/issues)
- **VWS サポートフォーラム**: [VWS フォーラム](https://vws.vektor-inc.co.jp/forums/)
- **Discord コミュニティ**: [Vektor Discord](https://discord.gg/vektor)

## よくある質問

### Q: プラグインを無効化したらどうなりますか？

A: 次回の更新チェックから安定版の情報が返されます。ただし、WordPressは自動的にダウングレードしないため、次の安定版がリリースされるまで現在のバージョンのままです。

### Q: どのプラグインが対応していますか？

A: 現在はVK Blocks Proのみ対応しています。今後、Lightning Proなど他のVWSプラグインにも対応予定です。

### Q: ベータ版と安定版を切り替えられますか？

A: はい。このプラグインを有効化/無効化することで切り替えられます。ただし、ダウングレードは次の安定版リリースまで行われません。

### Q: ライセンスキーは必要ですか？

A: はい。対応するVWSプラグインの有効なライセンスキーが必要です。ベータ版も安定版と同じライセンスで利用できます。

## ライセンス

GPL-2.0-or-later

## クレジット

開発: [Vektor,Inc.](https://vektor-inc.co.jp)
仕様設計: [Issue #2497](https://github.com/vektor-inc/vk-blocks-pro/issues/2497)

## 関連リンク

- [VK Blocks Pro](https://vws.vektor-inc.co.jp/product/vk-blocks-pro/)
- [Lightning Pro](https://vws.vektor-inc.co.jp/product/lightning-pro/)
- [Vektor WordPress Solutions](https://vws.vektor-inc.co.jp/)
