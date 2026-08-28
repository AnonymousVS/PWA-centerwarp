<?php
/*
Plugin Name: Vision CTA Install Button + Popup
Description: ปุ่มลอย "ติดตั้งแอป" (แท็บทอง) + popup เด้งกลางจอชวนติดตั้ง → centerwarp — static 100% imunify-safe
Version: 2.4
Text Domain: vision-cta
*/
if (!defined('ABSPATH')) { return; }

/* ★ แก้ที่เดียว แล้วรัน run-all.sh */
$VISION_CTA = array(
    'enabled'        => true,
    'install_url'    => 'https://centerwarp.app/?action=install',
    'icon_url'       => 'https://centerwarp.app/icon-192.png',
    'popup_enabled'  => true,
    'popup_cooldown' => 6,   // (ปิดใช้แล้ว v2.3) เดิม=กันเด้งซ้ำทุก 6 ชม. · ตอนนี้: ยังไม่ติดตั้ง = เด้งทุกครั้งที่เข้า
    'popup_delay'    => 1200, // ms — หน่วงก่อนเด้ง (ให้หน้าโหลดก่อน)
    'installed_days' => 30,  // วัน — กดปุ่มติดตั้งแล้ว popup+bar เงียบกี่วัน (ถอน/เกินกำหนด = เด้งกลับ) · ⚠️เช็คได้แค่จากการกดปุ่ม
    'bar_enabled'    => true, // แถบชวนติดตั้งด้านบน
    'bar_cooldown'   => 12,  // (ปิดใช้แล้ว v2.4) เดิม=ปิด bar แล้วไม่โผล่ซ้ำ 12ชม. · ตอนนี้ bar เหมือน popup: ยังไม่ติดตั้ง=โผล่ทุกครั้ง
);

