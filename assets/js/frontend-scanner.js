/**
 * WP A11y Kit — Frontend Scanner
 * Runs a11y checks on the live page and reports to admin bar + WP backend.
 */
(function () {
  'use strict';
  if (typeof wpaakData === 'undefined') return;
  function runQuickChecks() {
    let errors = 0, warnings = 0, notices = 0;
    document.querySelectorAll('img:not([alt])').forEach(() => errors++);
    document.querySelectorAll('a[href]').forEach(a => { if (!(a.textContent||a.getAttribute('aria-label')||a.getAttribute('title')||'').trim()) errors++; });
    document.querySelectorAll('button').forEach(b => { if (!(b.textContent||b.getAttribute('aria-label')||'').trim()) errors++; });
    document.querySelectorAll('input:not([type=hidden]):not([type=submit])').forEach(i => { if (!(i.id&&document.querySelector(`label[for="${i.id}"]`))&&!i.getAttribute('aria-label')&&!i.getAttribute('aria-labelledby')) warnings++; });
    let prev = 0;
    [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')].forEach(h => { const lv = parseInt(h.tagName[1],10); if (prev && lv > prev+1) errors++; prev = lv; });
    if (!document.querySelector('h1')) warnings++;
    if (!document.documentElement.getAttribute('lang')) errors++;
    document.querySelectorAll('iframe').forEach(f => { if (!f.getAttribute('title') && !f.getAttribute('aria-label')) errors++; });
    const score = Math.max(0,Math.round(100-errors*10-warnings*3-notices*1));
    return { errors, warnings, notices, score };
  }
  function updateAdminBar(r) { const n = document.getElementById('wp-admin-bar-wpaak-badge'); if (!n) return; const l = n.querySelector('#wpaak-ab-label'); if (!l) return; const c = r.score>=80?'#3fb950':r.score>=50?'#e3b341':'#f78166'; l.innerHTML=`♿ <span style="color:${c};font-weight:700">${r.score}</span>`; }
  function sendToBackend(r) { const b = new FormData(); b.append('action','wpaak_report'); b.append('nonce',wpaakData.nonce); b.append('report',JON.stringify({...r,url:location.href})); fetch(wpaakData.ajaxUrl,{method:'POST',body:b}).catch(()=>{}); }
  function init() { setTimeout(() => { const r = runQuickChecks(); updateAdminBar(r); sendToBackend(r); }, 800); }
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
})();
