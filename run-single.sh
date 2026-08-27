#!/bin/bash
# วางปุ่ม Vision CTA ลง 1 เว็บ (canary/ทดสอบ)
# ใช้:  bash run-single.sh <domain>     (ไม่ใส่ = สุ่ม 1 เว็บ)
GITHUB_RAW="https://raw.githubusercontent.com/AnonymousVS/PWA-centerwarp/main"
LOG_DIR="/root/vision-cta-logs"; mkdir -p "$LOG_DIR"

DOMAIN="$1"
if [ -z "$DOMAIN" ]; then
  echo "🔍 ไม่ได้ระบุโดเมน → สุ่ม 1 เว็บ WordPress..."
  WP_DOMAINS=()
  while IFS= read -r line; do
    D=$(echo "$line" | awk -F': ' '{print $1}' | tr -d ' ')
    P=$(echo "$line" | awk -F'==' '{print $5}')
    [ -f "$P/wp-config.php" ] && WP_DOMAINS+=("$D")
  done < <(awk -F'==' '$3=="main" || $3=="addon"' /etc/userdatadomains)
  TOTAL=${#WP_DOMAINS[@]}
  [ "$TOTAL" -eq 0 ] && { echo "❌ ไม่พบเว็บ WordPress"; exit 1; }
  DOMAIN="${WP_DOMAINS[$((RANDOM % TOTAL))]}"
  echo "🎲 สุ่มได้: $DOMAIN (จาก $TOTAL เว็บ)"
fi

LINE=$(grep "^$DOMAIN:" /etc/userdatadomains | head -1)
USERNAME=$(echo "$LINE" | awk -F'==' '{print $1}' | awk -F': ' '{print $2}' | tr -d ' ')
WP_PATH=$(echo "$LINE" | awk -F'==' '{print $5}')

if [ -z "$WP_PATH" ] || [ ! -f "$WP_PATH/wp-config.php" ]; then
  echo "❌ ไม่พบ WordPress ที่: $DOMAIN ($WP_PATH)"; exit 1
fi
echo "✅ username : $USERNAME"
echo "✅ path     : $WP_PATH"

PHPBIN=$(ls /opt/cpanel/ea-php*/root/usr/bin/php 2>/dev/null | sort | tail -1)
[ -z "$PHPBIN" ] && PHPBIN=$(command -v php 2>/dev/null)

MU_DIR="$WP_PATH/wp-content/mu-plugins"; mkdir -p "$MU_DIR"
TMP="$MU_DIR/.vision-cta.tmp"
curl -fsS "$GITHUB_RAW/vision-cta.php" -o "$TMP" 2>/dev/null

if [ -s "$TMP" ] && grep -q '<?php' "$TMP" && grep -q 'vision-cta' "$TMP" \
   && { [ -z "$PHPBIN" ] || "$PHPBIN" -l "$TMP" >/dev/null 2>&1; }; then
  mv -f "$TMP" "$MU_DIR/vision-cta.php"
  chown "$USERNAME":"$USERNAME" "$MU_DIR" 2>/dev/null
  chown "$USERNAME":"$USERNAME" "$MU_DIR/vision-cta.php" 2>/dev/null
  chmod 755 "$MU_DIR"; chmod 644 "$MU_DIR/vision-cta.php"
  echo "✅ วางไฟล์สำเร็จ: $MU_DIR/vision-cta.php"
  echo "[$(date '+%F %T')] run-single | $DOMAIN | $WP_PATH" >> "$LOG_DIR/run-$(date '+%Y%m%d').log"
  echo ""
  echo "🎯 เปิดทดสอบ: https://$DOMAIN/  (ดูปุ่มลอยมุมขวา)"
else
  rm -f "$TMP"; echo "❌ ดาวน์โหลดไฟล์ไม่สมบูรณ์"; exit 1
fi
exit 0
