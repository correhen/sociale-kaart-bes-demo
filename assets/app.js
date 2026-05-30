const DATA = {};
const ISLANDS = { bonaire: 'Bonaire', statia: 'Sint Eustatius', saba: 'Saba' };
const AUDIENCES = { jongeren: 'Jongeren', ouders: 'Ouders', volwassenen: 'Volwassenen', professionals: 'Professionals' };
const LANGUAGES = { nl: 'Nederlands', pap: 'Papiamentu', en: 'Engels', es: 'Spaans' };
const PREF_KEYS = { island: 'preferredIsland', language: 'preferredLanguage' };
const VALID_ISLANDS = Object.keys(ISLANDS);
const VALID_LANGUAGES = Object.keys(LANGUAGES);
const preferenceListeners = [];
const HERO_IMAGES = {
  default: { webp: 'assets/img/hero/herobanner.webp', png: 'assets/img/hero/herobanner.png' },
  bonaire: { webp: 'assets/img/hero/bonaire.webp', png: 'assets/img/hero/bonaire.png' },
  saba: { webp: 'assets/img/hero/saba.webp', png: 'assets/img/hero/saba.png' },
  statia: { webp: 'assets/img/hero/statia.webp', png: 'assets/img/hero/statia.png' }
};

const I18N = {
  nl: {
    navOrganizations: 'Organisaties',
    navServices: 'Hulp & aanbod',
    navEvents: 'Activiteiten',
    preferencesButton: 'Voorkeuren',
    searchOrganizations: 'Zoek op naam of omschrijving',
    searchServices: 'Waar zoek je hulp bij?',
    searchEvents: 'Zoek activiteit',
    filterAllIslands: 'Alle eilanden',
    filterAllThemes: "Alle thema's",
    filterAllAudiences: 'Alle doelgroepen',
    resetButton: 'Reset',
    directHelp: 'Directe hulp',
    homeEyebrow: 'Bonaire - Saba - Sint Eustatius',
    homeTitle: 'Vind wat jij nodig hebt. Dicht bij jou.',
    homeIntro: 'Zoek snel hulp, activiteiten en organisaties op jouw eiland. Warm, dichtbij en makkelijk te gebruiken.',
    homeSearchLabel: 'Zoeken',
    homeSearchPlaceholder: 'Waar zoek je naar?',
    homeSearchButton: 'Zoek',
    homeHelpButton: 'Hulp & ondersteuning',
    homeActivityButton: 'Activiteiten',
    homeOrganizationsButton: 'Organisaties',
    homeParentsButton: 'Voor ouders',
    homeChooseIsland: 'Kies je eiland',
    homeIslandText: 'Start met aanbod dat past bij waar jij bent.',
    homeHeroCaption: 'Jouw sociale kaart, dichtbij.',
    homeYouthTitle: 'Voor jongeren',
    homeYouthText: 'Vind iemand om mee te praten, iets om te doen of hulp bij school, geld en thuis.',
    homeParentsProsTitle: 'Voor ouders en professionals',
    homeParentsProsText: 'Snel overzicht van organisaties en aanbod om goed door te verwijzen.',
    homeYouthServicesTitle: 'Hulp voor jongeren',
    homeYouthServicesText: 'Een paar directe ingangen uit de demo-data.',
    homeParentsServicesTitle: 'Voor ouders en professionals',
    homeParentsServicesText: 'Ondersteuning voor gezin, opvoeding en praktische vragen.',
    homeTopicsTitle: "Populaire thema's",
    homeTopicsText: 'Herkenbare ingangen, geinspireerd op vraaggerichte sociale kaarten.',
    homeDirectTitle: 'Direct hulp nodig?',
    homeDirectText: 'Toon hier aanbod dat snel bereikbaar moet zijn.',
    homeEventsTitle: 'Activiteiten binnenkort',
    homeEventsText: 'Activiteiten maken de sociale kaart levendiger.',
    organizationsTitle: 'Organisaties',
    organizationsIntro: 'Bekijk organisaties op Bonaire, Saba en Sint Eustatius. In de echte versie komt hier actuele, gecontroleerde informatie.',
    servicesTitle: 'Hulp & aanbod',
    servicesIntro: 'Zoek niet alleen op organisatie, maar op wat iemand nodig heeft. Dit wordt het hart van de sociale kaart.',
    eventsTitle: 'Activiteiten',
    eventsIntro: 'Een agenda maakt de sociale kaart concreet en aantrekkelijker voor jongeren en ouders.',
    footerHome: 'Demo voor bespreking met stagiair/opdrachtgever. Data is fictief.',
    footerShort: 'Demo. Data is fictief.',
    modalIslandTitle: 'Kies eerst je eiland',
    modalIslandText: 'We gebruiken je keuze als standaardfilter voor organisaties, aanbod en activiteiten.',
    modalLanguageTitle: 'Kies je taal',
    modalLanguageText: 'De interface wordt in deze taal getoond. De bestaande demo-data blijft voorlopig ongewijzigd.',
    modalNext: 'Volgende',
    modalBack: 'Terug',
    modalSave: 'Opslaan',
    modalClose: 'Sluiten',
    modalRequired: 'Maak een keuze om verder te gaan.',
    empty: 'Geen resultaten gevonden. Pas de filters aan of voeg meer demo-data toe.',
    themeLabel: 'Thema',
    directTag: 'Directe hulp',
    onlineTag: 'Online',
    viewButton: 'Bekijk',
    islandTag: 'Eiland',
    audienceTag: 'Doelgroep',
    footerTagline: 'Voor hulp, activiteiten en ondersteuning op Bonaire, Sint Eustatius en Saba.',
    footerCredit: 'Design & development support by Nos Boneiru',
    organizationFallback: 'Organisatie'
  },
  pap: {
    navOrganizations: 'Organisashonnan',
    navServices: 'Yudansa i oferta',
    navEvents: 'Aktividatnan',
    preferencesButton: 'Preferensianan',
    searchOrganizations: 'Busca riba nomber of deskripshon',
    searchServices: 'Unda bo ta busca yudansa?',
    searchEvents: 'Busca aktividat',
    filterAllIslands: 'Tur isla',
    filterAllThemes: 'Tur tema',
    filterAllAudiences: 'Tur grupo',
    resetButton: 'Reset',
    directHelp: 'Yudansa direkt',
    homeEyebrow: 'Bonaire - Saba - Sint Eustatius',
    homeTitle: 'Haya loke bo tin mester. Serka bo.',
    homeIntro: 'Busca yudansa, aktividatnan i organisashonnan riba bo isla. Kayente, serka i fasil pa usa.',
    homeSearchLabel: 'Busca',
    homeSearchPlaceholder: 'Kiko bo ta busca?',
    homeSearchButton: 'Busca',
    homeHelpButton: 'Yudansa i sosten',
    homeActivityButton: 'Aktividatnan',
    homeOrganizationsButton: 'Organisashonnan',
    homeParentsButton: 'Pa mayornan',
    homeChooseIsland: 'Skoge bo isla',
    homeIslandText: 'Kuminsa ku oferta ku ta pas ku unda bo ta.',
    homeHeroCaption: 'Bo mapa sosial, serka bo.',
    homeYouthTitle: 'Pa hobennan',
    homeYouthText: 'Haya hende pa papia kune, algu pa hasi of yudansa ku skol, placa i kas.',
    homeParentsProsTitle: 'Pa mayornan i profesionalnan',
    homeParentsProsText: 'Un bista rapido di organisashonnan i oferta pa referi bon.',
    homeYouthServicesTitle: 'Yudansa pa hobennan',
    homeYouthServicesText: 'Algun entrada direkt for di e data di demo.',
    homeParentsServicesTitle: 'Pa mayornan i profesionalnan',
    homeParentsServicesText: 'Sosten pa famia, kriansa i preguntanan praktiko.',
    homeTopicsTitle: 'Temanan popular',
    homeTopicsText: 'Entradanan rekonosibel, inspira pa mapa sosial ku ta sali for di pregunta.',
    homeDirectTitle: 'Tin mester di yudansa direkt?',
    homeDirectText: 'Mustra aki oferta ku mester ta fasil pa alkansa.',
    homeEventsTitle: 'Aktividatnan pronto',
    homeEventsText: 'Aktividatnan ta hasi e mapa sosial mas bibu.',
    organizationsTitle: 'Organisashonnan',
    organizationsIntro: 'Wak organisashonnan riba Bonaire, Saba i Sint Eustatius. Den e version real informashon ta aktual i kontrola.',
    servicesTitle: 'Yudansa i oferta',
    servicesIntro: 'Busca no solamente riba organisashon, pero riba loke hende tin mester. Esaki ta bira e kurason di e mapa sosial.',
    eventsTitle: 'Aktividatnan',
    eventsIntro: 'Un agenda ta hasi e mapa sosial konkreto i mas atrakshon pa hobennan i mayornan.',
    footerHome: 'Demo pa kombersashon ku stagiair/kliente. Data ta fiktisio.',
    footerShort: 'Demo. Data ta fiktisio.',
    modalIslandTitle: 'Skoge prome bo isla',
    modalIslandText: 'Nos ta usa bo escogensia komo filtro standard pa organisashonnan, oferta i aktividatnan.',
    modalLanguageTitle: 'Skoge bo idioma',
    modalLanguageText: 'E interface ta wordu mustra den e idioma aki. E data di demo ta keda meskos pa awor.',
    modalNext: 'Siguiente',
    modalBack: 'Bek',
    modalSave: 'Warda',
    modalClose: 'Sera',
    modalRequired: 'Skoge un opshon pa sigui.',
    empty: 'No a haya resultado. Kambia e filternan of agrega mas data di demo.',
    themeLabel: 'Tema',
    directTag: 'Yudansa direkt',
    onlineTag: 'Online',
    viewButton: 'Wak',
    islandTag: 'Isla',
    audienceTag: 'Grupo',
    footerTagline: 'Pa yudansa, aktividatnan i sosten riba Bonaire, Sint Eustatius i Saba.',
    footerCredit: 'Design & development support by Nos Boneiru',
    organizationFallback: 'Organisashon'
  },
  en: {
    navOrganizations: 'Organizations',
    navServices: 'Help & services',
    navEvents: 'Activities',
    preferencesButton: 'Preferences',
    searchOrganizations: 'Search by name or description',
    searchServices: 'What do you need help with?',
    searchEvents: 'Search activities',
    filterAllIslands: 'All islands',
    filterAllThemes: 'All themes',
    filterAllAudiences: 'All audiences',
    resetButton: 'Reset',
    directHelp: 'Direct help',
    homeEyebrow: 'Bonaire - Saba - Sint Eustatius',
    homeTitle: 'Find what you need. Close to you.',
    homeIntro: 'Quickly search help, activities and organizations on your island. Warm, nearby and easy to use.',
    homeSearchLabel: 'Search',
    homeSearchPlaceholder: 'What are you looking for?',
    homeSearchButton: 'Search',
    homeHelpButton: 'Help & support',
    homeActivityButton: 'Activities',
    homeOrganizationsButton: 'Organizations',
    homeParentsButton: 'For parents',
    homeChooseIsland: 'Choose your island',
    homeIslandText: 'Start with services that match where you are.',
    homeHeroCaption: 'Your social map, nearby.',
    homeYouthTitle: 'For young people',
    homeYouthText: 'Find someone to talk to, something to do or help with school, money and home.',
    homeParentsProsTitle: 'For parents and professionals',
    homeParentsProsText: 'A quick overview of organizations and services for better referrals.',
    homeYouthServicesTitle: 'Help for young people',
    homeYouthServicesText: 'A few direct entry points from the demo data.',
    homeParentsServicesTitle: 'For parents and professionals',
    homeParentsServicesText: 'Support for family, parenting and practical questions.',
    homeTopicsTitle: 'Popular topics',
    homeTopicsText: 'Recognizable entry points inspired by question-led social maps.',
    homeDirectTitle: 'Need help right now?',
    homeDirectText: 'Show services that should be easy to reach quickly.',
    homeEventsTitle: 'Activities coming up',
    homeEventsText: 'Activities make the social map more alive.',
    organizationsTitle: 'Organizations',
    organizationsIntro: 'Browse organizations on Bonaire, Saba and Sint Eustatius. The real version will contain current, verified information.',
    servicesTitle: 'Help & services',
    servicesIntro: 'Search by what someone needs, not only by organization. This will become the heart of the social map.',
    eventsTitle: 'Activities',
    eventsIntro: 'An agenda makes the social map concrete and more appealing for young people and parents.',
    footerHome: 'Demo for discussion with intern/client. Data is fictional.',
    footerShort: 'Demo. Data is fictional.',
    modalIslandTitle: 'First choose your island',
    modalIslandText: 'We use your choice as the default filter for organizations, services and activities.',
    modalLanguageTitle: 'Choose your language',
    modalLanguageText: 'The interface will be shown in this language. The existing demo data stays unchanged for now.',
    modalNext: 'Next',
    modalBack: 'Back',
    modalSave: 'Save',
    modalClose: 'Close',
    modalRequired: 'Choose an option to continue.',
    empty: 'No results found. Adjust the filters or add more demo data.',
    themeLabel: 'Theme',
    directTag: 'Direct help',
    onlineTag: 'Online',
    viewButton: 'View',
    islandTag: 'Island',
    audienceTag: 'Audience',
    footerTagline: 'For help, activities and support on Bonaire, Sint Eustatius and Saba.',
    footerCredit: 'Design & development support by Nos Boneiru',
    organizationFallback: 'Organization'
  },
  es: {
    navOrganizations: 'Organizaciones',
    navServices: 'Ayuda y servicios',
    navEvents: 'Actividades',
    preferencesButton: 'Preferencias',
    searchOrganizations: 'Buscar por nombre o descripcion',
    searchServices: 'En que necesitas ayuda?',
    searchEvents: 'Buscar actividad',
    filterAllIslands: 'Todas las islas',
    filterAllThemes: 'Todos los temas',
    filterAllAudiences: 'Todos los grupos',
    resetButton: 'Restablecer',
    directHelp: 'Ayuda directa',
    homeEyebrow: 'Bonaire - Saba - Sint Eustatius',
    homeTitle: 'Encuentra lo que necesitas. Cerca de ti.',
    homeIntro: 'Busca rapidamente ayuda, actividades y organizaciones en tu isla. Calido, cercano y facil de usar.',
    homeSearchLabel: 'Buscar',
    homeSearchPlaceholder: 'Que estas buscando?',
    homeSearchButton: 'Buscar',
    homeHelpButton: 'Ayuda y apoyo',
    homeActivityButton: 'Actividades',
    homeOrganizationsButton: 'Organizaciones',
    homeParentsButton: 'Para padres',
    homeChooseIsland: 'Elige tu isla',
    homeIslandText: 'Empieza con servicios que encajan con donde estas.',
    homeHeroCaption: 'Tu mapa social, cerca de ti.',
    homeYouthTitle: 'Para jovenes',
    homeYouthText: 'Encuentra alguien con quien hablar, algo que hacer o ayuda con escuela, dinero y casa.',
    homeParentsProsTitle: 'Para padres y profesionales',
    homeParentsProsText: 'Un resumen rapido de organizaciones y servicios para orientar mejor.',
    homeYouthServicesTitle: 'Ayuda para jovenes',
    homeYouthServicesText: 'Algunas entradas directas de los datos demo.',
    homeParentsServicesTitle: 'Para padres y profesionales',
    homeParentsServicesText: 'Apoyo para familia, crianza y preguntas practicas.',
    homeTopicsTitle: 'Temas populares',
    homeTopicsText: 'Entradas reconocibles, inspiradas en mapas sociales orientados a preguntas.',
    homeDirectTitle: 'Necesitas ayuda directa?',
    homeDirectText: 'Muestra aqui servicios que deben ser faciles de contactar rapido.',
    homeEventsTitle: 'Actividades proximas',
    homeEventsText: 'Las actividades hacen que el mapa social sea mas vivo.',
    organizationsTitle: 'Organizaciones',
    organizationsIntro: 'Consulta organizaciones en Bonaire, Saba y Sint Eustatius. La version real tendra informacion actual y verificada.',
    servicesTitle: 'Ayuda y servicios',
    servicesIntro: 'Busca no solo por organizacion, sino por lo que alguien necesita. Este sera el centro del mapa social.',
    eventsTitle: 'Actividades',
    eventsIntro: 'Una agenda hace el mapa social mas concreto y atractivo para jovenes y padres.',
    footerHome: 'Demo para conversar con pasante/cliente. Los datos son ficticios.',
    footerShort: 'Demo. Los datos son ficticios.',
    modalIslandTitle: 'Primero elige tu isla',
    modalIslandText: 'Usamos tu eleccion como filtro predeterminado para organizaciones, servicios y actividades.',
    modalLanguageTitle: 'Elige tu idioma',
    modalLanguageText: 'La interfaz se mostrara en este idioma. Los datos existentes de la demo quedan sin cambios por ahora.',
    modalNext: 'Siguiente',
    modalBack: 'Atras',
    modalSave: 'Guardar',
    modalClose: 'Cerrar',
    modalRequired: 'Elige una opcion para continuar.',
    empty: 'No se encontraron resultados. Ajusta los filtros o agrega mas datos de demo.',
    themeLabel: 'Tema',
    directTag: 'Ayuda directa',
    onlineTag: 'En linea',
    viewButton: 'Ver',
    islandTag: 'Isla',
    audienceTag: 'Grupo',
    footerTagline: 'Para ayuda, actividades y apoyo en Bonaire, Sint Eustatius y Saba.',
    footerCredit: 'Design & development support by Nos Boneiru',
    organizationFallback: 'Organizacion'
  }
};

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
function currentLanguage(){ return getStoredPreference(PREF_KEYS.language, VALID_LANGUAGES) || 'nl'; }
function t(key){ return (I18N[currentLanguage()] && I18N[currentLanguage()][key]) || I18N.nl[key] || key; }
function islandName(key){ return ISLANDS[key] || ''; }
function activeIsland(){
  const islandSelect = document.getElementById('island');
  const urlIsland = params().get('island');
  if(VALID_ISLANDS.includes(urlIsland)) return urlIsland;
  if(islandSelect?.value) return islandSelect.value;
  return getPreferences().island;
}
function filteredByIsland(items, getter){
  const island = activeIsland();
  if(!island) return items;
  return items.filter(item => {
    const value = getter(item);
    return Array.isArray(value) ? value.includes(island) : value === island;
  });
}
function localizedHomeTitle(island){
  if(!island) return t('homeTitle');
  const name = islandName(island);
  const lang = currentLanguage();
  if(lang === 'pap') return `Haya loke bo tin mester riba ${name}.`;
  if(lang === 'en') return `Find what you need on ${name}.`;
  if(lang === 'es') return `Encuentra lo que necesitas en ${name}.`;
  return `Vind wat jij nodig hebt op ${name}.`;
}
function updateHomeHero(){
  const title = document.querySelector('[data-i18n="homeTitle"]');
  const image = document.getElementById('homeHeroImage');
  const source = document.getElementById('homeHeroSource');
  const islandLabel = document.getElementById('homeHeroIsland');
  const searchIsland = document.getElementById('homeSearchIsland');
  if(!title || !image || !source) return;
  const island = getPreferences().island;
  const islandQuery = island ? `?island=${encodeURIComponent(island)}` : '';
  const imageSet = HERO_IMAGES[island] || HERO_IMAGES.default;
  title.textContent = localizedHomeTitle(island);
  source.srcset = imageSet.webp;
  image.src = imageSet.png;
  image.alt = island ? `${islandName(island)} community` : 'Sociale Kaart BES community';
  if(islandLabel) islandLabel.textContent = island ? islandName(island) : 'BES';
  if(searchIsland) searchIsland.value = island || '';
  const helpLink = document.getElementById('homeHelpLink');
  const activityLink = document.getElementById('homeActivityLink');
  const orgLink = document.getElementById('homeOrgLink');
  if(helpLink) helpLink.href = `aanbod.html${islandQuery}`;
  if(activityLink) activityLink.href = `activiteiten.html${islandQuery}`;
  if(orgLink) orgLink.href = `organisaties.html${islandQuery}`;
}
function escapeHtml(value){
  return String(value).replace(/[&<>"']/g, ch => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[ch]));
}
function getStoredPreference(key, allowed){
  try {
    const value = localStorage.getItem(key);
    return allowed.includes(value) ? value : '';
  } catch(e) {
    return '';
  }
}
function setStoredPreference(key, value){
  try { localStorage.setItem(key, value); } catch(e) {}
}
function getPreferences(){
  return {
    island: getStoredPreference(PREF_KEYS.island, VALID_ISLANDS),
    language: getStoredPreference(PREF_KEYS.language, VALID_LANGUAGES)
  };
}
function hasPreferences(){
  const prefs = getPreferences();
  return Boolean(prefs.island && prefs.language);
}
function savePreferences(island, language){
  setStoredPreference(PREF_KEYS.island, island);
  setStoredPreference(PREF_KEYS.language, language);
  applyTranslations();
  applyPreferredIslandDefault(true);
  preferenceListeners.forEach(listener => listener());
}
function onPreferencesChanged(listener){ preferenceListeners.push(listener); }

function applyTranslations(){
  document.documentElement.lang = currentLanguage();
  document.querySelectorAll('[data-i18n]').forEach(el => { el.textContent = t(el.dataset.i18n); });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => { el.placeholder = t(el.dataset.i18nPlaceholder); });
  updateHomeHero();
}

function initAppShell(){
  addPreferenceButton();
  ensurePreferencesModal();
  applyTranslations();
  if(!hasPreferences()) openPreferencesModal(false);
}

function addPreferenceButton(){
  const header = document.querySelector('.site-header');
  if(!header || header.querySelector('.preference-button')) return;
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'preference-button';
  button.setAttribute('data-i18n', 'preferencesButton');
  button.addEventListener('click', () => openPreferencesModal(true));
  header.appendChild(button);
}

function ensurePreferencesModal(){
  if(document.getElementById('preferencesModal')) return;
  const modal = document.createElement('div');
  modal.id = 'preferencesModal';
  modal.className = 'modal-backdrop';
  modal.hidden = true;
  modal.innerHTML = `
    <div class="preference-modal" role="dialog" aria-modal="true" aria-labelledby="preferenceTitle">
      <button type="button" class="modal-close" data-modal-close aria-label="${t('modalClose')}">x</button>
      <div id="preferenceContent"></div>
    </div>`;
  document.body.appendChild(modal);
  modal.addEventListener('click', e => {
    if(e.target === modal && hasPreferences()) closePreferencesModal();
  });
  modal.querySelector('[data-modal-close]').addEventListener('click', closePreferencesModal);
}

function openPreferencesModal(canClose){
  ensurePreferencesModal();
  const modal = document.getElementById('preferencesModal');
  modal.dataset.canClose = canClose ? 'true' : 'false';
  modal.hidden = false;
  renderPreferenceStep('island');
}
function closePreferencesModal(){
  const modal = document.getElementById('preferencesModal');
  if(!modal || (modal.dataset.canClose !== 'true' && !hasPreferences())) return;
  modal.hidden = true;
}
function renderPreferenceStep(step, selectedIsland){
  const modal = document.getElementById('preferencesModal');
  const content = document.getElementById('preferenceContent');
  const prefs = getPreferences();
  const island = selectedIsland || prefs.island;
  const language = prefs.language || currentLanguage();
  const closeButton = modal.querySelector('[data-modal-close]');
  closeButton.hidden = modal.dataset.canClose !== 'true';
  closeButton.setAttribute('aria-label', t('modalClose'));

  if(step === 'island'){
    content.innerHTML = `
      <p class="modal-kicker">Sociale Kaart BES</p>
      <h2 id="preferenceTitle">${t('modalIslandTitle')}</h2>
      <p>${t('modalIslandText')}</p>
      <div class="choice-grid" data-choice-group="island">
        ${VALID_ISLANDS.map(key => `<button type="button" class="choice-card island-choice island-${key} ${island === key ? 'selected' : ''}" data-value="${key}"><span>${ISLANDS[key]}</span></button>`).join('')}
      </div>
      <p class="modal-error" hidden>${t('modalRequired')}</p>
      <div class="modal-actions">
        <button type="button" class="button primary" data-next>${t('modalNext')}</button>
      </div>`;
    content.querySelector('[data-next]').addEventListener('click', () => {
      const picked = content.querySelector('.choice-grid .selected')?.dataset.value;
      if(!picked){ content.querySelector('.modal-error').hidden = false; return; }
      renderPreferenceStep('language', picked);
    });
  } else {
    content.innerHTML = `
      <p class="modal-kicker">Sociale Kaart BES</p>
      <h2 id="preferenceTitle">${t('modalLanguageTitle')}</h2>
      <p>${t('modalLanguageText')}</p>
      <div class="choice-grid" data-choice-group="language">
        ${VALID_LANGUAGES.map(key => `<button type="button" class="choice-card language-choice ${language === key ? 'selected' : ''}" data-value="${key}">${LANGUAGES[key]}</button>`).join('')}
      </div>
      <p class="modal-error" hidden>${t('modalRequired')}</p>
      <div class="modal-actions">
        <button type="button" class="button" data-back>${t('modalBack')}</button>
        <button type="button" class="button primary" data-save>${t('modalSave')}</button>
      </div>`;
    content.querySelector('[data-back]').addEventListener('click', () => renderPreferenceStep('island', island));
    content.querySelector('[data-save]').addEventListener('click', () => {
      const pickedLanguage = content.querySelector('.choice-grid .selected')?.dataset.value;
      if(!pickedLanguage){ content.querySelector('.modal-error').hidden = false; return; }
      savePreferences(island, pickedLanguage);
      closePreferencesModal();
    });
  }

  content.querySelector('.choice-grid').addEventListener('click', e => {
    const button = e.target.closest('button[data-value]');
    if(!button) return;
    content.querySelectorAll('.choice-grid button').forEach(item => item.classList.remove('selected'));
    button.classList.add('selected');
    content.querySelector('.modal-error').hidden = true;
  });
}

function setParamDefaults(){
  const p=params();
  ['q','island','theme','audience'].forEach(id=>{ const el=document.getElementById(id); if(el && p.get(id)) el.value=p.get(id); });
  const direct=document.getElementById('direct'); if(direct && p.get('direct')==='true') direct.checked=true;
}
function applyPreferredIslandDefault(force=false){
  const island = document.getElementById('island');
  const preferredIsland = getPreferences().island;
  if(!island || !preferredIsland || params().has('island')) return;
  if(force || !island.value) {
    island.value = preferredIsland;
    if(force) island.dispatchEvent(new Event('input'));
  }
}
function populateFilters(){
  const island=document.getElementById('island'), theme=document.getElementById('theme'), audience=document.getElementById('audience');
  if(island && island.options.length <= 1) Object.entries(ISLANDS).forEach(([k,v])=>island.add(new Option(v,k)));
  if(theme && theme.options.length <= 1) DATA.themes.forEach(t=>theme.add(new Option(t.name,t.slug)));
  if(audience && audience.options.length <= 1) Object.entries(AUDIENCES).forEach(([k,v])=>audience.add(new Option(v,k)));
}
function buildSearchItems(type){
  const items = [];
  const push = (label, meta, href, keywords='') => items.push({ label, meta, href, search: `${label} ${meta} ${keywords}`.toLowerCase() });
  const island = activeIsland();

  if(type === 'organizations'){
    DATA.orgs.filter(o => !island || o.islands.includes(island)).forEach(o => push(o.name, `${o.short} - ${visibleIslands(o.islands).map(i=>ISLANDS[i]).join(', ')}`, `organisaties.html?q=${encodeURIComponent(o.name)}`, `${o.themes?.join(' ')} ${o.audiences?.join(' ')}`));
  } else if(type === 'events'){
    DATA.events.filter(e => !island || e.island === island).forEach(e => push(e.title, `${ISLANDS[e.island]} - ${e.date}`, `activiteiten.html?q=${encodeURIComponent(e.title)}`, `${e.short} ${orgName(e.organization_id)} ${e.themes?.join(' ')} ${e.audiences?.join(' ')}`));
  } else {
    DATA.services.filter(s => !island || s.islands.includes(island)).forEach(s => push(s.title, `${orgName(s.organization_id)} - ${visibleIslands(s.islands).map(i=>ISLANDS[i]).join(', ')}`, `aanbod.html?q=${encodeURIComponent(s.title)}`, `${s.short} ${s.themes?.join(' ')} ${s.audiences?.join(' ')}`));
  }

  DATA.themes.forEach(thematic => push(thematic.name, t('themeLabel'), `${type === 'events' ? 'activiteiten' : type === 'organizations' ? 'organisaties' : 'aanbod'}.html?theme=${encodeURIComponent(thematic.slug)}`, thematic.description || ''));
  return items;
}
function enableSmartSearch(render, type){
  const input = document.getElementById('q');
  if(!input) return;
  const panel = document.createElement('div');
  panel.className = 'suggestions';
  panel.hidden = true;
  input.parentNode.insertBefore(panel, input.nextSibling);

  function showSuggestions(){
    const q = input.value.trim().toLowerCase();
    if(q.length < 2){ panel.hidden = true; return; }
    const matches = buildSearchItems(type)
      .filter(item => item.search.includes(q))
      .slice(0, 7);
    if(!matches.length){ panel.hidden = true; return; }
    panel.innerHTML = matches.map(item => `
      <button type="button" class="suggestion" data-value="${escapeHtml(item.label)}">
        <strong>${escapeHtml(item.label)}</strong><span>${escapeHtml(item.meta)}</span>
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
  function updateFilterState(){
    ['island','theme','audience'].forEach(id=>{
      const el=document.getElementById(id);
      if(el) el.classList.toggle('has-value', Boolean(el.value));
    });
    const direct=document.getElementById('direct');
    if(direct) direct.closest('.check')?.classList.toggle('has-value', direct.checked);
  }
  ['island','theme','audience','direct'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('input', () => { updateFilterState(); render(); });
  });
  updateFilterState();
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
function orgName(id){ return DATA.orgs.find(o=>o.id===id)?.name || t('organizationFallback'); }
function visibleIslands(values){
  const island = activeIsland();
  return island && values?.includes(island) ? [island] : (values || []);
}
function labels(values, map, className=''){
  const displayValues = className.includes('island-tag') ? visibleIslands(values) : (values || []);
  return displayValues.map(v=>`<span class="tag ${className}">${escapeHtml(map?.[v]||v)}</span>`).join('');
}
function empty(){ return `<div class="empty">${t('empty')}</div>`; }
function serviceCard(s){
  return `<article class="card service-card"><div class="card-topline">${labels(s.islands,ISLANDS,'island-tag')}${s.direct?`<span class="tag yellow">${t('directTag')}</span>`:''}</div><h3>${escapeHtml(s.title)}</h3><p>${escapeHtml(s.short)}</p><strong>${escapeHtml(orgName(s.organization_id))}</strong><div class="meta">${labels(s.themes,null,'theme-tag')}${labels(s.audiences,AUDIENCES,'audience-tag')}<span class="tag gray">${escapeHtml(s.cost)}</span>${s.online?`<span class="tag gray">${t('onlineTag')}</span>`:''}</div><a class="view-link" href="aanbod.html?q=${encodeURIComponent(s.title)}">${t('viewButton')}</a></article>`;
}
function orgCard(o){
  return `<article class="card org-card"><div class="card-topline">${labels(o.islands,ISLANDS,'island-tag')}</div><h3>${escapeHtml(o.name)}</h3><p>${escapeHtml(o.short)}</p><div class="meta">${labels(o.themes,null,'theme-tag')}${labels(o.audiences,AUDIENCES,'audience-tag')}</div><div class="contact-row">${o.whatsapp?`<a href="https://wa.me/${o.whatsapp.replace(/\D/g,'')}">WhatsApp</a>`:''}${o.phone?`<a href="tel:${escapeHtml(o.phone)}">Bel</a>`:''}${o.email?`<a href="mailto:${escapeHtml(o.email)}">Mail</a>`:''}</div><a class="view-link" href="organisaties.html?q=${encodeURIComponent(o.name)}">${t('viewButton')}</a></article>`;
}
function eventCard(e){
  return `<article class="card event-card"><div class="card-topline"><span class="tag island-tag">${escapeHtml(ISLANDS[e.island])}</span><span class="tag yellow">${escapeHtml(e.date)}</span></div><h3>${escapeHtml(e.title)}</h3><p>${escapeHtml(e.short)}</p><strong>${escapeHtml(e.time)} - ${escapeHtml(orgName(e.organization_id))}</strong><div class="meta">${labels(e.themes,null,'theme-tag')}${labels(e.audiences,AUDIENCES,'audience-tag')}</div><a class="view-link" href="activiteiten.html?q=${encodeURIComponent(e.title)}">${t('viewButton')}</a></article>`;
}
function matchArray(arr,val){ return !val || arr.includes(val); }
function matchText(text,q){ return !q || text.toLowerCase().includes(q); }
async function initHome(){
  await loadData();
  initAppShell();
  renderHomeSections();
  onPreferencesChanged(() => {
    updateHomeHero();
    renderHomeSections();
  });
}
function renderHomeSections(){
  const services = filteredByIsland(DATA.services, item => item.islands);
  const events = filteredByIsland(DATA.events, item => item.island);
  const island = activeIsland();
  document.getElementById('topicGrid').innerHTML = DATA.themes.map(thematic=>{
    const href = `aanbod.html?theme=${encodeURIComponent(thematic.slug)}${island ? `&island=${encodeURIComponent(island)}` : ''}`;
    return `<a class="topic" href="${href}"><strong>${escapeHtml(thematic.name)}</strong><span>${escapeHtml(thematic.description)}</span></a>`;
  }).join('');
  document.getElementById('youthSupport').innerHTML = services.filter(s=>s.audiences?.includes('jongeren')).slice(0,4).map(serviceCard).join('') || empty();
  document.getElementById('parentSupport').innerHTML = services.filter(s=>s.audiences?.includes('ouders') || s.audiences?.includes('professionals')).slice(0,3).map(serviceCard).join('') || empty();
  document.getElementById('upcomingEvents').innerHTML = events.slice(0,3).map(eventCard).join('') || empty();
}
async function initOrganizations(){
  await loadData(); initAppShell(); populateFilters(); applyTranslations(); setParamDefaults(); applyPreferredIslandDefault();
  const render=()=>{ const f=selected(); const rows=DATA.orgs.filter(o=>matchArray(o.islands,f.island)&&matchArray(o.themes,f.theme)&&matchArray(o.audiences,f.audience)&&matchText(o.name+' '+o.short,f.q)); document.getElementById('results').innerHTML=rows.map(orgCard).join('')||empty(); };
  bindFilters(render, 'organizations'); onPreferencesChanged(render); render();
}
async function initServices(){
  await loadData(); initAppShell(); populateFilters(); applyTranslations(); setParamDefaults(); applyPreferredIslandDefault();
  const render=()=>{ const f=selected(); const rows=DATA.services.filter(s=>matchArray(s.islands,f.island)&&matchArray(s.themes,f.theme)&&matchArray(s.audiences,f.audience)&&(!f.direct||s.direct)&&matchText(s.title+' '+s.short+' '+orgName(s.organization_id),f.q)); document.getElementById('results').innerHTML=rows.map(serviceCard).join('')||empty(); };
  bindFilters(render, 'services'); onPreferencesChanged(render); render();
}
async function initEvents(){
  await loadData(); initAppShell(); populateFilters(); applyTranslations(); setParamDefaults(); applyPreferredIslandDefault();
  const render=()=>{ const f=selected(); const rows=DATA.events.filter(e=>(!f.island||e.island===f.island)&&matchArray(e.themes,f.theme)&&matchArray(e.audiences,f.audience)&&matchText(e.title+' '+e.short+' '+orgName(e.organization_id),f.q)); document.getElementById('results').innerHTML=rows.map(eventCard).join('')||empty(); };
  bindFilters(render, 'events'); onPreferencesChanged(render); render();
}
