# Vision CTA Buttons (mu-plugin)

ปุ่มลอย **[ติดตั้งแอป] [LINE] [Telegram]** บนทุกเว็บ WordPress
โมเดลเดียวกับ fast-redirect: **แก้ config บน GitHub → รัน `run-all.sh` → เขียนไฟล์ static ทุกเว็บบนเครื่อง**

## ทำไม static (ไม่ fetch ตอนรัน)
ไฟล์ `vision-cta.php` ที่วางบนเครื่อง = **static 100%** — ไม่ `file_get_contents(github)`, ไม่มี `window.location.replace`
→ **ไม่เข้าลายเซ็น imunify `php.bkdr.drpr`** (ต่างจาก fast-redirect v11.1 ที่ fetch github ตอนรัน = โดนจับ)
→ ใช้ได้ทั้ง cPanel และ Cloudways โดยไม่ต้อง whitelist (ถ้าเครื่องไหน imunify งอแง ค่อย whitelist เพิ่ม)

## ไฟล์ใน repo
| ไฟล์ | หน้าที่ |
|---|---|
| `vision-cta.php` | mu-plugin (★ แก้ config LINE/TG/install ที่หัวไฟล์) |
| `run-all.sh` | วางลงทุกเว็บบนเครื่อง (parallel ตาม CPU/RAM) |
| `run-single.sh` | วางลง 1 เว็บ (canary/ทดสอบ) — `bash run-single.sh <domain>` |
| `README.md` | ไฟล์นี้ |

## ตั้งค่าครั้งแรก
1. แก้ `GITHUB_RAW=` ใน `run-all.sh` + `run-single.sh` ให้ชี้ repo นี้ (`AnonymousVS/PWA-centerwarp`)
2. แก้ config ใน `vision-cta.php` (`install_url`, `line_url`, `tg_url`)
3. push ขึ้น GitHub

## เปลี่ยนลิงก์ LINE/Telegram (ทั้งฟลีต)
1. แก้ค่าใน `vision-cta.php` บน GitHub → commit
2. รันบนแต่ละเครื่อง (WHM Terminal / SSH root):
   ```
   curl -s https://raw.githubusercontent.com/AnonymousVS/PWA-centerwarp/main/run-all.sh | bash
   ```
3. Purge LiteSpeed/CF ถ้าจำเป็น (ปุ่มอยู่ใน wp_head — ปกติ page cache รอบใหม่ก็ขึ้นเอง)

## ทดสอบก่อน (canary 1 เว็บ)
```
curl -s https://raw.githubusercontent.com/AnonymousVS/PWA-centerwarp/main/run-single.sh | bash -s -- angsaslot.club
```
เปิดเว็บ → เห็นปุ่มลอยมุมขวา → กด [ติดตั้งแอป] ไปหน้า install / [LINE][Telegram] เปิดแชต

## ถอนออก (ถ้าต้องการ)
ลบไฟล์ `wp-content/mu-plugins/vision-cta.php` ของแต่ละเว็บ (เขียนสคริปต์ลบคล้าย run-all ได้)
