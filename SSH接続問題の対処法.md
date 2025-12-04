# SSH接続ができない問題の対処法

## 現在の状況
- サーバー: さくらのレンタルサーバー（navyracoon2.sakura.ne.jp）
- 接続: タイムアウト
- Ping: 失敗（100%パケットロス）

## 考えられる原因と対処法

### 1. サーバーがダウンしている
**確認方法:**
- さくらのレンタルサーバーのコントロールパネルでサーバーの状態を確認
- サーバーが起動しているか確認

**対処法:**
- さくらのレンタルサーバーのコントロールパネルからサーバーを再起動する

### 2. SSH接続設定の問題
**確認方法:**
- さくらのレンタルサーバーのコントロールパネルでSSH接続設定を確認
- SSH接続が有効になっているか確認

**対処法:**
- `~/.ssh/config` の設定を確認・更新
- または、直接接続を試す: `ssh [ユーザー名]@navyracoon2.sakura.ne.jp`

### 3. ファイアウォール設定
**確認方法:**
- サーバー側でSSHポート（22）が開いているか確認
- ファイアウォールルールを確認

**対処法:**
- 必要に応じてファイアウォールルールを追加

### 4. ネットワーク環境の変更
**確認方法:**
- VPNに接続しているか確認
- プロキシ設定があるか確認
- 別のネットワーク（自宅、オフィスなど）から試す

**対処法:**
- VPNを切断してから試す
- 別のネットワークから接続してみる

### 5. WSL環境からの接続問題
**確認方法:**
- Windows PowerShellから直接SSH接続を試す
- 同じネットワーク内の別端末から試す

**対処法:**
```powershell
# Windows PowerShellから（ユーザー名は実際のものに置き換え）
ssh [ユーザー名]@navyracoon2.sakura.ne.jp
```

## 一時的な対処法

サーバーに接続できない場合でも、コードはGitHubにプッシュ済みなので：

1. サーバー管理画面から直接コマンドを実行（VNCやコンソールアクセス）
2. 別の方法でサーバーにアクセス
3. サーバーが復旧するまで待つ

## コードは既にGitHubにプッシュ済み

サーバーに接続できれば、以下を実行するだけです：

```bash
cd ~/www/ProfileGen
git pull origin main
php artisan migrate --force
php artisan config:clear && php artisan cache:clear && php artisan route:clear
composer dump-autoload
php artisan config:cache && php artisan route:cache
```

## 接続テストコマンド

```bash
# 基本接続テスト（エイリアス使用）
ssh -v sakura "echo '接続成功'"

# 直接接続テスト
ssh -v navyracoon2@navyracoon2.sakura.ne.jp "echo '接続成功'"

# パスワード認証を使用する場合（sshpassが必要）
sshpass -p 'パスワード' ssh -v navyracoon2@navyracoon2.sakura.ne.jp "echo '接続成功'"

# タイムアウトを長めに設定
ssh -o ConnectTimeout=30 sakura

# ポート番号を指定（さくらのレンタルサーバーの場合、通常は22）
ssh -p 22 sakura
# または
ssh -p 22 navyracoon2@navyracoon2.sakura.ne.jp
```

