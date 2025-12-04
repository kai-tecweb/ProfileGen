# ProfileGenプロジェクト 現状調査レポート

調査日時: 2025年12月4日

## 1. データベーステーブル一覧

### 主要テーブル

#### articles（記事/ナレッジ）
- `id` (bigint, primary key)
- `title` (string, 255)
- `content` (longText)
- `url` (string, nullable) - 追加カラム
- `image_urls` (json, nullable) - 追加カラム
- `created_at` (timestamp)
- `updated_at` (timestamp)
- **インデックス**: `created_at`

#### consultations（相談履歴）
- `id` (bigint, primary key)
- `question` (text)
- `answer` (text)
- `user_id` (foreignId, nullable) - usersテーブルへの外部キー
- `is_corrected` (boolean, default: false)
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### corrections（修正履歴）
- `id` (bigint, primary key)
- `consultation_id` (foreignId) - consultationsテーブルへの外部キー
- `wrong_answer` (text)
- `correct_answer` (text)
- `corrected_by` (string, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

#### clients（クライアント）
- `id` (bigint, primary key)
- `name` (string, 255)
- `company` (string, 255, nullable)
- `memo` (text, nullable) - クライアント情報・特徴
- `questionnaire_text` (text, nullable) - 追加カラム
- `answers_text` (text, nullable) - 追加カラム
- `created_at` (timestamp)
- `updated_at` (timestamp)
- **インデックス**: `created_at`

#### proposals（提案）
- `id` (bigint, primary key)
- `client_id` (foreignId) - clientsテーブルへの外部キー
- `x_profile` (text)
- `instagram_profile` (text)
- `coconala_profile` (text)
- `product_design` (text)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- **インデックス**: `client_id`, `created_at`

#### questions（質問マスター）
- `id` (bigint, primary key)
- `question` (text) - 質問文
- `sort_order` (integer, default: 0) - 表示順
- `created_at` (timestamp)
- `updated_at` (timestamp)
- **インデックス**: `sort_order`

#### users（ユーザー）
- Laravel標準のusersテーブル

#### システムテーブル
- `cache` - キャッシュ用
- `jobs` - ジョブキュー用

## 2. ルート一覧

### 認証済みユーザー向けルート（`auth`, `verified`ミドルウェア）

#### ダッシュボード
- `GET /dashboard` → `DashboardController@index`

#### 記事管理（旧システム）
- `GET /articles` → `ArticleController@index`
- `POST /articles` → `ArticleController@store`
- `GET /articles/create` → `ArticleController@create`
- `GET /articles/{article}` → `ArticleController@show`
- `PUT/PATCH /articles/{article}` → `ArticleController@update`
- `DELETE /articles/{article}` → `ArticleController@destroy`
- `GET /articles/{article}/edit` → `ArticleController@edit`

#### クライアント管理
- `GET /clients` → `ClientController@index`
- `POST /clients` → `ClientController@store`
- `GET /clients/create` → `ClientController@create`
- `GET /clients/{client}` → `ClientController@show`
- `PUT/PATCH /clients/{client}` → `ClientController@update`
- `DELETE /clients/{client}` → `ClientController@destroy`
- `GET /clients/{client}/edit` → `ClientController@edit`
- `POST /clients/{client}/proposals/generate` → `ProposalController@generate`

#### 提案管理
- `GET /proposals/{proposal}` → `ProposalController@show`
- `PUT /proposals/{proposal}` → `ProposalController@update`
- `DELETE /proposals/{proposal}` → `ProposalController@destroy`
- `GET /proposals/{proposal}/edit` → `ProposalController@edit`

#### 質問マスター管理
- `GET /questions` → `QuestionController@index`
- `POST /questions` → `QuestionController@store`
- `GET /questions/create` → `QuestionController@create`
- `POST /questions/generate` → `QuestionController@generate`
- `POST /questions/regenerate` → `QuestionController@regenerate`
- `GET /questions/{question}` → `QuestionController@show`
- `PUT/PATCH /questions/{question}` → `QuestionController@update`
- `DELETE /questions/{question}` → `QuestionController@destroy`
- `GET /questions/{question}/edit` → `QuestionController@edit`

#### プロフィール
- `GET /profile` → `ProfileController@edit`
- `PATCH /profile` → `ProfileController@update`
- `DELETE /profile` → `ProfileController@destroy`

### 管理エリア（`AdminV2Auth`ミドルウェア）

#### 認証
- `GET /admin/login` → `AdminV2\AuthController@showLoginForm`
- `POST /admin/login` → `AdminV2\AuthController@login`
- `POST /admin/logout` → `AdminV2\AuthController@logout`

#### 相談履歴管理
- `GET /admin/consultations` → `AdminV2\ConsultationController@index`
- `POST /admin/consultations/{consultation}/correct` → `AdminV2\ConsultationController@correct`
- `POST /admin/consultations/{consultation}/notify` → `AdminV2\ConsultationController@notifyDiscord`

#### ナレッジ管理（相談チャット用）
- `GET /admin/consultation-knowledge` → `Admin\ConsultationKnowledgeController@index`

#### ナレッジ管理（旧記事管理システム）
- `GET /admin/articles` → `ArticleController@index`

### 学生エリア（`StudentAuth`ミドルウェア）

#### 認証
- `GET /student/login` → `Student\AuthController@showLoginForm`
- `POST /student/login` → `Student\AuthController@login`
- `POST /student/logout` → `Student\AuthController@logout`

#### 相談チャット
- `GET /student/consultations` → `Student\ConsultationController@index`
- `POST /student/consultations` → `Student\ConsultationController@store`

## 3. コントローラー一覧

### Adminコントローラー
- `app/Http/Controllers/Admin/ConsultationKnowledgeController.php`
  - `index()` - 相談チャット用ナレッジ一覧表示

### AdminV2コントローラー
- `app/Http/Controllers/AdminV2/ConsultationController.php`
  - `index()` - 相談履歴一覧表示
  - `correct()` - 回答修正
  - `notifyDiscord()` - Discord投稿

### Studentコントローラー
- `app/Http/Controllers/Student/ConsultationController.php`
  - `index()` - 相談チャット表示
  - `store()` - 相談投稿・AI回答生成

### その他のコントローラー
- `ArticleController` - 記事管理（CRUD）
- `ClientController` - クライアント管理（CRUD）
- `ProposalController` - 提案管理
- `QuestionController` - 質問マスター管理
- `DashboardController` - ダッシュボード
- `ProfileController` - プロフィール管理

## 4. ビューファイル（React/Inertia.js）

### Adminエリア
- `resources/js/Pages/Admin/ConsultationKnowledge/Index.tsx` - ナレッジ管理画面
- `resources/js/Pages/Admin/Consultations/Index.tsx` - 相談履歴管理画面

### レイアウト
- `resources/js/Layouts/AdminV2Layout.tsx` - 管理エリア用レイアウト
- `resources/js/Layouts/AuthenticatedLayout.tsx` - 認証済みユーザー用レイアウト
- `resources/js/Layouts/StudentLayout.tsx` - 学生エリア用レイアウト

### その他のページ
- `resources/js/Pages/Articles/` - 記事管理画面
- `resources/js/Pages/Clients/` - クライアント管理画面
- `resources/js/Pages/Proposals/` - 提案管理画面
- `resources/js/Pages/Questions/` - 質問マスター管理画面
- `resources/js/Pages/Dashboard.tsx` - ダッシュボード
- `resources/js/Pages/Profile/` - プロフィール管理画面

## 5. 実装されている機能

### 記事/ナレッジ管理機能
1. **記事管理（旧システム）**
   - 記事のCRUD操作
   - HTMLからの自動抽出機能
   - URL抽出機能
   - 画像URL抽出機能
   - OCR機能（画像内テキスト抽出）

2. **ナレッジ管理（相談チャット用）**
   - ナレッジ一覧表示
   - ページネーション対応
   - URL表示・リンク機能

### 相談管理機能
1. **学生側**
   - 相談チャット画面
   - 質問投稿
   - AI回答生成（Gemini API使用）
   - ナレッジベースからの回答生成

2. **管理側**
   - 相談履歴一覧表示
   - 回答修正機能
   - 修正履歴保存
   - Discord通知機能

### クライアント管理機能
- クライアントのCRUD操作
- アンケート機能
- 提案生成機能

### 提案管理機能
- 提案の表示・編集・削除
- AI生成提案機能

### 質問マスター管理機能
- 質問のCRUD操作
- AI生成質問機能
- 再生成機能

### 認証機能
- 通常ユーザー認証（Laravel標準）
- 管理エリア認証（`AdminV2Auth`）
- 学生エリア認証（`StudentAuth`）

## 6. 技術スタック

### バックエンド
- Laravel 12
- PHP 8.2以上
- MySQL 8.0

### フロントエンド
- React 18
- Inertia.js
- TypeScript
- Tailwind CSS
- Vite

### 外部サービス
- Google Gemini API（AI回答生成）
- Discord API（通知機能）

## 7. 主要なサービス

- `GeminiApiService` - Gemini API連携
- `ImageService` - 画像処理
- `DiscordService` - Discord通知

## 8. 注意事項

### ルートの重複
- `/admin/articles` - 旧記事管理システム（`ArticleController@index`）
- `/admin/consultation-knowledge` - 相談チャット用ナレッジ管理（`ConsultationKnowledgeController@index`）

両方とも`articles`テーブルを使用していますが、用途が異なります。

### ナビゲーション
- `AdminV2Layout`の「ナレッジ管理」リンクは`/admin/consultation-knowledge`を指しています
- 旧記事管理システムは`/admin/articles`からアクセス可能

