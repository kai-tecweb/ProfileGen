#!/usr/bin/env python3
"""DBホスト名を更新するスクリプト"""

import re

env_file = '.env'
new_host = 'mysql80.navyracoon2.sakura.ne.jp'

try:
    with open(env_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # DB_HOSTを更新
    content = re.sub(r'^DB_HOST=.*', f'DB_HOST={new_host}', content, flags=re.MULTILINE)
    
    with open(env_file, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f'✓ DB_HOSTを {new_host} に更新しました')
    print('\n現在のDB設定:')
    for line in content.split('\n'):
        if line.startswith('DB_'):
            print(f'  {line}')
    
except Exception as e:
    print(f'エラー: {e}')
    exit(1)

