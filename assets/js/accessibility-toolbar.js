(function(){
  var wrap=document.querySelector('[data-dci-a11y]'); if(!wrap) return;
  var KEY='dciA11yPrefs',root=document.documentElement,body=document.body;
  var panel=wrap.querySelector('#dci-a11y-panel'),toggle=wrap.querySelector('[data-a11y-action="toggle"]');
  var prefs={font:100,cursor:false,links:false,dyslexia:false};
  try{prefs=Object.assign(prefs,JSON.parse(localStorage.getItem(KEY)||'{}'));}catch(e){}
  function clamp(n,min,max){return Math.min(max,Math.max(min,n));}
  function save(){localStorage.setItem(KEY,JSON.stringify(prefs));}
  function apply(){
    root.style.fontSize=prefs.font+'%';
    body.classList.toggle('dci-a11y-links',!!prefs.links);
    body.classList.toggle('dci-a11y-dyslexia',!!prefs.dyslexia);
    body.style.cursor=prefs.cursor?'zoom-in':'';
    wrap.querySelectorAll('.dci-a11y-btn').forEach(function(btn){
      var a=btn.dataset.a11yAction;
      btn.classList.toggle('is-active',(a==='links'&&prefs.links)||(a==='dyslexia'&&prefs.dyslexia)||(a==='cursor'&&prefs.cursor));
    });
  }
  function speak(){if(!('speechSynthesis' in window)) return; window.speechSynthesis.cancel(); var t=(window.getSelection&&window.getSelection().toString())||((document.querySelector('main')||body).innerText||'').slice(0,2200); if(!t) return; var u=new SpeechSynthesisUtterance(t); u.lang='it-IT'; window.speechSynthesis.speak(u);}
  toggle.addEventListener('click',function(){var open=panel.hidden; panel.hidden=!open; toggle.setAttribute('aria-expanded',open?'true':'false');});
  wrap.addEventListener('click',function(e){var b=e.target.closest('.dci-a11y-btn'); if(!b) return; var a=b.dataset.a11yAction;
    if(a==='font-up') prefs.font=clamp(prefs.font+10,90,130);
    if(a==='font-down') prefs.font=clamp(prefs.font-10,90,130);
    if(a==='cursor') prefs.cursor=!prefs.cursor;
    if(a==='links') prefs.links=!prefs.links;
    if(a==='dyslexia') prefs.dyslexia=!prefs.dyslexia;
    if(a==='read') speak();
    if(a==='reset'){prefs={font:100,cursor:false,links:false,dyslexia:false}; if(window.speechSynthesis) window.speechSynthesis.cancel();}
    save(); apply();
  });
  apply();
})();
