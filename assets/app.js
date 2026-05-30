const DATA = {};
const ISLANDS = { bonaire: 'Bonaire', saba: 'Saba', statia: 'Sint Eustatius' };
const AUDIENCES = { jongeren: 'Jongeren', ouders: 'Ouders', volwassenen: 'Volwassenen', professionals: 'Professionals' };

async function loadData(){
  if(DATA.loaded) return DATA;
  const [orgs, services, events, themes] = await Promise.all([
    fetch('data/organizations.json').then(r=>r.json()),
    fetch('data/services.json').then(r=>r.json()),
    fetch('data/events.json').then(r=>r.json()),
    fetch('data/themes.json').then(r=>r.json())
  ]);
  Object.assign(DATA,{orgs,services,events,themes,loaded:true});
  return DATA;
}
function params(){ return new URLSearchParams(location.search); }
function setParamDefaults(){
  const p=params();
  ['q','island','theme','audience'].forEach(id=>{ const el=document.getElementById(id); if(el && p.get(id)) el.value=p.get(id); });
  const direct=document.getElementById('direct'); if(direct && p.get('direct')==='true') direct.checked=true;
}
function populateFilters(){
  const island=document.getElementById('island'), theme=document.getElementById('theme'), audience=document.getElementById('audience');
  if(island) Object.entries(ISLANDS).forEach(([k,v])=>island.add(new Option(v,k)));
  if(theme) DATA.themes.forEach(t=>theme.add(new Option(t.name,t.slug)));
  if(audience) Object.entries(AUDIENCES).forEach(([k,v])=>audience.add(new Option(v,k)));
}
function buildSearchItems(type){
  const items = [];
  const push = (label, meta, href, keywords='') => items.push({ label, meta, href, search: `${label} ${meta} ${keywords}`.toLowerCase() });

  if(type === 'organizations'){
    DATA.orgs.forEach(o => push(o.name, `${o.short} · ${o.islands.map(i=>ISLANDS[i]).join(', ')}`, `organisaties.html?q=${encodeURIComponent(o.name)}`, `${o.themes?.join(' ')} ${o.audiences?.join(' ')}`));
  } else if(type === 'events'){
    DATA.events.forEach(e => push(e.title, `${ISLANDS[e.island]} · ${e.date}`, `activiteiten.html?q=${encodeURIComponent(e.title)}`, `${e.short} ${orgName(e.organization_id)} ${e.themes?.join(' ')} ${e.audiences?.join(' ')}`));
  } else {
    DATA.services.forEach(s => push(s.title, `${orgName(s.organization_id)} · ${s.islands.map(i=>ISLANDS[i]).join(', ')}`, `aanbod.html?q=${encodeURIComponent(s.title)}`, `${s.short} ${s.themes?.join(' ')} ${s.audiences?.join(' ')}`));
  }

  DATA.themes.forEach(t => push(t.name, 'Thema', `${type === 'events' ? 'activiteiten' : type === 'organizations' ? 'organisaties' : 'aanbod'}.html?theme=${encodeURIComponent(t.slug)}`, t.description || ''));
  return items;
}
function enableSmartSearch(render, type){
  const input = document.getElementById('q');
  if(!input) return;
  const panel = document.createElement('div');
  panel.className = 'suggestions';
  panel.hidden = true;
  input.parentNode.insertBefore(panel, input.nextSibling);
  const items = buildSearchItems(type);

  function showSuggestions(){
    const q = input.value.trim().toLowerCase();
    if(q.length < 2){ panel.hidden = true; return; }
    const matches = items
      .filter(item => item.search.includes(q))
      .slice(0, 7);
    if(!matches.length){ panel.hidden = true; return; }
    panel.innerHTML = matches.map(item => `
      <button type="button" class="suggestion" data-value="${item.label.replace(/"/g,'&quot;')}">
        <strong>${item.label}</strong><span>${item.meta}</span>
      </button>`).join('');
    panel.hidden = false;
  }

  input.addEventListener('input', () => { render(); showSuggestions(); });
  input.addEventListener('focus', showSuggestions);
  document.addEventListener('click', (e) => { if(!panel.contains(e.target) && e.target !== input) panel.hidden = true; });
  panel.addEventListener('click', (e) => {
    const btn = e.target.closest('.suggestion');
    if(!btn) return;
    input.value = btn.dataset.value;
    panel.hidden = true;
    render();
  });
}
function bindFilters(render, type='services'){
  ['island','theme','audience','direct'].forEach(id=>{ const el=document.getElementById(id); if(el) el.addEventListener('input', render); });
  enableSmartSearch(render, type);
  const reset=document.getElementById('reset'); if(reset) reset.addEventListener('click',()=>{ location.href=location.pathname; });
}
function selected(){ return {
  q:(document.getElementById('q')?.value||'').toLowerCase(),
  island:document.getElementById('island')?.value||'',
  theme:document.getElementById('theme')?.value||'',
  audience:document.getElementById('audience')?.value||'',
  direct:document.getElementById('direct')?.checked||false
};}
function orgName(id){ return DATA.orgs.find(o=>o.id===id)?.name || 'Organisatie'; }
function labels(values, map){ return values.map(v=>`<span class="tag">${map?.[v]||v}</span>`).join(''); }
function empty(){ return '<div class="empty">Geen resultaten gevonden. Pas de filters aan of voeg meer demo-data toe.</div>'; }
function serviceCard(s){
  return `<article class="card"><h3>${s.title}</h3><p>${s.short}</p><strong>${orgName(s.organization_id)}</strong><div class="meta">${labels(s.islands,ISLANDS)}${s.direct?'<span class="tag yellow">Directe hulp</span>':''}<span class="tag gray">${s.cost}</span>${s.online?'<span class="tag gray">Online</span>':''}</div></article>`;
}
function orgCard(o){
  return `<article class="card"><h3>${o.name}</h3><p>${o.short}</p><div class="meta">${labels(o.islands,ISLANDS)}${labels(o.audiences,AUDIENCES)}</div><div class="contact-row">${o.whatsapp?`<a href="https://wa.me/${o.whatsapp.replace(/\D/g,'')}">WhatsApp</a>`:''}${o.phone?`<a href="tel:${o.phone}">Bel</a>`:''}${o.email?`<a href="mailto:${o.email}">Mail</a>`:''}</div></article>`;
}
function eventCard(e){
  return `<article class="card"><h3>${e.title}</h3><p>${e.short}</p><strong>${ISLANDS[e.island]} · ${e.date} · ${e.time}</strong><div class="meta">${labels(e.audiences,AUDIENCES)}</div></article>`;
}
function matchArray(arr,val){ return !val || arr.includes(val); }
function matchText(text,q){ return !q || text.toLowerCase().includes(q); }
async function initHome(){
  await loadData();
  document.getElementById('topicGrid').innerHTML = DATA.themes.map(t=>`<a class="topic" href="aanbod.html?theme=${t.slug}"><strong>${t.name}</strong><span>${t.description}</span></a>`).join('');
  document.getElementById('directHelp').innerHTML = DATA.services.filter(s=>s.direct).slice(0,3).map(serviceCard).join('') || empty();
  document.getElementById('upcomingEvents').innerHTML = DATA.events.slice(0,3).map(eventCard).join('') || empty();
}
async function initOrganizations(){
  await loadData(); populateFilters(); setParamDefaults();
  const render=()=>{ const f=selected(); const rows=DATA.orgs.filter(o=>matchArray(o.islands,f.island)&&matchArray(o.themes,f.theme)&&matchArray(o.audiences,f.audience)&&matchText(o.name+' '+o.short,f.q)); document.getElementById('results').innerHTML=rows.map(orgCard).join('')||empty(); };
  bindFilters(render, 'organizations'); render();
}
async function initServices(){
  await loadData(); populateFilters(); setParamDefaults();
  const render=()=>{ const f=selected(); const rows=DATA.services.filter(s=>matchArray(s.islands,f.island)&&matchArray(s.themes,f.theme)&&matchArray(s.audiences,f.audience)&&(!f.direct||s.direct)&&matchText(s.title+' '+s.short+' '+orgName(s.organization_id),f.q)); document.getElementById('results').innerHTML=rows.map(serviceCard).join('')||empty(); };
  bindFilters(render, 'services'); render();
}
async function initEvents(){
  await loadData(); populateFilters(); setParamDefaults();
  const render=()=>{ const f=selected(); const rows=DATA.events.filter(e=>(!f.island||e.island===f.island)&&matchArray(e.themes,f.theme)&&matchArray(e.audiences,f.audience)&&matchText(e.title+' '+e.short+' '+orgName(e.organization_id),f.q)); document.getElementById('results').innerHTML=rows.map(eventCard).join('')||empty(); };
  bindFilters(render, 'events'); render();
}
