# Template Part Shortcode
 
テーマで用意したテンプレートをショートコードにしてリッチテキストエディタに追加するプラグインです。  

## Description
テーマ内にtemplate-parts/shortcodeディレクトリを作成してそのディレクトリ内にテンプレートファイルを追加して下さい。  
テンプレートファイルの先頭には必ず以下のようにコメントを追加して下さい。

<pre>
/**
 * Template Label: Sample(任意のラベル）
 */
</pre> 

上記をファイルを設置すると投稿画面のリッチテキストエディタにテンプレートコードという項目が追加されます。

テンプレートコード項目にはtemplate-parts/shortcodeディレクトリ内に作成したテンプレートの「Template Label」一覧が表示されます。

ラベルをクリックすると本文にテンプレートのショートコードが挿入されます。

投稿を保存すると挿入されたショートコードが展開されてテーマで作成したテンプレートが表示されます。


## 翻訳ファイルの生成（開発者向け）
[WP-CLI](https://wp-cli.org/) を使って POT ファイルを生成します。

<pre>
wp i18n make-pot . languages/template-part-shortcode.pot --exclude=bin
</pre>

翻訳後の PO ファイルから MO ファイルを生成するには以下を実行します。

<pre>
wp i18n make-mo languages
</pre>
