# Vision CTA Buttons (mu-plugin)

ปุ่มชวน **ติดตั้งแอป (PWA)** บนทุกเว็บ WordPress → พาไปหน้า install ที่ `centerwarp.app`
โมเดลเดียวกับ fast-redirect: **แก้ config ที่หัว `vision-cta.php` บน GitHub → รัน `run-all.sh` → เขียนไฟล์ static ทุกเว็บบนเครื่อง**

## ปุ่มมี 3 จุด (กดแล้วไปกล่อง install ที่ centerwarp.app)
- **แท็บทองลอย** มุมขวา (เดสก์ท็อป `bottom:28%` · มือถือย่อเล็ก)
- **bar บนสุด** ของหน้า
- **popup กลางจอ** (พื้นกระจกฝ้า ขอบทอง) — เด้งทุกครั้งถ้ายังไม่กดติดตั้ง · กดปุ่มติดตั้งแล้วเงียบ 30 วัน

> ปุ่ม/ข้อความทองมีมิติ (bevel + shine + glow) · **ไม่โชว์ในหน้า Customizer/admin** · ไม่โชว์ตอนเปิดจากแอปที่ติดตั้งแล้ว (standalone)

## ทำไม static (ไม่ fetch ตอนรัน)
ไฟล์ `vision-cta.php` ที่วางบนเครื่อง = **static 100%** — ไม่ `file_get_contents(github)`, ไม่มี `window.location.replace`
→ **ไม่เข้าลายเซ็น imunify `php.bkdr.drpr`** (ต่างจาก fast-redirect v11.1 ที่ fetch github ตอนรัน = โดนจับ)
→ ใช้ได้ทั้ง cPanel และ Cloudways โดยไม่ต้อง whitelist (ถ้าเครื่องไหน imunify งอแง ค่อย whitelist เพิ่ม)

## ไฟล์ใน repo
| ไฟล์ | หน้าที่ |
|---|---|
| `vision-cta.php` | mu-plugin (★ แก้ config ที่หัวไฟล์) |
| `run-all.sh` | วางลงทุกเว็บบนเครื่อง (parallel ตาม CPU/RAM) + **purge LiteSpeed cache** ให้เอง |
| `run-single.sh` | วางลง 1 เว็บ (canary/ทดสอบ) — `bash run-single.sh <domain>` |

## config (หัวไฟล์ `vision-cta.php`)
- `install_url` — หน้า install (default `https://centerwarp.app/?action=install`)
- `popup_enabled` / `bar_enabled` — เปิด/ปิด popup กลางจอ / bar บนสุด
- `installed_days` — กดปุ่มติดตั้งแล้วเงียบกี่วัน (default 30 · ⚠️เช็คได้แค่จาก "การกดปุ่ม" cross-origin เช็ค install จริงไม่ได้)
- `popup_delay` — หน่วงก่อน popup เด้ง (ms)

## ยกทั้งฟลีต
1. แก้ config ใน `vision-cta.php` บน GitHub → commit
2. รันบนแต่ละเครื่อง (WHM Terminal / SSH root):
   ```
   curl -s https://raw.githubusercontent.com/AnonymousVS/PWA-centerwarp/main/run-all.sh | bash
   ```

> ⚠️ **run-all.sh purge LiteSpeed cache ให้อัตโนมัติ** (ลบ `/home/<user>/lscache`) — **จำเป็น!** เพราะปุ่มฝัง inline อยู่ใน HTML ที่ LiteSpeed แคชไว้ ถ้าไม่ purge เว็บจะเสิร์ฟสคริปต์ตัวเก่าจนกว่าแคชจะหมดอายุเอง

## ทดสอบก่อน (canary 1 เว็บ)
```
curl -s https://raw.githubusercontent.com/AnonymousVS/PWA-centerwarp/main/run-single.sh | bash -s -- angsaslot.club
```
เปิดเว็บ → เห็นแท็บทองมุมขวา + popup → กดไปหน้า install ที่ centerwarp

## ถอนออก (ถ้าต้องการ)
ลบไฟล์ `wp-content/mu-plugins/vision-cta.php` ของแต่ละเว็บ + purge cache (`find /home/<user>/lscache -mindepth 1 -delete`)
