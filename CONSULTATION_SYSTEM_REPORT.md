# 相談管理システム 現状レポート

## 1. システム構成

### 1.1 ルーティング

#### 生徒用エリア（`/student`）
- **ログイン**: `GET/POST /student/login`
- **ログアウト**: `POST /student/logout`
- **相談一覧**: `GET /student/consultations` → `StudentConsultationController@index`
- **相談投稿**: `POST /student/consultations` → `StudentConsultationController@store`

#### 管理用エリア（`/admin`）
- **ログイン**: `GET/POST /admin/login`
- **ログアウト**: `POST /admin/logout`
- **相談一覧**: `GET /admin/consultations` → `AdminV2ConsultationController@index`
- **回答修正**: `POST /admin/consultations/{consultation}/correct` → `AdminV2ConsultationController@correct`
- **Discord投稿**: `POST /admin/consultations/{consultation}/notify` → `AdminV2ConsultationController@notifyDiscord`

### 1.2 コントローラー

#### 生徒用コントローラー
- **ファイル**: `app/Http/Controllers/Student/ConsultationController.php`
- **主要メソッド**:
  - `index()`: 相談チャット画面を表示（最新20件の相談履歴を表示）
  - `store()`: 相談を保存・回答生成（Gemini APIを使用）

#### 管理用コントローラー
- **ファイル**: `app/Http/Controllers/AdminV2/ConsultationController.php`
- **主要メソッド**:
  - `index()`: 相談履歴一覧を表示（ページネーション対応、修正履歴含む）
  - `correct()`: 回答を修正（correctionsテーブルに保存、Discord通知）
  - `notifyDiscord()`: 質問・回答をDiscordに投稿（手動実行用）

### 1.3 認証・ミドルウェア

#### 生徒用認証
- **ミドルウェア**: `App\Http\Middleware\StudentAuth`
- **認証方式**: セッション認証（`student_authenticated`セッションキー）
- **コントローラー**: `App\Http\Controllers\Student\AuthController`

#### 管理用認証
- **ミドルウェア**: `App\Http\Middleware\AdminV2Auth`
- **認証方式**: セッション認証（`adminv2_authenticated`セッションキー）
- **コントローラー**: `App\Http\Controllers\AdminV2\AuthController`

### 1.4 モデル構造

#### Consultation（相談）
- **テーブル**: `consultations`
- **カラム**:
  - `id`: BIGINT UNSIGNED (PRIMARY KEY)
  - `question`: TEXT (質問文)
  - `answer`: TEXT (回答文)
  - `user_id`: BIGINT UNSIGNED (nullable, FOREIGN KEY → users.id)
  - `is_corrected`: BOOLEAN (default: false, 修正済みフラグ)
  - `created_at`, `updated_at`: TIMESTAMP
- **リレーション**:
  - `user()`: BelongsTo (User)
  - `corrections()`: HasMany (Correction)

#### Correction（修正履歴）
- **テーブル**: `corrections`
- **カラム**:
  - `id`: BIGINT UNSIGNED (PRIMARY KEY)
  - `consultation_id`: BIGINT UNSIGNED (FOREIGN KEY → consultations.id)
  - `wrong_answer`: TEXT (間違った回答)
  - `correct_answer`: TEXT (正しい回答)
  - `corrected_by`: VARCHAR (nullable, 修正者名)
  - `created_at`, `updated_at`: TIMESTAMP
- **リレーション**:
  - `consultation()`: BelongsTo (Consultation)

#### Article（ナレッジベース）
- **テーブル**: `articles`
- **カラム**:
  - `id`: BIGINT UNSIGNED (PRIMARY KEY)
  - `title`: VARCHAR(255) (タイトル)
  - `content`: LONGTEXT (本文)
  - `url`: VARCHAR(2048) (nullable, 元URL)
  - `image_urls`: JSON (nullable, 画像URL配列)
  - `created_at`, `updated_at`: TIMESTAMP
- **管理機能**: `ArticleController`（`/articles`ルート、認証必須）
  - 一覧・登録・編集・削除機能
  - HTMLパース機能
  - URL抽出機能

