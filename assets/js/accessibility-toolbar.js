(function(){
  var KEY='dciA11yPrefs';
  var root=document.documentElement;
  var body=document.body;
  if(!body)return;
  var prefs={font:100,cursor:false,links:false,dyslexia:false};
  try{var stored=JSON.parse(localStorage.getItem(KEY)||'{}');prefs=Object.assign(prefs,stored);}catch(e){}

  function clamp(n,min,max){return Math.min(max,Math.max(min,n));}
  function save(){localStorage.setItem(KEY,JSON.stringify(prefs));}
  function apply(){
    root.style.fontSize=prefs.font+'%';
    body.classList.toggle('dci-a11y-cursor',!!prefs.cursor);
    body.classList.toggle('dci-a11y-links',!!prefs.links);
    body.classList.toggle('dci-a11y-dyslexia',!!prefs.dyslexia);
    document.querySelectorAll('.dci-a11y-btn').forEach(function(btn){
      var a=btn.getAttribute('data-a11y-action');
      var active=(a==='cursor'&&prefs.cursor)||(a==='links'&&prefs.links)||(a==='dyslexia'&&prefs.dyslexia);
      btn.classList.toggle('is-active',active);
    });
  }
  function readContent(){
    if(!('speechSynthesis' in window))return;
    window.speechSynthesis.cancel();
    var selected=(window.getSelection&&window.getSelection().toString())||'';
    var txt=selected||((document.querySelector('main')||document.body).innerText||'').slice(0,3000);
    if(!txt)return;
    var u=new SpeechSynthesisUtterance(txt);
    u.lang='it-IT';
    window.speechSynthesis.speak(u);
  }

  document.addEventListener('click',function(e){
    var btn=e.target.closest('.dci-a11y-btn'); if(!btn)return;
    var a=btn.getAttribute('data-a11y-action');
    if(a==='font-up')prefs.font=clamp(prefs.font+10,90,130);
    if(a==='font-down')prefs.font=clamp(prefs.font-10,90,130);
    if(a==='cursor')prefs.cursor=!prefs.cursor;
    if(a==='links')prefs.links=!prefs.links;
    if(a==='dyslexia')prefs.dyslexia=!prefs.dyslexia;
    if(a==='read')readContent();
    if(a==='reset'){prefs={font:100,cursor:false,links:false,dyslexia:false};window.speechSynthesis&&window.speechSynthesis.cancel();}
    save();apply();
  });

  apply();
})();
