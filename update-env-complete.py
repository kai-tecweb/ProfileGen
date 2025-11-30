#!/usr/bin/env python3
"""完全な.envファイル設定スクリプト"""

import re

env_file = '.env'

# 更新する値
updates = {
    'DB_HOST': 'mysql80.navyracoon2.sakura.ne.jp',
    'DB_DATABASE': 'navyracoon2_a1',
    'DB_USERNAME': 'navyracoon2',
    'DB_PASSWORD': '12345678aa',
    'GEMINI_API_KEY': 'AIzaSyA7Vgyc4WrdjCJ3AorYBbVTItLgT_k5BBw',
    'APP_URL': 'https://navyracoon2.sakura.ne.jp',
    'APP_ENV': 'production',
    'APP_DEBUG': 'false',
}

try:
    # .envファイルを読み込み
    with open(env_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 各行を処理
    lines = content.split('\n')
    updated_lines = []
    updated_keys = set()
    
    for line in lines:
        # コメント行や空行はそのまま
        if not line.strip() or line.strip().startswith('#'):
            updated_lines.append(line)
            continue
        
        # KEY=VALUE 形式の行を処理
        if '=' in line:
            key = line.split('=')[0].strip()
            if key in updates:
                # 値を更新
                updated_lines.append(f"{key}={updates[key]}")
                updated_keys.add(key)
            else:
                updated_lines.append(line)
        else:
            updated_lines.append(line)
    
    # 更新されなかったキーを追加
    for key, value in updates.items():
        if key not in updated_keys:
            updated_lines.append(f"{key}={value}")
    
    # .envファイルに書き込み
    with open(env_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(updated_lines))
    
    print(f"✓ .envファイルを更新しました")
    print(f"\n更新された設定:")
    for key in ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'GEMINI_API_KEY', 'APP_URL', 'APP_ENV', 'APP_DEBUG']:
        if key in updates:
            value = updates[key]
            if key == 'DB_PASSWORD' or key == 'GEMINI_API_KEY':
                print(f"  {key}=***")
            else:
                print(f"  {key}={value}")
    
except FileNotFoundError:
    print(f"エラー: {env_file} が見つかりません")
    exit(1)
except Exception as e:
    print(f"エラー: {e}")
    exit(1)

