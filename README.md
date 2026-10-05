# WP Date Range Exporter

![概要画像ヘッダー](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/WP_DRE_header.png)

## 概要

wp-date-range-exporter は、WordPressの記事を指定した条件で取得・一覧表示・エクスポートするためのプラグインです。
個人での利用・開発を目的として作成したものですが、グループ内での共有・利用を想定してGitHub上で公開しています。

WordPress管理画面から操作できるため、データベースを直接操作することなく、対象となる記事を確認できます。

### 想定している用途と解決できること

- 指定した期間の記事を確認したい
- 過去の記事をまとめて一覧化したい
- 手作業で記事を探す手間を減らしたい
- 標準機能のXML形式だと扱いづらい

## 主な機能

### 記事の取得

指定した条件に基づいて、WordPressに登録されている記事(post)を取得します。

### 日付範囲の指定

開始日・終了日などを指定して、対象となる記事を絞り込むことができます。
レポート用に四半期の範囲指定ボタンで四半期範囲を指定することができます。

### エクスポート

取得した記事情報を、指定した形式(csv,マークダウン,text 詳細は後述)で出力できます。

---

## 動作環境

本プラグインは以下の環境を想定しています。インストール前にご確認ください。

| 項目 | バージョン |
| :--- | :--- |
| **WordPress** | 7.0.1 以上 |
| **PHP** | 8.4 以上推奨（7.2以降対応） |

---

## インストール方法
### 1. GitHubからダウンロード

