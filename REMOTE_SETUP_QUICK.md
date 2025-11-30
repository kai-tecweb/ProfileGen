# リモートリポジトリ設定（簡易版）

## クイックスタート

### 1. GitHubでリポジトリを作成

1. [GitHub](https://github.com/new)で新しいリポジトリを作成
2. リポジトリ名: `ProfileGen`（任意）
3. **「Initialize this repository with a README」はチェックしない**
4. 「Create repository」をクリック

### 2. リモートリポジトリを設定

#### オプションA: 対話式スクリプトを使用（推奨）

```bash
bash setup-remote.sh
```

#### オプションB: 手動で設定

```bash
# GitHubの場合（SSH - 推奨）
git remote add origin git@github.com:your-username/ProfileGen.git

# GitHubの場合（HTTPS）
git remote add origin https://github.com/your-username/ProfileGen.git

# 設定確認
git remote -v
```

### 3. 初回プッシュ

```bash
git push -u origin main
```

## 次のステップ

プッシュ後、さくらサーバーで以下を実行：

```bash
# サーバーにSSH接続
ssh username@server.sakura.ne.jp

# リポジトリをクローン
cd ~/www
git clone git@github.com:your-username/ProfileGen.git
cd ProfileGen

# デプロイ
bash deploy-sakura.sh
```