add_action('wp_head', function () use ($VISION_CTA) {
    if (empty($VISION_CTA['enabled']) || empty($VISION_CTA['install_url'])) { return; }
    if (is_admin() || is_customize_preview()) { return; }   // ไม่โชว์ใน Customizer/หน้า admin (Customizer โหลดหน้าเว็บจริงใน iframe)
    echo "\n<script data-no-optimize=\"1\" data-cfasync=\"false\">\n";
    echo 'window.__VISION_CTA=' . wp_json_encode($VISION_CTA) . ";\n";
    echo <<<'JS'
(function () {
  var CFG = window.__VISION_CTA; if (!CFG || !CFG.enabled || !CFG.install_url) return;
  if (window.__visionCTAdone) return; window.__visionCTAdone = true;

  // 🧪 รีเซ็ต flag ทดสอบ — เข้า URL ...?vcreset แล้ว bar/popup กลับมาเด้งใหม่
  try { if (/[?&]vcreset\b/i.test(location.search)) { ['vc_bar_last', 'vc_pop_last', 'vc_installed'].forEach(function (k) { localStorage.removeItem(k); }); } } catch (e) {}
  var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone;
  if (standalone) return;

  // โหลดฟอนต์ Prompt (inject ตอนรัน — LiteSpeed ลบ <link> ใน HTML แต่ไม่แตะ runtime)
  try { if (!document.getElementById('vc-font')) { var fl = document.createElement('link'); fl.id = 'vc-font'; fl.rel = 'stylesheet'; fl.href = 'https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap'; document.head.appendChild(fl); } } catch (e) {}

  // จำว่า "เคยกดติดตั้ง" (heuristic แทนการเช็คข้ามโดเมนที่เบราว์เซอร์ไม่ให้ทำ)
  function markInstalled() { try { localStorage.setItem('vc_installed', String(Date.now())); } catch (e) {} }

  var css = ''
    // ---- แท็บทอง (สไตล์ #uvw-tab) ----
    + '#vc-install{position:fixed;z-index:99991;right:0;top:auto;bottom:28%;'
    + 'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;overflow:hidden;'
    + 'width:80px;height:78px;border-radius:16px 0 0 16px;text-decoration:none;'
    + 'border:1px solid rgba(255,236,180,.45);border-right:0;cursor:pointer;color:#2a1c04;'
    + 'background:linear-gradient(145deg,#f7d05c 0%,#d69a22 46%,#9c6727 100%);'
    + 'box-shadow:inset 0 2px 0 rgba(255,248,220,.75),inset 2px 0 0 rgba(255,245,205,.3),inset 0 -3px 8px rgba(90,55,5,.4),-3px 2px 10px rgba(214,154,34,.32),-2px 2px 8px rgba(0,0,0,.5);'
    + 'font-family:"Prompt","Sarabun",-apple-system,"Segoe UI","Leelawadee UI",Tahoma,sans-serif;'
    + 'font-weight:700;font-size:13.5px;line-height:1.2;letter-spacing:.2px;text-align:center;'
    + '-webkit-tap-highlight-color:transparent;animation:vcInstallGlow 3s ease-in-out infinite;'
    + 'transition:width .2s ease,height .2s ease,filter .16s}'
    + '#vc-install .vc-lb{white-space:nowrap}'
    + '#vc-install svg{width:31px;height:31px;fill:currentColor;display:block}'
    + '#vc-install::after{content:"";position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;'
    + 'background:linear-gradient(115deg,transparent 34%,rgba(255,255,255,.6) 50%,transparent 66%);'
    + 'transform:translateX(-130%);animation:vcShine 3s ease-in-out infinite}'
    + '@keyframes vcShine{0%,58%{transform:translateX(-130%)}82%,100%{transform:translateX(130%)}}'
    + '@keyframes vcInstallGlow{0%,100%{box-shadow:inset 0 2px 0 rgba(255,248,220,.75),inset 2px 0 0 rgba(255,245,205,.3),inset 0 -3px 8px rgba(90,55,5,.4),-3px 2px 10px rgba(214,154,34,.26),-2px 2px 8px rgba(0,0,0,.5)}'
    + '50%{box-shadow:inset 0 2px 0 rgba(255,248,220,.75),inset 2px 0 0 rgba(255,245,205,.3),inset 0 -3px 8px rgba(90,55,5,.4),-3px 2px 14px rgba(242,178,2,.45),-2px 2px 8px rgba(0,0,0,.5)}}'
    + '#vc-install:hover{width:88px;height:86px;filter:brightness(1.08);animation:none}'
    + '#vc-install:active{width:84px;height:82px}'
    + '@media(max-width:600px){#vc-install{top:auto;bottom:150px;transform:none;width:56px;height:64px;border-radius:13px 0 0 13px;font-size:9.5px;gap:3px}'
    + '#vc-install:hover{width:56px;height:64px}#vc-install svg{width:21px;height:21px}}'
    // ---- popup เด้งกลางจอ ----
    + '#vc-pop{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px;'
    + 'background:rgba(6,9,20,.45);opacity:0;transition:opacity .25s ease;'
    + 'font-family:"Prompt","Sarabun",-apple-system,"Segoe UI",Tahoma,sans-serif}'
    + '#vc-pop.on{opacity:1}'
    + '#vc-pop-card{position:relative;width:100%;max-width:330px;text-align:center;color:#fff;'
    + 'background:linear-gradient(180deg,rgba(40,48,82,.55),rgba(16,21,44,.48));-webkit-backdrop-filter:blur(20px) saturate(1.35);backdrop-filter:blur(20px) saturate(1.35);border:1px solid rgba(255,216,124,.55);border-radius:20px;'
    + 'padding:26px 22px 20px;box-shadow:0 24px 70px rgba(0,0,0,.5),0 0 44px rgba(255,178,60,.16),inset 0 1px 0 rgba(255,255,255,.14);'
    + 'transform:scale(.9) translateY(10px);transition:transform .3s cubic-bezier(.2,.9,.3,1.35)}'
    + '#vc-pop.on #vc-pop-card{transform:scale(1) translateY(0)}'
    + '#vc-pop-x{position:absolute;top:8px;right:12px;background:0;border:0;color:#8b95ad;font-size:26px;line-height:1;cursor:pointer;padding:2px 6px}'
    + '#vc-pop-x:hover{color:#fff}'
    + '#vc-pop-ic{width:80px;height:80px;border-radius:19px;margin:4px auto 13px;display:flex;align-items:center;justify-content:center;'
    + 'background:rgba(0,0,0,.3);color:#c1a058;'
    + 'box-shadow:0 8px 22px rgba(0,0,0,.5),0 0 0 1px rgba(255,202,92,.3),0 0 26px rgba(255,168,55,.28)}'
    + '#vc-pop-ic svg{width:44px;height:44px;fill:currentColor;display:block}'
    + '#vc-pop-card h3{margin:0 0 7px;font-size:1.32rem;font-weight:700}'
    + '#vc-pop-card p{margin:0 0 18px;font-size:.92rem;color:#c6cee0;line-height:1.65}'
    + '.vc-pop-cta{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:15px;border-radius:13px;line-height:1;transition:transform .16s ease,filter .16s ease;'
    + 'text-decoration:none;font-family:inherit;font-weight:700;font-size:1.1rem;color:#20140a;'
    + 'background:linear-gradient(145deg,#f7d05c 0%,#d69a22 46%,#9c6727 100%);'
    + 'box-shadow:inset 0 2px 0 rgba(255,248,220,.75),inset 0 -3px 9px rgba(90,55,5,.45),0 6px 16px rgba(214,154,34,.42),0 3px 10px rgba(0,0,0,.5);'
    + 'animation:vcPopGlow 1.9s ease-in-out infinite}'
    + '.vc-pop-cta:hover,.vc-pop-cta:focus,.vc-pop-cta:active,.vc-pop-cta:visited{color:#20140a !important;text-decoration:none !important}'
    + '.vc-pop-cta:hover,.vc-pop-cta:focus{transform:translateY(-2px) scale(1.02);filter:brightness(1.08)}'
    + '.vc-pop-cta:active{transform:translateY(0) scale(.99);filter:brightness(.97)}'
    + '.vc-pop-cta::after{content:"";position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;'
    + 'background:linear-gradient(115deg,transparent 33%,rgba(255,255,255,.65) 50%,transparent 67%);'
    + 'transform:translateX(-130%);animation:vcBtnShine 2.8s ease-in-out infinite}'
    + '@keyframes vcBtnShine{0%,55%{transform:translateX(-130%)}80%,100%{transform:translateX(130%)}}'
    + '@keyframes vcPopGlow{0%,100%{box-shadow:inset 0 2px 0 rgba(255,248,220,.75),inset 0 -3px 9px rgba(90,55,5,.45),0 6px 16px rgba(214,154,34,.34),0 3px 10px rgba(0,0,0,.5)}50%{box-shadow:inset 0 2px 0 rgba(255,248,220,.75),inset 0 -3px 9px rgba(90,55,5,.45),0 6px 24px rgba(242,178,2,.62),0 3px 10px rgba(0,0,0,.5)}}'
    + '.vc-pop-later{display:block;margin:13px auto 2px;background:0;border:0;color:#9fb0d8;font-size:.86rem;cursor:pointer;font-family:inherit}'
    + '.vc-pop-later:hover{color:#fff}'
    + '#vc-pop-card .vc-pop-note{margin:11px 0 0;font-size:.72rem;color:#8b95ad;line-height:1.45}'
    + '.vc-dlic{width:16px;height:16px;fill:currentColor;flex:0 0 auto}'
    + '.vc-pop-cta .vc-dlic{width:19px;height:19px}'
    // ---- แถบชวนติดตั้งด้านบน ----
    + '#vc-bar{position:relative;z-index:2147483000;display:flex;align-items:center;gap:9px;font-family:"Prompt","Sarabun",sans-serif;'
    + 'padding:5px 12px;background:#000}'
    + '#vc-bar .vb-ic-img{flex:0 0 auto;display:flex;align-items:center;color:#c1a058}'
    + '#vc-bar .vb-ic-img svg{display:block;width:22px;height:22px;fill:currentColor}'
    + '#vc-bar .vb-tx{flex:1;min-width:0;color:#fff;line-height:1.2}'
    + '#vc-bar .vb-tx b{display:block;font-size:12.5px;font-weight:700}'
    + '#vc-bar .vb-tx i{font-style:normal;font-size:10px;color:#b9c2d8}'
    + '#vc-bar .vb-cta{position:relative;overflow:hidden;flex:0 0 auto;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;line-height:1;transition:transform .15s ease,filter .15s ease;'
    + 'padding:7px 13px;border-radius:9px;font-family:inherit;font-weight:700;font-size:12px;color:#20140a;white-space:nowrap;'
    + 'background:linear-gradient(145deg,#f7d05c 0%,#d69a22 46%,#9c6727 100%);'
    + 'box-shadow:inset 0 1px 0 rgba(255,248,220,.7),inset 0 -2px 5px rgba(90,55,5,.4),0 2px 8px rgba(214,154,34,.4)}'
    + '#vc-bar .vb-cta::after{content:"";position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;background:linear-gradient(115deg,transparent 33%,rgba(255,255,255,.6) 50%,transparent 67%);transform:translateX(-130%);animation:vcBtnShine 2.8s ease-in-out infinite}'
    + '#vc-bar .vb-cta:hover{transform:translateY(-1px);filter:brightness(1.08)}#vc-bar .vb-cta:active{transform:translateY(0);filter:brightness(.97)}'
    + '#vc-bar .vc-dlic{width:15px;height:15px}'
    + '#vc-bar .vb-x{flex:0 0 auto;background:0;border:0;color:#8b95ad;font-size:22px;line-height:1;cursor:pointer;padding:0 3px}'
    + '#vc-bar .vb-x:hover{color:#fff}'
    + '@media(max-width:600px){#vc-bar{padding:5px 9px;gap:7px}#vc-bar .vb-ic-img svg{width:20px;height:20px}'
    + '#vc-bar .vb-tx b{font-size:11.5px}#vc-bar .vb-tx i{font-size:9.5px}#vc-bar .vb-cta{padding:6px 10px;font-size:11px}}'
    + '@media(prefers-reduced-motion:reduce){#vc-install,#vc-install::after,.vc-pop-cta,.vc-pop-cta::after,#vc-bar .vb-cta::after{animation:none}#vc-bar{transition:none}}';

  function injectCSS() {
    if (document.getElementById('vc-style')) return;
    var s = document.createElement('style'); s.id = 'vc-style'; s.textContent = css; document.head.appendChild(s);
  }

  // ---- ปุ่มแท็บทอง ----
  function buildTab() {
    if (document.getElementById('vc-install')) return;
    var a = document.createElement('a');
    a.id = 'vc-install'; a.href = CFG.install_url; a.target = '_blank'; a.rel = 'noopener'; a.setAttribute('aria-label', 'ติดตั้งแอป');
    a.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a1 1 0 0 1 1 1v9.59l3.29-3.3a1 1 0 1 1 1.42 1.42l-5 5a1 1 0 0 1-1.42 0l-5-5a1 1 0 1 1 1.42-1.42L11 13.59V4a1 1 0 0 1 1-1zM5 19a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1z"></path></svg><span class="vc-lb">ติดตั้งแอป</span>';
    a.addEventListener('click', markInstalled);
    document.body.appendChild(a);
  }

  // ---- popup เด้งกลางจอ ----
  function showPopup() {
    if (!CFG.popup_enabled || document.getElementById('vc-pop')) return;
    try { var inst = +localStorage.getItem('vc_installed') || 0;
      if (inst && Date.now() - inst < (CFG.installed_days || 30) * 86400000) return; } catch (e) {}   // ⭐ กดติดตั้งแล้ว = เงียบ N วัน (ถอนแล้วเด้งกลับมาใน N วัน) — ไม่มี cooldown เวลา: ยังไม่ติดตั้ง = เด้งทุกครั้งที่เข้า
    var ov = document.createElement('div'); ov.id = 'vc-pop';
    ov.innerHTML = '<div id="vc-pop-card"><button id="vc-pop-x" aria-label="ปิด">&times;</button>'
      + '<span id="vc-pop-ic"><svg viewBox="0 0 24 24"><path d="M17 1.01 7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"></path></svg></span>'
      + '<h3>ติดตั้งแอป</h3>'
      + '<p>เข้าเล่นเร็วขึ้น · ติดตั้งไว้หน้าจอโฮม<br>ไม่ต้องพิมพ์ลิงก์ทุกครั้ง</p>'
      + '<a class="vc-pop-cta" href="' + CFG.install_url + '" target="_blank" rel="noopener"><svg class="vc-dlic" viewBox="0 0 24 24"><path d="M12 3a1 1 0 0 1 1 1v9.59l3.29-3.3a1 1 0 1 1 1.42 1.42l-5 5a1 1 0 0 1-1.42 0l-5-5a1 1 0 1 1 1.42-1.42L11 13.59V4a1 1 0 0 1 1-1zM5 19a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1z"></path></svg>ติดตั้งเลย</a>'
      + '<button class="vc-pop-later" type="button">ไว้ทีหลัง</button>'
      + '<p class="vc-pop-note">เมื่อติดตั้งแล้ว หน้าต่างนี้จะไม่แสดงอีก</p></div>';
    document.body.appendChild(ov);
    setTimeout(function () { ov.classList.add('on'); }, 30);   // setTimeout เชื่อถือได้กว่า rAF (ทำงานแม้แท็บ background)
    function close() { ov.classList.remove('on'); setTimeout(function () { if (ov.parentNode) ov.parentNode.removeChild(ov); }, 280); }
    ov.querySelector('#vc-pop-x').addEventListener('click', close);
    ov.querySelector('.vc-pop-later').addEventListener('click', close);
    ov.querySelector('.vc-pop-cta').addEventListener('click', markInstalled);   // กดติดตั้ง = จำไว้ ไม่เด้งซ้ำ 30 วัน
    ov.addEventListener('click', function (e) { if (e.target === ov) close(); });
  }

  // ---- แถบชวนติดตั้งด้านบน (ดันเนื้อหาลง ไม่ทับ header) ----
  function buildBar() {
    if (!CFG.bar_enabled || document.getElementById('vc-bar')) return;
    try { var inst = +localStorage.getItem('vc_installed') || 0;
      if (inst && Date.now() - inst < (CFG.installed_days || 30) * 86400000) return; } catch (e) {}   // ⭐ ยังไม่ติดตั้ง = โชว์ bar ทุกครั้ง (ปิด X แล้วรีเฟรชกลับมา เหมือน popup) · เงียบเมื่อกดติดตั้ง
    var bar = document.createElement('div'); bar.id = 'vc-bar';
    bar.innerHTML = '<span class="vb-ic-img" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M17 1.01 7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"></path></svg></span>'
      + '<div class="vb-tx"><b>เพิ่มแอปไปหน้าจอโฮม</b><i>เข้าถึงสะดวก รวดเร็ว ไม่ต้องพิมพ์ลิงก์</i></div>'
      + '<a class="vb-cta" href="' + CFG.install_url + '" target="_blank" rel="noopener"><svg class="vc-dlic" viewBox="0 0 24 24"><path d="M12 3a1 1 0 0 1 1 1v9.59l3.29-3.3a1 1 0 1 1 1.42 1.42l-5 5a1 1 0 0 1-1.42 0l-5-5a1 1 0 1 1 1.42-1.42L11 13.59V4a1 1 0 0 1 1-1zM5 19a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1z"></path></svg>ติดตั้งแอป</a>'
      + '<button class="vb-x" type="button" aria-label="ปิด">&times;</button>';
    document.body.insertBefore(bar, document.body.firstChild);   // วางบนสุดในสายเนื้อหา (เลื่อนลงแล้วหายไป ไม่ทับ header)
    function closeBar() {
      if (bar.parentNode) bar.parentNode.removeChild(bar);   // ปิดเฉพาะครั้งนี้ (ไม่จำ) → รีเฟรชกลับมา เหมือน popup
    }
    bar.querySelector('.vb-x').addEventListener('click', closeBar);
    bar.querySelector('.vb-cta').addEventListener('click', markInstalled);
  }

  function build() {
    injectCSS();
    buildTab();
    buildBar();
    setTimeout(showPopup, CFG.popup_delay || 1200);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build);
  else build();
})();
JS;
    echo "\n</script>\n";
}, 99);