## 2. 実装済み機能

### 2.1 生徒用画面の機能

#### 相談投稿機能
- ✅ 質問文の入力（最大2000文字）
- ✅ 同じ質問の重複チェック（正規化して完全一致チェック）
- ✅ Gemini APIによる自動回答生成
- ✅ ナレッジベース（Article）の活用
  - 全記事の本文を結合してプロンプトに含める
- ✅ 過去の間違い事例（Correction）の活用
  - 最新50件の修正履歴をプロンプトに含める
- ✅ Discord通知（設定でONの場合）
- ✅ 相談履歴の表示（最新20件）

#### UI機能
- ✅ チャット形式の表示（質問：右側、回答：左側）
- ✅ ローディング表示
- ✅ エラーメッセージ表示
- ✅ 既存質問の警告表示

### 2.2 管理用画面の機能

#### 相談履歴管理
- ✅ 相談履歴一覧表示（ページネーション対応）
- ✅ 修正履歴の表示（`corrections`リレーション）
- ✅ 修正済みフラグの表示
- ✅ 質問・回答のプレビュー（100文字で切り詰め）

#### 回答修正機能
- ✅ モーダルで修正フォームを表示
- ✅ 元の質問・回答の表示
- ✅ 正しい回答の入力
- ✅ 修正者名の入力（任意）
- ✅ `corrections`テーブルへの保存
- ✅ `is_corrected`フラグの更新
- ✅ Discord通知（修正通知）

#### Discord投稿機能
- ✅ 手動でDiscordに投稿（`notifyDiscord`メソッド）

### 2.3 ナレッジベース機能

#### ナレッジの登録・管理
- ✅ **実装済み**: `ArticleController`でナレッジ（記事）の登録・管理が可能
- ✅ ルート: `/articles`（認証必須、`auth`ミドルウェア）
- ✅ 機能:
  - 一覧表示
  - 新規登録（HTMLパース機能付き）
  - 編集
  - 削除
  - URL抽出機能

#### ナレッジの活用
- ✅ **生徒用画面**: 相談回答生成時に全記事の本文をプロンプトに含める
- ✅ **管理用画面**: **ナレッジ参照機能は未実装**

## 3. 未実装・課題

### 3.1 管理用画面でのナレッジ機能

- [ ] **課題1**: 管理用画面（`/admin/consultations`）からナレッジ（Article）にアクセスできない
  - 現状: 
    - ナレッジ管理機能（`/articles`）は`auth`ミドルウェアで保護されている
    - 管理用画面（`/admin`）は`AdminV2Auth`ミドルウェアを使用（別の認証システム）
    - 管理用画面にはナレッジ管理機能へのリンクやルートがない
  - 影響: 管理画面でナレッジを参照・編集できない
  - 解決策: 
    - 管理用画面にナレッジ管理機能へのルートを追加（`/admin/articles`）
    - または、`AdminV2Auth`ミドルウェアで`/articles`ルートにもアクセス可能にする

- [ ] **課題2**: 相談返答時にナレッジを参照する機能がない
  - 現状: 管理用画面で相談を確認する際、関連するナレッジを参照できない
  - 影響: 回答の精度向上やナレッジの更新判断が困難
  - 解決策: 相談詳細画面で関連ナレッジを表示する機能を追加

- [ ] **課題3**: ナレッジと相談の関連付けがない
  - 現状: `consultations`テーブルと`articles`テーブルにリレーションがない
  - 影響: どのナレッジがどの相談で使われたか追跡できない
  - 解決策: 中間テーブルまたは関連付け機能の実装

### 3.2 機能の不完全な部分

- [ ] **課題4**: 相談詳細画面がない
  - 現状: 管理用画面は一覧のみで、個別の相談詳細画面がない
  - 影響: 相談の全文を確認するには一覧画面のプレビュー（100文字）しか見られない
  - 解決策: 相談詳細画面（`/admin/consultations/{id}`）を追加

