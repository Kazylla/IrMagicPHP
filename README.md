# IrMagicPHP
## IrMagicPHPとは
IrMagicPHPは、irMagicianを操作するためのPHP APIです。  
http://www.omiya-giken.com/?page_id=889  
に記載のある一通りのコマンドをAPIとして実装しています。  

## 簡単な操作
以下のようなプログラムを実行するだけで、赤外線リモコンのデータのキャプチャおよび再生ができます。
### キャプチャ
```
$ir = new IrMagic();
$bytes = $ir->execCapture('capture.json');
```

### 再生
```
$ir = new IrMagic();
$ir->execPlay('capture.json');
```

### 気温取得
```
$ir = new IrMagic();
$temp = $ir->temperature();
```

## 確認済み環境
開発にあたって検証に用いた環境は以下のとおりです。  
- irMagician T
- Raspberry Pi Model B+
- Raspberry Pi Raspbian Wheezy(2015-05-05)
- PHP 5.6.20-0+deb8u1 (cli) (built: Apr 28 2016 00:01:26)

シリアル入出力を行うために、peclのdioを使用していますので、実行にはdioのインストールが必要です。  
https://pecl.php.net/package/dio

## アーキテクチャー

以下の3クラスの継承関係により、段階的にハイレベルな処理の実行が可能になるように構成しています。

### Serial.php
irMagicianを使用したシリアル入出力に関する、最低限のシリアル通信機能を実装しています。  

### Ir.php
irMagicianのコマンドと1:1となるAPIを実装しています。  
irMagicianのコマンドと同レベルの処理を行う場合は、このクラスを用います。  

### IrMagic.php
さらに上位レベルの処理として、キャプチャ、赤外線送信、状態の取得などの機能を提供します。   
通常はこのクラスを利用して開発を行うことになります。  
もし、キャプチャデータの保存方法をjson以外にしたい、または例えば、ファイルではなくDBに保存したい場合は、本クラスを継承し、以下のメソッドを継承して実装を行います。  
- loadDataInternal()
- saveDataInternal()
