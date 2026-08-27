<?php
/*
Plugin Name: Vision CTA Buttons
Description: ปุ่มลอย [ติดตั้งแอป] [LINE] [Telegram] — static 100% (ไม่ fetch GitHub, ไม่ redirect) = imunify-safe
Version: 1.0
*/
if (!defined('ABSPATH')) { return; }

/* ============================================================
   ★ แก้ค่าตรงนี้ที่เดียว (บน GitHub) แล้วรัน run-all.sh บนแต่ละเครื่อง
   ============================================================ */
$VISION_CTA = array(
    'enabled'       => true,
    'install_url'   => 'https://REPLACE-install-domain/?action=install',  // หน้า install แยก (ยังไม่เปิด)
    'line_url'      => 'https://line.me/R/ti/p/@979epvsz',                // ลิงก์ LINE
    'tg_url'        => 'https://t.me/VISIONSBET',                         // ลิงก์ Telegram
    'show_install'  => false,     // ★ ปิดไว้ก่อน จนกว่าจะได้โดเมนหน้า install แล้วเปลี่ยนเป็น true
    'show_line'     => true,
    'show_tg'       => true,
    'position'      => 'right',   // right | left
    'offset_bottom' => 90,        // px จากขอบล่าง (เผื่อเลี่ยงปุ่มอื่นของเว็บ)
);

add_action('wp_head', function () use ($VISION_CTA) {
    if (empty($VISION_CTA['enabled'])) { return; }
    echo "\n<script data-no-optimize=\"1\" data-cfasync=\"false\">\n";
    echo 'window.__VISION_CTA=' . wp_json_encode($VISION_CTA) . ";\n";
    echo <<<'JS'
(function () {
  var CFG = window.__VISION_CTA; if (!CFG || !CFG.enabled) return;
  if (window.__visionCTAdone) return; window.__visionCTAdone = true;

  var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone;

  var B = [];
  if (CFG.show_install && !standalone) B.push({k:'install',t:'ติดตั้งแอป',h:CFG.install_url,bg:'linear-gradient(145deg,#ffe08a,#f7c250,#d69a22)',fg:'#20140a',i:'⬇️',blank:false});
  if (CFG.show_line)  B.push({k:'line', t:'LINE',    h:CFG.line_url,bg:'#06C755',fg:'#fff',i:'💬',blank:true});
  if (CFG.show_tg)    B.push({k:'tg',   t:'Telegram',h:CFG.tg_url,  bg:'#229ED9',fg:'#fff',i:'✈️',blank:true});
  if (!B.length) return;

  var side = CFG.position === 'left' ? 'left' : 'right';
  var ob = parseInt(CFG.offset_bottom, 10) || 90;
  var radius = side === 'left' ? '0 22px 22px 0' : '22px 0 0 22px';
  var alignI = side === 'left' ? 'flex-start' : 'flex-end';
  var hoverX = side === 'left' ? '4px' : '-4px';

  var css = ''
    + '#vision-cta{position:fixed;'+side+':0;bottom:'+ob+'px;z-index:99998;display:flex;flex-direction:column;gap:8px;align-items:'+alignI+';font-family:"Prompt",system-ui,-apple-system,sans-serif;}'
    + '#vision-cta a{display:flex;align-items:center;gap:8px;text-decoration:none;padding:11px 16px;font-size:14px;font-weight:700;line-height:1;white-space:nowrap;border-radius:'+radius+';box-shadow:0 4px 14px rgba(0,0,0,.28);transition:transform .15s ease,box-shadow .15s ease;-webkit-tap-highlight-color:transparent;}'
    + '#vision-cta a:hover{transform:translateX('+hoverX+');box-shadow:0 6px 20px rgba(0,0,0,.4);}'
    + '#vision-cta a .vc-ic{font-size:16px;}'
    + '#vision-cta a.vc-install{animation:vcGlow 1.8s ease-in-out infinite;}'
    + '@keyframes vcGlow{0%,100%{box-shadow:0 4px 14px rgba(214,154,34,.4);}50%{box-shadow:0 4px 22px rgba(255,200,80,.75);}}'
    + '@media(max-width:600px){#vision-cta a{padding:10px 13px;font-size:13px;}}';

  function build() {
    if (document.getElementById('vision-cta')) return;
    var s = document.createElement('style'); s.textContent = css; document.head.appendChild(s);
    var w = document.createElement('div'); w.id = 'vision-cta';
    B.forEach(function (b) {
      var a = document.createElement('a');
      a.href = b.h;
      if (b.blank) { a.target = '_blank'; a.rel = 'noopener'; }
      if (b.k === 'install') a.className = 'vc-install';
      a.style.background = b.bg; a.style.color = b.fg;
      a.innerHTML = '<span class="vc-ic">' + b.i + '</span><span>' + b.t + '</span>';
      w.appendChild(a);
    });
    document.body.appendChild(w);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build);
  else build();
})();
JS;
    echo "\n</script>\n";
}, 99);
