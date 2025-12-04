# 画面遷移問題の修正

## 問題の状況

- コンソールやネットワークではエラーがない
- 302リダイレクトは発生している
- しかし画面が更新されない（リロードされない）

## 実施した修正

### 1. Inertia.jsのフラッシュメッセージ共有

✅ `HandleInertiaRequests.php`でフラッシュメッセージを共有するように修正：
```php
'flash' => [
    'success' => $request->session()->get('success'),
    'error' => $request->session()->get('error'),
],
```

### 2. フロントエンドのエラーハンドリング改善

✅ `Questions/Index.tsx`を完全に書き直し：
- `usePage`フックでフラッシュメッセージを取得
- ローディング状態の表示を追加
- エラーメッセージの表示を改善
- `preserveScroll: false`でスクロール位置をリセット

### 3. エラーハンドリングの統一

✅ `QuestionController`のエラーハンドリングを`with('error', ...)`に統一

## 確認事項

ブラウザで「質問マスター管理」画面を開き、「質問を生成」ボタンをクリックしてください。

### 正常な動作

1. ボタンが「生成中...」に変わる
2. ローディングスピナーが表示される
3. 質問が生成されたら、自動的に画面が更新される
4. 成功メッセージが表示される（3秒後に消える）

### エラーが発生した場合

1. エラーメッセージが画面に表示される
2. 詳細なエラー情報がログに記録される

## まだ動作しない場合

最新のエラーログを確認：
```bash
ssh navyracoon2@navyracoon2.sakura.ne.jp
cd ~/www/ProfileGen
tail -50 storage/logs/laravel.log
```