- [ ] **課題5**: ナレッジ検索機能がない
  - 現状: ナレッジ（Article）の検索機能が実装されていない
  - 影響: 大量のナレッジから特定の情報を見つけるのが困難
  - 解決策: タイトル・本文での検索機能を追加

- [ ] **課題6**: 相談回答の改善提案機能がない
  - 現状: 管理画面で回答を修正する機能はあるが、ナレッジを基にした改善提案機能がない
  - 影響: 回答の品質向上が手動のみ
  - 解決策: ナレッジを参照して回答を改善する機能を追加

### 3.3 データフローの課題

- [ ] **課題7**: ナレッジ更新時の相談への影響が不明
  - 現状: ナレッジを更新しても、既存の相談回答は更新されない
  - 影響: 古いナレッジに基づいた回答が残る可能性
  - 解決策: ナレッジ更新時の相談への影響を明確化（または再生成機能）

- [ ] **課題8**: 相談とナレッジの関連度が不明
  - 現状: どのナレッジがどの相談で使われたか分からない
  - 影響: ナレッジの重要度や使用頻度が分からない
  - 解決策: 相談生成時に使用したナレッジを記録する機能

## 4. 次のアクション

### 優先度：高

1. **管理用画面にナレッジ管理機能へのアクセスを追加**
   - `/admin/articles`ルートを追加
   - 管理用画面のナビゲーションに「ナレッジ管理」リンクを追加
   - 管理用認証（`AdminV2Auth`）で保護

2. **相談詳細画面の実装**
   - `GET /admin/consultations/{id}`ルートを追加
   - `AdminV2ConsultationController@show`メソッドを実装
   - 相談の全文表示、修正履歴の表示、関連ナレッジの表示

3. **相談返答時にナレッジを参照する機能**
   - 相談詳細画面で関連ナレッジを表示
   - ナレッジ検索機能の実装（相談返答時に活用）

### 優先度：中

4. **ナレッジ検索機能の実装**
   - タイトル・本文での全文検索
   - 管理用画面とナレッジ管理画面の両方で利用可能に

5. **ナレッジと相談の関連付け機能**
   - 相談生成時に使用したナレッジを記録
   - 中間テーブル（`consultation_articles`）の作成
   - 相談詳細画面で関連ナレッジを表示

### 優先度：低

6. **ナレッジ更新時の相談への影響を明確化**
   - ナレッジ更新時に影響を受ける相談を一覧表示
   - 必要に応じて相談回答を再生成する機能

7. **相談回答の改善提案機能**
   - ナレッジを参照して回答を改善する提案機能
   - 管理画面でワンクリックで改善案を生成

## 5. 技術スタック

- **バックエンド**: Laravel 12, PHP 8.2+
- **フロントエンド**: React 18, Inertia.js, TypeScript, Tailwind CSS
- **AI API**: Google Gemini API (gemini-2.5-flash)
- **データベース**: MySQL 8.0
- **認証**: セッション認証（カスタムミドルウェア）

## 6. 補足情報

### ナレッジベースの管理場所

- **コントローラー**: `app/Http/Controllers/ArticleController.php`
- **ルート**: `/articles`（認証必須、`auth`ミドルウェア）
- **モデル**: `app/Models/Article.php`
- **テーブル**: `articles`
- **マイグレーション**: `2025_11_30_021111_create_articles_table.php`

### 相談機能のデータフロー

```
1. 生徒が質問を投稿
   ↓
2. 重複チェック（既存質問の場合は既存回答を返す）
   ↓
3. 新規質問の場合:
   - 全記事（Article）の本文を取得
   - 過去の修正履歴（Correction）を取得
   - プロンプトを生成
   - Gemini APIで回答生成
   ↓
4. Consultationテーブルに保存
   ↓
5. Discord通知（設定でONの場合）
   ↓
6. 管理画面で確認・修正可能
```

### 修正機能のデータフロー

```
1. 管理画面で回答を修正
   ↓
2. Correctionテーブルに保存（wrong_answer, correct_answer）
   ↓
3. Consultationのis_correctedをtrueに更新
   ↓
4. Discord通知（修正通知）
   ↓
5. 次回の相談回答生成時に修正履歴を参照
```

