# SSH接続ができない問題の対処法

## 現在の状況
- サーバーIP: 163.44.126.95
- 接続: タイムアウト
- Ping: 失敗（100%パケットロス）

## 考えられる原因と対処法

### 1. サーバーがダウンしている
**確認方法:**
- サーバーの管理画面（ConoHa VPSなど）でサーバーの状態を確認
- サーバーが起動しているか確認

**対処法:**
- サーバーを再起動する

### 2. サーバーのIPアドレスが変更された
**確認方法:**
- サーバー管理画面で現在のIPアドレスを確認
- 以前のIPアドレスと比較

**対処法:**
- `~/.ssh/config` のIPアドレスを更新
- または、新しいホスト名を使用

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
# Windows PowerShellから
ssh root@163.44.126.95 -i ~/.ssh/conoha_vps
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
# 基本接続テスト
ssh -v conoha "echo '接続成功'"

# タイムアウトを長めに設定
ssh -o ConnectTimeout=30 conoha

# ポート番号を指定（もし変更されている場合）
ssh -p 22 conoha
```

