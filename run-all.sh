#!/bin/bash
# วางปุ่ม Vision CTA (mu-plugin static) ลงทุกเว็บ WordPress บนเครื่อง cPanel นี้
# แก้ config ที่ vision-cta.php บน GitHub -> push -> รันสคริปต์นี้ -> ทุกเว็บอัปเดต
GITHUB_RAW="https://raw.githubusercontent.com/AnonymousVS/PWA-centerwarp/main"
LOG_DIR="/root/vision-cta-logs"
mkdir -p "$LOG_DIR"

# หา ea-php ไว้ lint ไฟล์ก่อนติดตั้ง (กัน syntax error ทำเว็บล่ม)
PHPBIN=$(ls /opt/cpanel/ea-php*/root/usr/bin/php 2>/dev/null | sort | tail -1)
[ -z "$PHPBIN" ] && PHPBIN=$(command -v php 2>/dev/null)

CPU=$(nproc)
RAM_GB=$(free -g | awk '/Mem:/{print $2}')
if   [ "$CPU" -ge 16 ] && [ "$RAM_GB" -ge 32 ]; then JOBS=8
elif [ "$CPU" -ge 8 ]  && [ "$RAM_GB" -ge 16 ]; then JOBS=4
elif [ "$CPU" -ge 4 ]  && [ "$RAM_GB" -ge 8 ];  then JOBS=2
else JOBS=1; fi

echo "======================================"
echo "🖥️  CPU: $CPU cores | RAM: ${RAM_GB}GB | ⚡ Jobs: $JOBS"
echo "======================================"

process_domain() {
  local DOMAIN="$1"; local GITHUB_RAW="$2"; local LOG_DIR="$3"
  local LOG_FILE="$LOG_DIR/run-$(date '+%Y%m%d').log"
  log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $DOMAIN | $1" | tee -a "$LOG_FILE"; }

  local LINE USERNAME WP_PATH MU_DIR TMP
  LINE=$(grep "^$DOMAIN:" /etc/userdatadomains | head -1)
  USERNAME=$(echo "$LINE" | awk -F'==' '{print $1}' | awk -F': ' '{print $2}' | tr -d ' ')
  WP_PATH=$(echo "$LINE" | awk -F'==' '{print $5}')

  if [ -z "$WP_PATH" ];               then log "⏭️ หา path ไม่เจอ - ข้าม"; return; fi
  if [ ! -f "$WP_PATH/wp-config.php" ]; then log "⏭️ ไม่ใช่ WordPress - ข้าม"; return; fi

  MU_DIR="$WP_PATH/wp-content/mu-plugins"
  mkdir -p "$MU_DIR"
  TMP="$MU_DIR/.vision-cta.tmp"

  # ดาวน์โหลดเข้า temp ก่อน + ตรวจไฟล์สมบูรณ์ ค่อยเขียนทับ (กันไฟล์พังทำเว็บล่ม)
  curl -fsS "$GITHUB_RAW/vision-cta.php" -o "$TMP" 2>/dev/null
  if [ -s "$TMP" ] && grep -q '<?php' "$TMP" && grep -q 'vision-cta' "$TMP" \
     && { [ -z "$PHPBIN" ] || "$PHPBIN" -l "$TMP" >/dev/null 2>&1; }; then
    mv -f "$TMP" "$MU_DIR/vision-cta.php"
    chown "$USERNAME":"$USERNAME" "$MU_DIR" 2>/dev/null
    chown "$USERNAME":"$USERNAME" "$MU_DIR/vision-cta.php" 2>/dev/null
    chmod 755 "$MU_DIR"; chmod 644 "$MU_DIR/vision-cta.php"
    # 🧹 purge LiteSpeed page cache ของบัญชี (สำคัญ! LiteSpeed แคช HTML ที่ฝังสคริปต์ inline ไว้ ถ้าไม่ purge เว็บจะเสิร์ฟตัวเก่า)
    find "/home/$USERNAME/lscache" -mindepth 1 -delete 2>/dev/null
    log "✅ สำเร็จ + purge cache: $WP_PATH"
  else
    rm -f "$TMP"
    log "❌ ดาวน์โหลดไม่สมบูรณ์ - ข้าม (ไฟล์เดิมไม่ถูกแตะ)"
  fi
}
export -f process_domain
export GITHUB_RAW LOG_DIR PHPBIN

DOMAINS=$(awk -F'==' '$3=="main" || $3=="addon" {print $1}' /etc/userdatadomains \
  | awk -F': ' '{print $1}' | sort -u)
TOTAL=$(echo "$DOMAINS" | wc -l)
echo "📋 พบ domain ทั้งหมด: $TOTAL"
echo "======================================"

echo "$DOMAINS" | xargs -P "$JOBS" -I {} bash -c 'process_domain "$@"' _ {} "$GITHUB_RAW" "$LOG_DIR"

LOG_FILE="$LOG_DIR/run-$(date '+%Y%m%d').log"
SUCCESS=$(grep -c "✅ สำเร็จ"      "$LOG_FILE" 2>/dev/null || echo 0)
FAILED=$(grep -c "❌"             "$LOG_FILE" 2>/dev/null || echo 0)
SKIPPED=$(grep -c "⏭️"            "$LOG_FILE" 2>/dev/null || echo 0)
echo ""
echo "======================================"
echo "📊 สรุปผล"
echo "======================================"
echo "✅ สำเร็จ           : $SUCCESS เว็บ"
echo "❌ ไม่สำเร็จ         : $FAILED เว็บ"
echo "⏭️  ข้าม (ไม่ใช่ WP) : $SKIPPED เว็บ"
echo "📁 Log: $LOG_DIR/run-$(date '+%Y%m%d').log"
exit 0