以下のリンクをクリックして、プラグインのZIPファイルを直接ダウンロードしてください。

 [wp-date-range-exporter.zip をダウンロード](https://github.com/Tsukumo-999/wp-date-range-exporter/raw/main/releases/wp-date-range-exporter.zip)

※上記リンクからダウンロードできない場合は、リポジトリの `releases` フォルダ内にある `wp-date-range-exporter.zip` を手動でダウンロードしてください。

### 2. WordPressへインストール

WordPress管理画面の左側メニューから **「プラグイン」** を開き、以下の手順でZIPファイルをアップロードします。

#### ① 「プラグインを追加」をクリック
画面上部にある「プラグインを追加」ボタンをクリックします。
![プラグインを追加](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/wp-plugin-zip-add.png)

#### ② 「プラグインのアップロード」をクリック
画面が切り替わったら、タイトルの横にある「プラグインのアップロード」ボタンをクリックします。
![プラグインのアップロード](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/wp-plugin-zip-upload.png)

#### ③ ZIPファイルを選択してインストール
「ファイルを選択」をクリックし、先ほどダウンロードした `wp-date-range-exporter.zip` を選びます。その後、**「今すぐインストール」** をクリックしてください。
![ZIPファイルのインストール](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/wp-plugin-select-zip.png)


### 3. プラグインを有効化

インストールが完了したら、WordPressのプラグイン一覧から wp-date-range-exporter を有効化します。
![プラグインの有効化](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/enable-plugin.png)

プラグインを有効化すると、WordPress管理画面から本プラグインの機能を利用できます。

---

## 使い方
### 1. 操作画面を開く

WordPress管理画面(ツール>記事エクスポート)から、プラグインの操作画面を開きます。

![ダッシュボード](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/dashboard_tools_scs.png)


### 2. 取得条件を指定

取得したい記事の条件を指定します。
「期間をクイック選択」ボタンを使って四半期や1年をワンクリックで指定するか、開始日と終了日をカレンダーで直接指定します。

![取得範囲の設定](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/tools_ui_scs.png)

### 3. エクスポート形式を指定

エクスポート形式は、以下の4種類に対応しています。出力データは、（投稿日、記事タイトル、記事リンク）の形式です。
- 1. csv (excel対応)
- 2. Markdown形式（リスト）
- 3. Markdown形式（テーブル）
- 4. プレーンテキスト(カンマ区切り)

出力形式の指定は、操作画面のドロップダウンより選択することが可能です。

![エクスポート形式の指定](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/export-formats.png)

### 4. 記事一覧をエクスポート

必要な記事を確認した後、エクスポート機能を使用してデータを出力しダウンロードすることができます。

![設定後エクスポート](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/export-sample.png)


### 💡 各フォーマットの出力サンプル
用途に合わせて最適な形式を選択してください。
![エクスポート形式一覧](https://raw.githubusercontent.com/Tsukumo-999/wp-date-range-exporter/main/docs/images/export-formats-dtails.png)


---

## どのような仕組みで記事を取得しているのか？
 
 本プラグインは、WordPressのデータベースを直接操作するのではなく、WordPress標準のクエリ機能（WP_Query）を利用して安全に記事データを検索・取得しています。

### 取得条件と仕様
- 対象データ: 「投稿（post）」のみ
- ステータス: 「公開済み（publish）」のみ（※下書きや非公開、ゴミ箱の記事は含まれません）
- 並び順: 公開日の古い順
- 取得項目: 「公開日」「記事タイトル」「URL」の3点のみ（※本文、記事ID、カテゴリーなどは取得しません）


### 処理の流れ
1. 管理画面で「抽出期間」と「出力形式」を指定して実行

2. WordPressの標準機能で、期間内に該当する公開済みの記事を検索

3. 対象記事から必要な項目（公開日・タイトル・URL）だけを抽出

4. 画面への一覧表示は行わず、指定された形式のファイル（CSV, Markdown, テキスト）として直接ダウンロード

--- 

## リポジトリのファイル構成

現在のリポジトリには、主に以下のファイル・ディレクトリが含まれています。
```text
wp-date-range-exporter/
├── wp-date-range-exporter.php
├── README.md
├── LICENSE
├── docs/ (README用の画像など)
└── releases/
    └── wp-date-range-exporter.zip
```

### 主なファイル
| ファイル / ディレクトリ | 内容 |
|---|---|
| `wp-date-range-exporter.php` | プラグイン本体のソースコード |
| `README.md` | 本ドキュメント |
| `releases/wp-date-range-exporter.zip` | WordPressへのインストールに使用するプラグイン本体のZIPファイル |


## 注意事項

> * 本プラグインはWordPress公式プラグインではありません。個人製作の非公式プラグインです。
> * 利用する環境によっては正常に動作しない場合があります。
> * 本番環境で使用する場合は、事前に十分な動作確認を行ってください。
> * 大量の記事を取得する場合、サーバーの負荷が高くなる可能性があります。


## 動作環境

以下の環境を想定しています。

| 項目 | バージョン |
| :--- | :--- |
| **WordPress** | 7.0.1 以上 |
| **PHP** | 8.4 以上推奨（7.2以降対応） |


## 開発について

GitHubリポジトリおよびWordPressプラグインの名称には、機能が分かりやすいよう `wp-date-range-exporter` を使用しています。

### 製作者

**lumenHero** (Tukumo)

- **GitHub:** [Tsukumo-999](https://github.com/Tsukumo-999)
- **X (Twitter):** [@tukumolog](https://x.com/tukumolog)
- **Websites:** 
  - [TUKUMO工房](https://tukumolog.com/)
  - [METRIC](https://tukumolog.topaz.ne.jp/)

## License

**lumenHero Custom License**

本プラグインの著作権は作成者（lumenHero）に帰属します。
個人・商用を問わず無償でのご利用やカスタマイズが可能ですが、**本プラグイン（改変したものを含む）を自身の著作物として公開・主張する行為や、無断での二次配布・販売は固く禁止**しております。

詳細な条項については [LICENSE](./LICENSE) ファイルを参照してください。

## Disclaimer

本プラグインの利用によって発生したデータの損失、サイトの不具合、その他の問題について、製作者は責任を負いません。 AsISの提供となります。

利用者自身の責任において、バックアップや動作確認を行ったうえで使用してください。