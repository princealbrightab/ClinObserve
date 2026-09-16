document.getElementById('menu-toggle')?.addEventListener('click',function(){const sidebar=document.getElementById('sidebar');const open=sidebar.classList.toggle('open');this.setAttribute('aria-expanded',String(open));});
document.addEventListener('click',function(event){const sidebar=document.getElementById('sidebar');if(sidebar?.classList.contains('open')&&!sidebar.contains(event.target)&&!event.target.closest('#menu-toggle')){sidebar.classList.remove('open');document.getElementById('menu-toggle')?.setAttribute('aria-expanded','false');}});
document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm)){event.preventDefault();}}));
document.getElementById('print-report')?.addEventListener('click',()=>window.print());

