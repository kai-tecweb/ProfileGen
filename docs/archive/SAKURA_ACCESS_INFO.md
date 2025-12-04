# さくらサーバー接続情報

## SSH接続情報

### ホスト名
```
navyracoon2.sakura.ne.jp
```

### ユーザー名
```
navyracoon2
```

### パスワード
```
NPkwGwZu=NM2
```

## SSH接続コマンド

```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
```

パスワードを聞かれたら上記のパスワードを入力してください。

## データベース情報

### ホスト名
```
mysql80.navyracoon2.sakura.ne.jp
```

### データベース名
```
navyracoon2_a1
```

### ユーザー名
```
navyracoon2_a1
```

### パスワード
```
12345678aa
```

## ドメイン/URL

```
https://navyracoon2.sakura.ne.jp
```

## ディレクトリ構成

### プロジェクトルート
```
~/www/ProfileGen
/home/navyracoon2/www/ProfileGen
```

### ドキュメントルート
```
~/www
/home/navyracoon2/www
```

### シンボリックリンク
```
~/www/index.php → ProfileGen/public/index.php
~/www/.htaccess → ProfileGen/public/.htaccess
~/www/build → ProfileGen/public/build
```

## Gemini API Key

```
AIzaSyA7Vgyc4WrdjCJ3AorYBbVTItLgT_k5BBw
```

## よく使うコマンド

### SSH接続（パスワード自動入力）
```bash
sshpass -p "NPkwGwZu=NM2" ssh navyracoon2@navyracoon2.sakura.ne.jp
```

### ファイルアップロード
```bash
sshpass -p "NPkwGwZu=NM2" scp file.txt navyracoon2@navyracoon2.sakura.ne.jp:~/www/ProfileGen/
```

### ディレクトリアップロード
```bash
sshpass -p "NPkwGwZu=NM2" scp -r directory/ navyracoon2@navyracoon2.sakura.ne.jp:~/www/ProfileGen/
```

