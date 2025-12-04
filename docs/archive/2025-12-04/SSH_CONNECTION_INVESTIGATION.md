# SSH接続方法の調査結果

## 調査日時
2024年12月4日

## 調査内容

### 1. SSH鍵ファイルの確認
`~/.ssh/`ディレクトリ内の鍵ファイル：
- `id_ed25519` / `id_ed25519.pub` - デフォルトのEd25519鍵
- `conoha_vps` / `conoha_vps.pub` - 旧設定（使用しない）
- `conoha_vps_simple` / `conoha_vps_simple.pub` - 旧設定（使用しない）
- `kai-vps-key2.pem` - 他のVPS用
- `posl-production-key.pem` - 他のプロジェクト用
- `syusei-meishi-key.pem` - 他のプロジェクト用

### 2. SSH設定ファイルの確認
- `~/.ssh/config`: さくらのレンタルサーバー用に更新済み
  - `Host sakura` → `navyracoon2.sakura.ne.jp` (ユーザー: `navyracoon2`, ポート: 22)
- `~/.ssh/config.backup`: 旧設定（使用しない）

### 3. 接続履歴の確認
- `history | grep "ssh.*sakura"`: 結果なし
- `history | grep "navyracoon"`: 結果なし
- `~/.ssh/known_hosts`: `navyracoon2.sakura.ne.jp`のエントリが存在（過去に接続した記録あり）

### 4. 接続テスト結果

#### 試行した接続方法
1. `ssh -i ~/.ssh/id_ed25519 navyracoon2@navyracoon2.sakura.ne.jp` → **失敗** (Permission denied)
2. `ssh -i ~/.ssh/kai-vps-key2.pem navyracoon2@navyracoon2.sakura.ne.jp` → **失敗** (Permission denied)
3. `ssh -i ~/.ssh/syusei-meishi-key.pem navyracoon2@navyracoon2.sakura.ne.jp` → **失敗** (Permission denied)
4. `ssh -i ~/.ssh/posl-production-key.pem navyracoon2@navyracoon2.sakura.ne.jp` → **失敗** (Permission denied)
5. `ssh -o PreferredAuthentications=password navyracoon2@navyracoon2.sakura.ne.jp` → **失敗** (ssh_askpassエラー)

#### 接続状況
- サーバーとの接続は確立されている（`Connection established`）
- 認証が失敗している（`Permission denied (publickey,password)`）
- すべての鍵ファイルで認証に失敗
- パスワード認証も失敗（`ssh_askpass`が存在しないため対話的入力不可）

## 結論

### 現在の状況
1. **サーバー接続**: 成功（TCP接続は確立）
2. **認証**: 失敗（鍵認証・パスワード認証ともに失敗）
3. **過去の接続記録**: `known_hosts`に記録あり（過去に接続成功した記録）

### 考えられる原因
1. **正しいSSH鍵ファイルが存在しない**
   - さくらのレンタルサーバー用の専用鍵が`~/.ssh/`に存在しない可能性
   - 鍵ファイルが別の場所にある可能性

2. **パスワード認証が必要**
   - さくらのレンタルサーバーではパスワード認証のみが有効になっている可能性
   - WSL環境では`ssh_askpass`がないため、対話的パスワード入力ができない

3. **鍵ファイルの権限問題**
   - 鍵ファイルの権限が正しく設定されていない可能性（ただし、すべて`600`または`400`で適切）

4. **サーバー側の設定変更**
   - さくらのレンタルサーバー側でSSH設定が変更された可能性
   - 鍵認証が無効化された可能性

## 推奨される対処法

### 方法1: 正しいSSH鍵を取得・設定
1. さくらのレンタルサーバーのコントロールパネルでSSH鍵を確認
2. 正しい鍵ファイルを`~/.ssh/`に配置
3. `~/.ssh/config`に鍵ファイルのパスを指定

### 方法2: パスワード認証で接続（手動）
WSL環境ではなく、Windows PowerShellやターミナルから直接接続：
```powershell
ssh navyracoon2@navyracoon2.sakura.ne.jp
```
パスワードを対話的に入力できる環境で接続を試す。

### 方法3: さくらのレンタルサーバーのコントロールパネルを使用
1. さくらのレンタルサーバーのコントロールパネルにログイン
2. SSH接続設定を確認
3. 新しいSSH鍵を生成・登録
4. 鍵ファイルをダウンロードして`~/.ssh/`に配置

### 方法4: 別の接続方法を検討
- さくらのレンタルサーバーのコントロールパネルから直接コマンド実行（可能な場合）
- FTP/SFTPクライアントでファイルをアップロード
- GitHub ActionsやCI/CDを使用した自動デプロイ

## 次のステップ

1. **さくらのレンタルサーバーのコントロールパネルでSSH設定を確認**
   - SSH接続が有効になっているか
   - 鍵認証の設定を確認
   - パスワード認証の設定を確認

2. **正しいSSH鍵を取得**
   - コントロールパネルから鍵をダウンロード
   - または、新しい鍵ペアを生成

3. **接続テスト**
   - 取得した鍵で接続を試す
   - または、Windows環境からパスワード認証で接続

4. **接続成功後、デプロイを実行**
   ```bash
   ssh sakura
   または
   ssh navyracoon2@navyracoon2.sakura.ne.jp
   cd ~/www/ProfileGen
   git pull origin main
   npm run build
   php artisan config:clear && php artisan cache:clear && php artisan route:clear
   php artisan config:cache && php artisan route:cache
   ```

## 参考情報

- サーバー: さくらのレンタルサーバー（`navyracoon2.sakura.ne.jp`）
- ユーザー名: `navyracoon2`
- ポート: `22`
- プロジェクトパス: `~/www/ProfileGen`
- Gitリポジトリ: `git@github.com:kai-tecweb/ProfileGen.git`

