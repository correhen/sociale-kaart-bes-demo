const DATA = {};
const LANGUAGES = { nl: 'Nederlands', pap: 'Papiamentu', en: 'English', es: 'Espanol' };
const AUDIENCES = { youth: 'Jongeren', professional: 'Professionals', parents: 'Ouder/verzorger' };
const FALLBACK = 'Wordt nog aangevuld';
const CONTACT_TO_EMAIL = 'info@kadenahubenil.com';
const LANGUAGE_FLAGS = {
  nl: 'assets/language-icons/png/rounded/128/flag-nl.png',
  pap: 'assets/language-icons/png/rounded/128/flag-pap.png',
  en: 'assets/language-icons/png/rounded/128/flag-en.png',
  es: 'assets/language-icons/png/rounded/128/flag-es.png'
};
const THEME_VISUALS = {
  help_support: { color: '#00A7A7', icon: 'assets/theme-icons/web/hulp-ondersteuning.webp' },
  school_future: { color: '#FFC400', icon: 'assets/theme-icons/web/school-toekomst.webp' },
  health_wellbeing: { color: '#32B67A', icon: 'assets/theme-icons/web/gezondheid-welzijn.webp' },
  safety_rights: { color: '#1366D6', icon: 'assets/theme-icons/web/veiligheid-rechten.webp' },
  family_system: { color: '#FF7A00', icon: 'assets/theme-icons/web/familie-omgeving.webp' },
  free_time_development: { color: '#7B4DFF', icon: 'assets/theme-icons/web/vrijetijd-ontwikkeling.webp' },
  housing_stay: { color: '#5B6B8D', icon: 'assets/theme-icons/web/wonen-verblijf.webp' },
  direct_help: { color: '#FF4D6D', icon: 'assets/theme-icons/web/directehulp.webp' }
};
const THEME_LABELS = {
  nl: {
    help_support: 'Hulp en ondersteuning',
    school_future: 'School en toekomst',
    health_wellbeing: 'Gezondheid en welzijn',
    safety_rights: 'Veiligheid en rechten',
    family_system: 'Familie en omgang',
    free_time_development: 'Vrije tijd en ontwikkeling',
    housing_stay: 'Wonen en verblijf',
    direct_help: 'Directe hulp'
  },
  pap: {
    help_support: 'Yudansa i sosten',
    school_future: 'Skol i futuro',
    health_wellbeing: 'Salú i bienestar',
    safety_rights: 'Seguridat i bo derecho',
    family_system: 'Famía i trato',
    free_time_development: 'Tempu liber i desaroyo',
    housing_stay: 'Biba i estadia',
    direct_help: 'Yudansa directo'
  },
  en: {
    help_support: 'Help and support',
    school_future: 'School and future',
    health_wellbeing: 'Health and wellbeing',
    safety_rights: 'Safety and your rights',
    family_system: 'Family and relationships',
    free_time_development: 'Free time and development',
    housing_stay: 'Housing and stay',
    direct_help: 'Direct help'
  },
  es: {
    help_support: 'Ayuda y apoyo',
    school_future: 'Escuela y futuro',
    health_wellbeing: 'Salud y bienestar',
    safety_rights: 'Seguridad y tus derechos',
    family_system: 'Familia y relaciones',
    free_time_development: 'Tiempo libre y desarrollo',
    housing_stay: 'Vivienda y estancia',
    direct_help: 'Ayuda directa'
  }
};
const PROFILE_I18N = {
  youth: {
    title: {
      nl: 'Meer over deze organisatie',
      pap: 'Mas tokante e organisashon aki',
      en: 'More about this organisation',
      es: 'Más sobre esta organización'
    },
    fields: [
      { key: 'who_we_are', labels: { nl: 'Wie zijn wij?', pap: 'Ken nos ta?', en: 'Who are we?', es: '¿Quiénes somos?' } },
      { key: 'who_for', labels: { nl: 'Voor wie zijn wij?', pap: 'Pa ken nos ta?', en: 'Who are we for?', es: '¿Para quiénes estamos?' } },
      { key: 'what_help', labels: { nl: 'Waarmee kunnen wij helpen?', pap: 'Ku kiko nos por yuda?', en: 'What can we help with?', es: '¿En qué podemos ayudar?' } },
      { key: 'how_to_access', labels: { nl: 'Hoe kom je bij ons terecht?', pap: 'Kon bo por yega na nos?', en: 'How do you get help?', es: '¿Cómo puedes llegar a nosotros?' } },
      { key: 'how_we_help', labels: { nl: 'Hoe helpen wij?', pap: 'Kon nos ta yuda bo?', en: 'How do we help?', es: '¿Cómo te ayudamos?' } },
      { key: 'duration', labels: { nl: 'Hoelang duurt de hulp?', pap: 'Kuantu tempu e yudansa ta dura?', en: 'How long does support take?', es: '¿Cuánto dura la ayuda?' } },
      { key: 'partners', labels: { nl: 'Met wie werken wij samen?', pap: 'Ku ken nos ta traha huntu?', en: 'Who do we work with?', es: '¿Con quiénes trabajamos?' } },
      { key: 'contact', labels: { nl: 'Contact', pap: 'Kontakto', en: 'Contact', es: 'Contacto' } }
    ]
  },
  professional: {
    title: {
      nl: 'Uitgebreide informatie voor professionals',
      pap: 'Informashon amplio pa profesionalnan',
      en: 'Detailed information for professionals',
      es: 'Información detallada para profesionales'
    },
    groups: [
      {
        key: 'general',
        labels: { nl: 'Algemene informatie', pap: 'Informashon general', en: 'General information', es: 'Información general' },
        fields: [
          { key: 'organisation_name', labels: { nl: 'Naam van de organisatie/instantie?', pap: 'Nòmber di e organisashon/instansia?', en: 'Name of the organisation/agency?', es: '¿Nombre de la organización/instancia?' } },
          { key: 'short_description', labels: { nl: 'Korte omschrijving van de organisatie (wat doet u, wat zijn uw visie en missie?)', pap: 'Deskripshon kòrtiku di e organisashon: kiko boso ta hasi, kiko ta boso vishon i mishon?', en: 'Short description of the organisation: what do you do, and what are your vision and mission?', es: 'Breve descripción de la organización: ¿qué hacen y cuál es su visión y misión?' } },
          { key: 'target_group', labels: { nl: 'Doelgroep?', pap: 'Grupo meta?', en: 'Target group?', es: 'Grupo destinatario' } },
          { key: 'support_offer', labels: { nl: 'Hulpaanbod/dienstverlening? (Hoe komt u in samenwerking met de jeugdige?)', pap: 'Oferta di yudansa/servisio? Kon boso ta drenta den kolaborashon ku e hóben?', en: 'Support/services offered? How do you work with the young person?', es: 'Oferta de ayuda/servicios. ¿Cómo trabajan con el/la joven?' } },
          { key: 'services', labels: { nl: 'Welke diensten biedt u aan?', pap: 'Kua servisionan boso ta ofresé?', en: 'Which services do you offer?', es: '¿Qué servicios ofrecen?' } },
          { key: 'methods', labels: { nl: 'Welke werkwijzen, methodieken of trainingen worden ingezet?', pap: 'Kua forma di trabou, metodikanan òf entrenamentunan boso ta usa?', en: 'Which approaches, methods or training programmes are used?', es: '¿Qué formas de trabajo, metodologías o capacitaciones se utilizan?' } },
          { key: 'execution', labels: { nl: 'Hoe ziet de uitvoering hiervan eruit?', pap: 'Kon e ehersisio di esaki ta mira den práktika?', en: 'What does this look like in practice?', es: '¿Cómo se lleva esto a cabo en la práctica?' } },
          { key: 'problems', labels: { nl: 'Op welke problematiek of hulpvragen is het aanbod gericht?', pap: 'Riba kua problemátika òf preguntanan di yudansa e oferta ta enfoká?', en: 'Which problems or support needs is the offer aimed at?', es: '¿A qué problemáticas o necesidades de ayuda está dirigida la oferta?' } },
          { key: 'trajectory', labels: { nl: 'Hoe verloopt een traject doorgaans?', pap: 'Kon un trayekto normalmente ta bai?', en: 'How does a support pathway usually proceed?', es: '¿Cómo suele desarrollarse un proceso de acompañamiento?' } },
          { key: 'average_duration', labels: { nl: 'Wat is de gemiddelde duur van een traject?', pap: 'Kua ta e durashon promedio di un trayekto?', en: 'What is the average duration of a support pathway?', es: '¿Cuál es la duración promedio de un proceso?' } }
        ]
      },
      {
        key: 'referral',
        labels: { nl: 'Verwijzing en toegankelijkheid', pap: 'Referensia i aksesibilidat', en: 'Referral and accessibility', es: 'Derivación y accesibilidad' },
        fields: [
          { key: 'when_appropriate', labels: { nl: 'Wanneer is doorverwijzing naar uw organisatie passend?', pap: 'Ki ora referí na boso organisashon ta adekuá?', en: 'When is referral to your organisation appropriate?', es: '¿Cuándo es apropiado derivar a alguien a su organización?' } },
          { key: 'criteria', labels: { nl: 'Wat zijn de verwijscriteria? Is een verwijzing nodig, bijvoorbeeld via huisarts of andere professional, of is uw aanbod vrij toegankelijk?', pap: 'Kua ta e kriterianan di referensia? Un referensia ta nesesario, por ehèmpel via dokter di kas òf otro profeshonal, òf boso oferta ta libermente aksesibel?', en: 'What are the referral criteria? Is a referral required, for example through a general practitioner or another professional, or is your service freely accessible?', es: '¿Cuáles son los criterios de derivación? ¿Se necesita una derivación, por ejemplo a través del médico de cabecera u otro profesional, o el servicio es de libre acceso?' } },
          { key: 'indications_required', labels: { nl: 'Zijn er indicaties nodig? Ja/nee.', pap: 'Indikashon ta nesesario? Si/no.', en: 'Are indications required? Yes/no.', es: '¿Se necesitan indicaciones? Sí/no.' } }
        ]
      },
      {
        key: 'practical',
        labels: { nl: 'Praktische informatie', pap: 'Informashon práktiko', en: 'Practical information', es: 'Información práctica' },
        fields: [
          { key: 'contact_details', labels: { nl: 'Contactgegevens', pap: 'Detayenan di kontakto', en: 'Contact details', es: 'Datos de contacto' } },
          { key: 'opening_hours', labels: { nl: 'Openingstijden', pap: 'Orario di apertura', en: 'Opening hours', es: 'Horario de atención' } },
          { key: 'waiting_times', labels: { nl: 'Eventuele wachttijden', pap: 'Eventual tempu di espera', en: 'Any waiting times', es: 'Posibles tiempos de espera' } }
        ]
      },
      {
        key: 'additional',
        labels: { nl: 'Aanvullende informatie voor hulpverleners', pap: 'Informashon adishonal pa dunadónan di yudansa', en: 'Additional information for care providers/professionals', es: 'Información adicional para profesionales' },
        fields: [
          { key: 'partners', labels: { nl: 'Samenwerkingspartners', pap: 'Partnernan di kolaborashon', en: 'Cooperation partners', es: 'Organizaciones colaboradoras' } },
          { key: 'other_information', labels: { nl: 'Overige relevante informatie die belangrijk is voor verwijzers/professionals', pap: 'Otro informashon relevante ku ta importante pa referidónan/profeshonalnan', en: 'Other relevant information that is important for referrers/professionals', es: 'Otra información relevante que sea importante para derivadores/profesionales' } }
        ]
      }
    ]
  }
};
const SEARCH_SYNONYMS = {
  themes: {
    health_wellbeing: ['ziek', 'gezondheid', 'dokter', 'ggd', 'stress', 'somber', 'depressief', 'paniek', 'malu', 'salú', 'médiko', 'tristu', 'strès', 'sick', 'health', 'doctor', 'sad', 'depressed', 'ill', 'enfermo', 'salud', 'médico', 'triste', 'deprimido'],
    direct_help: ['bang', 'nood', 'crisis', 'gevaar', 'help nu', 'mishandeling', 'geweld', 'misbruik', 'abuso', 'peligro', 'krísis', 'yudansa awor', 'danger', 'afraid', 'scared', 'violence', 'abuse', 'emergency', 'miedo', 'violencia', 'emergencia'],
    safety_rights: ['bang', 'politie', 'veilig', 'rechten', 'geweld', 'misbruik', 'mishandeling', 'kpcn', 'polis', 'seguridat', 'derecho', 'abuso', 'police', 'safe', 'rights', 'violence', 'abuse', 'policía', 'seguridad', 'derechos'],
    school_future: ['school', 'huiswerk', 'opleiding', 'stage', 'werk', 'toekomst', 'leren', 'eoz', 'forma', 'rebound', 'skol', 'trabou', 'futuro', 'siña', 'education', 'homework', 'training', 'future', 'learn', 'escuela', 'tarea', 'futuro', 'aprender'],
    family_system: ['thuis', 'ouders', 'familie', 'ruzie', 'gezin', 'omgang', 'scheiding', 'kas', 'mayor', 'famia', 'pleitu', 'home', 'parents', 'family', 'fight', 'relationship', 'casa', 'padres', 'familia', 'pelea'],
    housing_stay: ['thuis', 'wonen', 'opvang', 'slaapplaats', 'dakloos', 'kamer', 'verblijf', 'kas', 'biba', 'refugio', 'housing', 'shelter', 'sleep', 'homeless', 'stay', 'vivienda', 'alojamiento', 'dormir'],
    help_support: ['geld', 'papieren', 'praktisch', 'hulp', 'ondersteuning', 'aanvraag', 'plaka', 'yudansa', 'sosten', 'praktiko', 'money', 'practical', 'support', 'application', 'dinero', 'ayuda', 'apoyo'],
    free_time_development: ['sport', 'hobby', 'vrienden', 'vrije tijd', 'activiteit', 'talent', 'deporte', 'amigu', 'hòbi', 'activity', 'friends', 'free time', 'actividad', 'amigos', 'tiempo libre']
  },
  organizations: {
    'ggd-bonaire': ['ziek', 'dokter', 'gezondheid', 'vaccinatie', 'seks', 'malu', 'médiko', 'salú', 'sick', 'doctor', 'health', 'enfermo', 'médico', 'salud'],
    'ggd-bonaire-salubridat-publiko': ['ziek', 'dokter', 'gezondheid', 'vaccinatie', 'malu', 'salú', 'sick', 'doctor', 'health', 'enfermo', 'salud'],
    'mental-health-caribbean': ['depressief', 'stress', 'somber', 'angst', 'paniek', 'tristu', 'strès', 'depressed', 'sad', 'anxiety', 'ansiedad', 'triste'],
    'mental-health-caribbean-kind-jeugd': ['depressief', 'stress', 'somber', 'angst', 'paniek', 'tristu', 'strès', 'depressed', 'sad', 'anxiety', 'ansiedad', 'triste'],
    'guana-chat-918': ['bang', 'mishandeling', 'geweld', 'misbruik', 'nood', 'crisis', 'abuso', 'violensia', 'afraid', 'violence', 'abuse', 'scared', 'miedo', 'violencia'],
    'korps-politie-caribisch-nederland': ['politie', 'bang', 'veiligheid', 'geweld', 'misbruik', 'kpcn', 'polis', 'seguridat', 'police', 'safety', 'violence', 'policía', 'seguridad'],
    'voogdijraad-caribisch-nederland': ['mishandeling', 'misbruik', 'geweld', 'veilig', 'ouders', 'abuso', 'famia', 'violence', 'abuse', 'parents', 'violencia'],
    'expertisecenter-onderwijs-zorg-bonaire': ['school', 'leren', 'huiswerk', 'eoz', 'skol', 'siña', 'education', 'learn', 'escuela', 'aprender'],
    'fundashon-forma': ['school', 'werk', 'opleiding', 'toekomst', 'forma', 'skol', 'trabou', 'futuro', 'work', 'training', 'future', 'trabajo'],
    'fundashon-forma-skj': ['school', 'werk', 'opleiding', 'toekomst', 'forma', 'skj', 'skol', 'trabou', 'future', 'training'],
    'rebound': ['school', 'vastlopen', 'leren', 'skol', 'siña', 'education', 'learn', 'escuela'],
    'olb-vergoeding-schoolspullen': ['geld', 'school', 'schoolspullen', 'betaling', 'plaka', 'skol', 'money', 'school supplies', 'dinero', 'útiles']
  }
};
const UI_TEXT = {
  nl: { fallback: 'Wordt nog aangevuld', addressMissing: 'Adres wordt nog aangevuld', lastCheckedMissing: 'Nog niet ingevuld', noResults: 'Geen resultaten gevonden. Pas je zoekterm of filter aan.', view: 'Bekijk', backToOrganizations: 'Terug naar organisaties', infoNotice: 'Deze informatie wordt nog aangevuld en gecontroleerd.', professionalDescription: 'Professionele omschrijving', youthDescription: 'Waar kun je voor terecht?', referral: 'Aanmelding / verwijzing', contactHow: 'Hoe neem je contact op?', notes: 'Opmerkingen / controle-status', contact: 'Contact', address: 'Adres', audience: 'Doelgroep', age: 'Leeftijd', lastChecked: 'Laatst gecontroleerd', themes: "Thema's", serviceLabels: 'Onderdelen / labels', youth: 'Jongere', professional: 'Professional', parents: 'Ouder/verzorger', callLabel: 'Bel', mailLabel: 'Mail', websiteLabel: 'Website', whatsappLabel: 'WhatsApp', statusChecked: '' },
  pap: { fallback: 'Ainda pa yena', addressMissing: 'Adres ta wordu yena despues', lastCheckedMissing: 'Ainda no yena', noResults: 'No a haya resultado. Kambia bo buskeda of filtro.', view: 'Wak', backToOrganizations: 'Bek na organisashonnan', infoNotice: 'E informashon aki ta wordu yena i kontrola.', professionalDescription: 'Deskripshon profesional', youthDescription: 'Pa kiko bo por bai aki?', referral: 'Registro / referensia', contactHow: 'Kon bo por tuma kontakto?', notes: 'Nota / status di kontrol', contact: 'Kontakto', address: 'Adres', audience: 'Grupo', age: 'Edat', lastChecked: 'Ultimo kontrol', themes: 'Temanan', serviceLabels: 'Partinan / labelnan', youth: 'Hoben', professional: 'Profesional', parents: 'Mayor/kuidado', callLabel: 'Yama', mailLabel: 'Mail', websiteLabel: 'Website', whatsappLabel: 'WhatsApp', statusChecked: '' },
  en: { fallback: 'To be completed', addressMissing: 'Address will be added', lastCheckedMissing: 'Not filled in yet', noResults: 'No results found. Adjust your search or filter.', view: 'View', backToOrganizations: 'Back to organisations', infoNotice: 'This information is being completed and checked.', professionalDescription: 'Professional description', youthDescription: 'What can you go here for?', referral: 'Access / referral', contactHow: 'How do you get in touch?', notes: 'Notes / review status', contact: 'Contact', address: 'Address', audience: 'Audience', age: 'Age', lastChecked: 'Last checked', themes: 'Topics', serviceLabels: 'Parts / labels', youth: 'Young person', professional: 'Professional', parents: 'Parent/carer', callLabel: 'Call', mailLabel: 'Email', websiteLabel: 'Website', whatsappLabel: 'WhatsApp', statusChecked: '' },
  es: { fallback: 'Por completar', addressMissing: 'La dirección se añadirá', lastCheckedMissing: 'Aún no completado', noResults: 'No se encontraron resultados. Ajusta tu busqueda o filtro.', view: 'Ver', backToOrganizations: 'Volver a organizaciones', infoNotice: 'Esta informacion se esta completando y verificando.', professionalDescription: 'Descripcion profesional', youthDescription: 'Para que puedes acudir?', referral: 'Acceso / derivacion', contactHow: 'Como contactar?', notes: 'Notas / estado de verificacion', contact: 'Contacto', address: 'Direccion', audience: 'Grupo', age: 'Edad', lastChecked: 'Ultima revision', themes: 'Temas', serviceLabels: 'Partes / etiquetas', youth: 'Joven', professional: 'Profesional', parents: 'Padre/madre/cuidador', callLabel: 'Llamar', mailLabel: 'Correo', websiteLabel: 'Sitio web', whatsappLabel: 'WhatsApp', statusChecked: '' }
};
Object.assign(UI_TEXT.nl, {
  navYouth: 'Jongeren',
  navProfessional: 'Professionals',
  organizationsNav: 'Organisaties',
  feedbackNav: 'Feedback',
  languageLabel: 'Taal',
  footerFinal: '© 2026 Kadena Hubenil · Nos Boneiru',
  homeYouthLabel: 'IK BEN JONGERE',
  homeYouthTitle: 'Ik zoek hulp of informatie',
  homeYouthTitleMain: 'Ik zoek hulp',
  homeYouthTitleEm: 'of informatie',
  homeYouthText: 'Vind snel organisaties, activiteiten en hulp op Bonaire.',
  homeContinue: 'Verder',
  homeProLabel: 'WERK JE MET JONGEREN?',
  homeProTitle: 'Voor professionals',
  homeProText: 'Zoek organisaties en verwijsinformatie.',
  homeProCta: 'Naar professionals',
  professionalHeroTitle: 'Voor professionals',
  professionalHeroSubtitle: 'Vind organisaties, contactgegevens en verwijsinformatie voor jongeren en gezinnen op Bonaire.',
  professionalSearchPlaceholder: 'Zoek organisatie, thema of verwijsroute',
  professionalOrganizationsTitle: 'Organisatieoverzicht',
  professionalOrganizationsSubtitle: "Zoek organisaties, thema's en verwijsinformatie.",
  professionalListSearchPlaceholder: 'Zoek op naam, doelgroep, thema of verwijsroute',
  organizationsOverview: 'Organisatieoverzicht',
  registerOrganization: 'Organisatie aanmelden',
  giveFeedback: 'Feedback geven',
  switchToYouth: 'Wissel naar jongeren',
  highlightLanguageTitle: 'In jouw taal',
  highlightLanguageText: 'Duidelijk en simpel uitgelegd.',
  highlightLocalTitle: 'Lokaal & dichtbij',
  highlightLocalText: 'Organisaties op Bonaire die je helpen.',
  highlightCalmTitle: 'Rustig zoeken',
  highlightCalmText: 'Kijk eerst wat bij jouw situatie past.',
  youthHeroTitle: 'Hulp, informatie & kansen op Bonaire',
  youthHeroSubtitle: 'Alles wat je nodig hebt, op één plek.',
  searchPlaceholder: 'Waar zoek je hulp bij?',
  searchButton: 'Zoek',
  popularLabel: 'Populair:',
  allOrganizations: 'Alle organisaties',
  organizationSearchPlaceholder: 'Waar zoek je hulp bij?',
  allThemes: "Alle thema's",
  reset: 'Reset',
  youthOrganizationsTitle: 'Organisaties die er voor jou zijn',
  youthOrganizationsSubtitle: 'Zoek op onderwerp, naam of situatie. We helpen je snel de juiste plek te vinden.',
  organizationType: 'Organisatie',
  themeType: 'Thema',
  organizationEmpty: 'Geen resultaten gevonden. Probeer een ander woord of thema.',
  callLabel: 'Bel',
  emergencyLabel: 'Nood',
  mailLabel: 'Mail',
  websiteLabel: 'Website',
  statusChecked: '',
  goodToKnow: 'Goed om te weten',
  forWhom: 'Ook handig voor',
  themesTitle: "Thema's",
  recentOrganizations: 'Laatst bekeken organisaties',
  popularOrganizations: 'Populaire organisaties',
  printDetail: 'Print of bewaar als PDF',
  signupTitle: 'Nieuwe organisatie of aanbod doorgeven',
  signupIntro: 'Ken je een organisatie of aanbod dat jongeren op Bonaire kan helpen? Geef het hier door.',
  orgNameLabel: 'Organisatienaam',
  contactPersonLabel: 'Contactpersoon',
  emailLabel: 'E-mail',
  phoneLabel: 'Telefoon',
  descriptionLabel: 'Korte omschrijving',
  formThemesLabel: "Thema's",
  formAudienceLabel: 'Doelgroep',
  messageLabel: 'Bericht / opmerking',
  submitSignup: 'Aanmelden',
  feedbackTitle: 'Help deze pagina actueel te houden',
  feedbackIntro: 'Geef door wat mist, onduidelijk is of gecontroleerd moet worden.',
  nameLabel: 'Naam',
  organizationLabel: 'Organisatie',
  noSpecificOrganization: 'Geen specifieke organisatie',
  submitFeedback: 'Verstuur feedback',
  requiredError: 'Vul de verplichte velden in.',
  signupSuccess: 'Dank je. Je aanmelding is ontvangen.',
  feedbackSuccess: 'Dank je. Je feedback is ontvangen.',
  requiredMark: '*'
});
Object.assign(UI_TEXT.pap, {
  navYouth: 'Hóbennan',
  navProfessional: 'Profesionalnan',
  organizationsNav: 'Organisashonnan',
  feedbackNav: 'Feedback',
  languageLabel: 'Idioma',
  footerFinal: '© 2026 Kadena Hubenil · Nos Boneiru',
  homeYouthLabel: 'MI TA HOBEN',
  homeYouthTitle: 'Mi ta buska yudansa òf informashon',
  homeYouthTitleMain: 'Mi ta buska yudansa',
  homeYouthTitleEm: 'òf informashon',
  homeYouthText: 'Haya organisashonnan, aktividatnan i yudansa na Boneiru rapidamente.',
  homeContinue: 'Sigi',
  homeProLabel: 'BO TA TRAHA KU HÓBENNAN?',
  homeProTitle: 'Pa profesionalnan',
  homeProText: 'Busca organisashonnan i informashon pa referí.',
  homeProCta: 'Pa profesionalnan',
  professionalHeroTitle: 'Pa profesionalnan',
  professionalHeroSubtitle: 'Busca organisashonnan, kontakto i informashon pa referí hobennan i famianan na Boneiru.',
  professionalSearchPlaceholder: 'Busca organisashon, tema òf ruta di referensia',
  professionalOrganizationsTitle: 'Bista di organisashonnan',
  professionalOrganizationsSubtitle: 'Busca organisashonnan, temanan i informashon pa referí.',
  professionalListSearchPlaceholder: 'Busca riba nòmber, grupo, tema òf ruta di referensia',
  organizationsOverview: 'Bista di organisashonnan',
  registerOrganization: 'Registrá organisashon',
  giveFeedback: 'Duna feedback',
  switchToYouth: 'Bai pa hobennan',
  highlightLanguageTitle: 'Den bo idioma',
  highlightLanguageText: 'Splika klaro i simpel.',
  highlightLocalTitle: 'Lokal i serka',
  highlightLocalText: 'Organisashonnan na Boneiru ku por yuda bo.',
  highlightCalmTitle: 'Busca trankil',
  highlightCalmText: 'Wak prome kiko ta pas ku bo situashon.',
  youthHeroTitle: 'Yudansa, informashon i oportunidat na Boneiru',
  youthHeroSubtitle: 'Tur loke bo tin mester, na un luga.',
  searchPlaceholder: 'Unda bo ta busca yudansa?',
  searchButton: 'Busca',
  popularLabel: 'Popular:',
  allOrganizations: 'Tur organisashonnan',
  organizationSearchPlaceholder: 'Unda bo ta busca yudansa?',
  allThemes: 'Tur temanan',
  reset: 'Reset',
  youthOrganizationsTitle: 'Organisashonnan ku ta ei pa bo',
  youthOrganizationsSubtitle: 'Busca riba tema, nòmber òf situashon. Nos ta yuda bo haña e lugá ku ta pas.',
  organizationType: 'Organisashon',
  themeType: 'Tema',
  organizationEmpty: 'No a haña resultado. Purba un otro palabra òf tema.',
  callLabel: 'Yama',
  emergencyLabel: 'Emergensia',
  mailLabel: 'Mail',
  websiteLabel: 'Website',
  statusChecked: '',
  goodToKnow: 'Bon pa sa',
  forWhom: 'Tambe útil pa',
  themesTitle: 'Temanan',
  recentOrganizations: 'Organisashonnan wak ultimo',
  popularOrganizations: 'Organisashonnan popular',
  printDetail: 'Print òf warda komo PDF',
  signupTitle: 'Duna un organisashon òf oferta nobo',
  signupIntro: 'Bo sa di un organisashon òf oferta ku por yuda hóbennan na Boneiru? Duna e informashon aki.',
  orgNameLabel: 'Nòmber di organisashon',
  contactPersonLabel: 'Persona di kontakto',
  emailLabel: 'E-mail',
  phoneLabel: 'Telefòn',
  descriptionLabel: 'Deskripshon kòrtiku',
  formThemesLabel: 'Temanan',
  formAudienceLabel: 'Grupo',
  messageLabel: 'Mensaje / remarka',
  submitSignup: 'Manda',
  feedbackTitle: 'Yuda tene e página aki aktual',
  feedbackIntro: 'Duna nos sa kiko falta, kiko no ta kla òf kiko mester kontrol.',
  nameLabel: 'Nòmber',
  organizationLabel: 'Organisashon',
  noSpecificOrganization: 'Ningun organisashon spesífiko',
  submitFeedback: 'Manda feedback',
  requiredError: 'Yena e kamponan obligá.',
  signupSuccess: 'Danki. Nos a risibí bo informashon.',
  feedbackSuccess: 'Danki. Nos a risibí bo feedback.',
  requiredMark: '*'
});
Object.assign(UI_TEXT.en, {
  navYouth: 'Youth',
  navProfessional: 'Professionals',
  organizationsNav: 'Organisations',
  feedbackNav: 'Feedback',
  languageLabel: 'Language',
  footerFinal: '© 2026 Kadena Hubenil · Nos Boneiru',
  homeYouthLabel: 'I AM A YOUNG PERSON',
  homeYouthTitle: 'I need help or information',
  homeYouthTitleMain: 'I need help',
  homeYouthTitleEm: 'or information',
  homeYouthText: 'Find organisations, activities and support on Bonaire.',
  homeContinue: 'Continue',
  homeProLabel: 'DO YOU WORK WITH YOUNG PEOPLE?',
  homeProTitle: 'For professionals',
  homeProText: 'Find organisations and referral information.',
  homeProCta: 'For professionals',
  professionalHeroTitle: 'For professionals',
  professionalHeroSubtitle: 'Find organisations, contact details and referral information for young people and families on Bonaire.',
  professionalSearchPlaceholder: 'Search organisation, topic or referral route',
  professionalOrganizationsTitle: 'Organisation overview',
  professionalOrganizationsSubtitle: 'Search organisations, themes and referral information.',
  professionalListSearchPlaceholder: 'Search by name, audience, topic or referral route',
  organizationsOverview: 'Organisation overview',
  registerOrganization: 'Register organisation',
  giveFeedback: 'Give feedback',
  switchToYouth: 'Switch to young people',
  highlightLanguageTitle: 'In your language',
  highlightLanguageText: 'Clear and simple explanations.',
  highlightLocalTitle: 'Local and nearby',
  highlightLocalText: 'Organizations on Bonaire that can help.',
  highlightCalmTitle: 'Search calmly',
  highlightCalmText: 'First see what fits your situation.',
  youthHeroTitle: 'Help, information & opportunities on Bonaire',
  youthHeroSubtitle: 'Everything you need, in one place.',
  searchPlaceholder: 'What do you need help with?',
  searchButton: 'Search',
  popularLabel: 'Popular:',
  allOrganizations: 'All organizations',
  organizationSearchPlaceholder: 'What do you need help with?',
  allThemes: 'All topics',
  reset: 'Reset',
  youthOrganizationsTitle: 'Organisations here for you',
  youthOrganizationsSubtitle: 'Search by topic, name or situation. Find the right place faster.',
  organizationType: 'Organisation',
  themeType: 'Topic',
  organizationEmpty: 'No results found. Try another word or topic.',
  callLabel: 'Call',
  emergencyLabel: 'Emergency',
  mailLabel: 'Email',
  websiteLabel: 'Website',
  statusChecked: '',
  goodToKnow: 'Good to know',
  forWhom: 'Also relevant for',
  themesTitle: 'Topics',
  recentOrganizations: 'Recently viewed organisations',
  popularOrganizations: 'Featured organisations',
  printDetail: 'Print or save as PDF',
  signupTitle: 'Submit a new organisation or service',
  signupIntro: 'Know an organisation or service that can help young people on Bonaire? Share it here.',
  orgNameLabel: 'Organisation name',
  contactPersonLabel: 'Contact person',
  emailLabel: 'Email',
  phoneLabel: 'Phone',
  descriptionLabel: 'Short description',
  formThemesLabel: 'Themes',
  formAudienceLabel: 'Audience',
  messageLabel: 'Message / note',
  submitSignup: 'Submit',
  feedbackTitle: 'Help keep this page up to date',
  feedbackIntro: 'Tell us what is missing, unclear or should be checked.',
  nameLabel: 'Name',
  organizationLabel: 'Organisation',
  noSpecificOrganization: 'No specific organisation',
  submitFeedback: 'Send feedback',
  requiredError: 'Fill in the required fields.',
  signupSuccess: 'Thank you. Your submission has been received.',
  feedbackSuccess: 'Thank you. Your feedback has been received.',
  requiredMark: '*'
});
Object.assign(UI_TEXT.es, {
  navYouth: 'Jóvenes',
  navProfessional: 'Profesionales',
  organizationsNav: 'Organizaciones',
  feedbackNav: 'Comentarios',
  languageLabel: 'Idioma',
  footerFinal: '© 2026 Kadena Hubenil · Nos Boneiru',
  homeYouthLabel: 'SOY JOVEN',
  homeYouthTitle: 'Busco ayuda o información',
  homeYouthTitleMain: 'Busco ayuda',
  homeYouthTitleEm: 'o información',
  homeYouthText: 'Encuentra organizaciones, actividades y apoyo en Bonaire.',
  homeContinue: 'Continuar',
  homeProLabel: '¿TRABAJAS CON JÓVENES?',
  homeProTitle: 'Para profesionales',
  homeProText: 'Busca organizaciones e información para derivar.',
  homeProCta: 'Para profesionales',
  professionalHeroTitle: 'Para profesionales',
  professionalHeroSubtitle: 'Busca organizaciones, datos de contacto e información para derivar a jóvenes y familias en Bonaire.',
  professionalSearchPlaceholder: 'Busca organización, tema o ruta de derivación',
  professionalOrganizationsTitle: 'Resumen de organizaciones',
  professionalOrganizationsSubtitle: 'Busca organizaciones, temas e información para derivar.',
  professionalListSearchPlaceholder: 'Busca por nombre, grupo, tema o ruta de derivación',
  organizationsOverview: 'Resumen de organizaciones',
  registerOrganization: 'Registrar organización',
  giveFeedback: 'Enviar comentarios',
  switchToYouth: 'Cambiar a jóvenes',
  highlightLanguageTitle: 'En tu idioma',
  highlightLanguageText: 'Explicado claro y simple.',
  highlightLocalTitle: 'Local y cercano',
  highlightLocalText: 'Organizaciones en Bonaire que te ayudan.',
  highlightCalmTitle: 'Busca con calma',
  highlightCalmText: 'Mira primero que encaja con tu situacion.',
  youthHeroTitle: 'Ayuda, información y oportunidades en Bonaire',
  youthHeroSubtitle: 'Todo lo que necesitas, en un solo lugar.',
  searchPlaceholder: '¿Con qué necesitas ayuda?',
  searchButton: 'Buscar',
  popularLabel: 'Popular:',
  allOrganizations: 'Todas las organizaciones',
  organizationSearchPlaceholder: '¿Con qué necesitas ayuda?',
  allThemes: 'Todos los temas',
  reset: 'Restablecer',
  youthOrganizationsTitle: 'Organizaciones que están para ayudarte',
  youthOrganizationsSubtitle: 'Busca por tema, nombre o situación. Encuentra más rápido el lugar adecuado.',
  organizationType: 'Organización',
  themeType: 'Tema',
  organizationEmpty: 'No se encontraron resultados. Prueba otra palabra o tema.',
  callLabel: 'Llamar',
  emergencyLabel: 'Emergencia',
  mailLabel: 'Correo',
  websiteLabel: 'Sitio web',
  statusChecked: '',
  goodToKnow: 'Bueno saber',
  forWhom: 'También útil para',
  themesTitle: 'Temas',
  recentOrganizations: 'Organizaciones vistas recientemente',
  popularOrganizations: 'Organizaciones populares',
  printDetail: 'Imprimir o guardar como PDF',
  signupTitle: 'Enviar una nueva organización o servicio',
  signupIntro: '¿Conoces una organización o servicio que pueda ayudar a jóvenes en Bonaire? Compártelo aquí.',
  orgNameLabel: 'Nombre de la organización',
  contactPersonLabel: 'Persona de contacto',
  emailLabel: 'Correo',
  phoneLabel: 'Teléfono',
  descriptionLabel: 'Descripción breve',
  formThemesLabel: 'Temas',
  formAudienceLabel: 'Grupo',
  messageLabel: 'Mensaje / nota',
  submitSignup: 'Enviar',
  feedbackTitle: 'Ayuda a mantener esta página actualizada',
  feedbackIntro: 'Dinos qué falta, qué no está claro o qué debe revisarse.',
  nameLabel: 'Nombre',
  organizationLabel: 'Organización',
  noSpecificOrganization: 'Ninguna organización específica',
  submitFeedback: 'Enviar comentarios',
  requiredError: 'Completa los campos obligatorios.',
  signupSuccess: 'Gracias. Hemos recibido tu información.',
  feedbackSuccess: 'Gracias. Hemos recibido tus comentarios.',
  requiredMark: '*'
});

const STORAGE = {
  language: 'preferredLanguage',
  audience: 'audience_preference',
  submissions: 'kadena_demo_submissions',
  recentYouthOrganizations: 'kadena_recent_youth_organizations',
  recentProfessionalOrganizations: 'kadena_recent_professional_organizations'
};

function assetBase(){
  return window.SEED_BASE || '';
}

function currentIsland(){
  const configured = String(window.ISLAND_CONTEXT || document.body?.dataset?.island || '').toLowerCase();
  if(['bonaire', 'statia', 'saba'].includes(configured)) return configured;
  const firstSegment = location.pathname.split('/').filter(Boolean)[0]?.toLowerCase();
  return ['statia', 'saba'].includes(firstSegment) ? firstSegment : 'bonaire';
}

function islandName(island = currentIsland()){
  return island === 'statia' ? 'St. Eustatius' : island === 'saba' ? 'Saba' : 'Bonaire';
}

function dataUrl(path){
  return `${assetBase()}data/${path}`;
}

function ui(key, lang = currentLanguage()){
  return UI_TEXT[lang]?.[key] || UI_TEXT.nl[key] || key;
}

function fallbackText(){
  return ui('fallback');
}

function pageUrl(path){
  return `${assetBase()}${path}`;
}

function assetUrl(path){
  return `${assetBase()}${path}`;
}

async function fetchJson(path, fallback){
  try {
    const response = await fetch(dataUrl(path));
    if(!response.ok) throw new Error(`HTTP ${response.status}`);
    return await response.json();
  } catch(error) {
    console.warn(`Kon ${path} niet laden`, error);
    return fallback;
  }
}

async function fetchPublicSeedData(){
  const island = currentIsland();
  const apiUrl = `${assetBase()}api/seeddata.php?island=${encodeURIComponent(island)}`;
  try {
    const response = await fetch(apiUrl, {
      cache: 'no-store',
      headers: { Accept: 'application/json' }
    });
    if(!response.ok) throw new Error(`HTTP ${response.status}`);
    const seed = await response.json();
    if(!seed || !Array.isArray(seed.themes) || !Array.isArray(seed.organizations)) {
      throw new Error('Ongeldige API-data');
    }
    return seed;
  } catch(error) {
    console.warn('Publieke database-API niet beschikbaar; statische seeddata wordt gebruikt.', error);
    const bonaireSeed = await fetchJson('kadena_hubentut_seeddata_bonaire_v0_1.json', {
      themes: [],
      organizations: [],
      metadata: {}
    });
    if(island === 'bonaire') return bonaireSeed;
    const islandSeed = await fetchJson('youthcare_compass_saba_statia_v0_1.json', {
      themes: [],
      organizations: [],
      metadata: {}
    });
    return {
      metadata: { ...(bonaireSeed.metadata || {}), ...(islandSeed.metadata || {}), requested_island: island },
      themes: bonaireSeed.themes || [],
      organizations: (islandSeed.organizations || []).filter(org => org.island === island)
    };
  }
}

async function fetchAdminContext(){
  if(DATA.adminContext) return DATA.adminContext;
  if(DATA.adminContextPromise) return DATA.adminContextPromise;

  DATA.adminContextPromise = fetch(`${assetBase()}api/admin_context.php`, {
    cache: 'no-store',
    credentials: 'same-origin',
    headers: { Accept: 'application/json' }
  }).then(async response => {
    if(!response.ok) throw new Error(`HTTP ${response.status}`);
    const context = await response.json();
    DATA.adminContext = context?.authenticated ? context : { authenticated: false };
    return DATA.adminContext;
  }).catch(error => {
    console.warn('Beheercontext kon niet worden geladen.', error);
    DATA.adminContext = { authenticated: false };
    return DATA.adminContext;
  });

  return DATA.adminContextPromise;
}

async function load_seed_data(){
  if(DATA.loaded) return DATA;

  const seed = await fetchPublicSeedData();

  const legacy = await Promise.all([
    fetchJson('services.json', []),
    fetchJson('events.json', []),
    fetchJson('organizations.json', [])
  ]);

  DATA.seed = seed;
  DATA.metadata = seed.metadata || {};
  DATA.themes = Array.isArray(seed.themes) ? seed.themes.slice().sort((a,b)=>(a.order || 0) - (b.order || 0)) : [];
  DATA.organizations = Array.isArray(seed.organizations) ? seed.organizations.map(normalizeOrganization) : [];
  DATA.orgs = DATA.organizations.map(toLegacyOrg);
  DATA.services = legacy[0] || [];
  DATA.events = legacy[1] || [];
  DATA.legacyOrganizations = legacy[2] || [];
  DATA.loaded = true;
  return DATA;
}

const loadSeedData = load_seed_data;
const loadData = load_seed_data;

function get_themes(){
  return DATA.themes || [];
}

function get_organizations(){
  return DATA.organizations || [];
}

function get_organization_by_slug(slug){
  return get_organizations().find(org => org.slug === slug);
}

function currentLanguage(){
  const urlLang = new URLSearchParams(location.search).get('lang');
  if(LANGUAGES[urlLang]) return urlLang;
  const island = currentIsland();
  const storageKey = island === 'bonaire' ? STORAGE.language : `${STORAGE.language}_${island}`;
  const stored = safeGet(storageKey);
  return LANGUAGES[stored] ? stored : (island === 'bonaire' ? 'nl' : 'en');
}

function setLanguage(lang){
  if(!LANGUAGES[lang]) return;
  const island = currentIsland();
  safeSet(island === 'bonaire' ? STORAGE.language : `${STORAGE.language}_${island}`, lang);
  document.documentElement.lang = lang;
  document.querySelectorAll('[data-language-select]').forEach(select => { select.value = lang; });
  updateLanguageFlag();
  applyStaticTranslations();
  renderCurrentPage();
}

function currentAudience(){
  const stored = safeGet(STORAGE.audience);
  return stored === 'professional' ? 'professional' : stored === 'youth' ? 'youth' : '';
}

function setAudiencePreference(audience){
  if(!['youth','professional'].includes(audience)) return;
  safeSet(STORAGE.audience, audience);
  document.cookie = `audience_preference=${audience}; path=/; max-age=15552000; SameSite=Lax`;
}

function safeGet(key){
  try { return localStorage.getItem(key) || ''; } catch(error) { return ''; }
}

function safeSet(key, value){
  try { localStorage.setItem(key, value); } catch(error) {}
}

function safeJsonGet(key, fallback = []){
  try {
    const value = JSON.parse(localStorage.getItem(key) || 'null');
    return Array.isArray(value) ? value : fallback;
  } catch(error) {
    return fallback;
  }
}

function safeJsonSet(key, value){
  try { localStorage.setItem(key, JSON.stringify(value)); } catch(error) {}
}

function getTranslatedValue(value, lang = currentLanguage(), fallbackLang = 'nl'){
  if(value == null) return '';
  if(typeof value === 'string' || typeof value === 'number') return String(value);
  if(Array.isArray(value)) return value.map(item => getTranslatedValue(item, lang, fallbackLang)).filter(Boolean).join(', ');
  if(typeof value === 'object') {
    if(value[lang] && typeof value[lang] !== 'object') return String(value[lang]);
    if(value[fallbackLang] && typeof value[fallbackLang] !== 'object') return String(value[fallbackLang]);
    if(currentIsland() !== 'bonaire' && value.en && typeof value.en !== 'object') return String(value.en);
    if(value[lang]?.text) return String(value[lang].text);
    if(value[fallbackLang]?.text) return String(value[fallbackLang].text);
    if(currentIsland() !== 'bonaire' && value.en?.text) return String(value.en.text);
    if(value.text) return String(value.text);
    if(value.name) return getTranslatedValue(value.name, lang, fallbackLang);
    if(value.value) return getTranslatedValue(value.value, lang, fallbackLang);
  }
  return '';
}

function getTranslatedField(item, field, lang = currentLanguage(), fallbackLang = 'nl'){
  const translated = getTranslatedValue(item?.translations?.[lang]?.[field], lang, fallbackLang);
  if(translated) return translated;
  if(currentIsland() !== 'bonaire') {
    const english = getTranslatedValue(item?.translations?.en?.[field], 'en', 'en');
    if(english) return english;
  }
  const fallback = getTranslatedValue(item?.translations?.[fallbackLang]?.[field], fallbackLang, fallbackLang);
  if(fallback) return fallback;
  return getTranslatedValue(item?.[field], lang, fallbackLang);
}

function get_translated_field(item, field, lang = currentLanguage()){
  return getTranslatedField(item, field, lang);
}

function getOrganizationText(org, field, lang = currentLanguage()){
  const aliases = {
    name: ['name', 'youth_title', 'title'],
    short: ['short', 'youth_short', 'professional_summary'],
    youth_short: ['youth_short', 'short', 'professional_summary'],
    youth_where_can_you_go: ['youth_where_can_you_go', 'youth_short', 'short'],
    professional_summary: ['professional_summary', 'professional_description', 'short', 'youth_short'],
    professional_referral_or_access: ['professional_referral_or_access', 'referral_route', 'access_route', 'youth_how_it_works'],
    professional_notes: ['professional_notes', 'notes', 'review_notes'],
    type: ['type'],
    age_range: ['age_range']
  };
  const fields = aliases[field] || [field];
  for(const candidate of fields) {
    const value = getTranslatedField(org, candidate, lang);
    if(value) return value;
  }
  return '';
}

function getThemeText(theme, field, lang = currentLanguage()){
  return getTranslatedField(theme, field, lang);
}

function getAudienceThemeText(theme, audience, lang = currentLanguage()){
  const labels = theme?.labels?.[audience];
  const id = theme?.id || theme?.slug;
  if(labels?.[lang]) return labels[lang];
  if(THEME_LABELS[lang]?.[id]) return THEME_LABELS[lang][id];
  const translated = getThemeText(theme, 'name', lang);
  if(translated) return translated;
  if(labels?.nl) return labels.nl;
  return THEME_LABELS.nl?.[id] || '';
}

function normalizeAudienceValue(value){
  const normalized = String(value || '').toLowerCase().trim();
  if(['professional','professionals','professional(s)'].includes(normalized)) return 'professional';
  if(['youth','jongere','jongeren'].includes(normalized)) return 'youth';
  if(['parent','parents','ouder','ouders','ouder/verzorger'].includes(normalized)) return 'parents';
  return normalized;
}

function normalizeOrganization(org){
  const primary = Array.isArray(org.primary_theme_ids) ? org.primary_theme_ids : [];
  const secondary = Array.isArray(org.secondary_theme_ids) ? org.secondary_theme_ids : [];
  const status = org.status || 'active';
  return {
    ...org,
    island: org.island || 'bonaire',
    status,
    active: status === 'active' && org.visibility?.default !== false,
    themes: [...new Set([...primary, ...secondary])],
    audiences: Array.isArray(org.target_audiences) ? [...new Set(org.target_audiences.map(normalizeAudienceValue).filter(Boolean))] : [],
    contact: org.contact || {},
    review_flags: org.review_flags || {},
    logo_url: org.logo_url || org.logo || '',
    image_url: org.image_url || org.thumbnail_url || '',
    demo: Boolean(org.demo_status || org.review_flags?.needs_content_review || org.review_flags?.replace_with_official_format_when_available)
  };
}

function toLegacyOrg(org){
  return {
    id: org.id,
    name: org.name,
    slug: org.slug,
    islands: [org.island],
    themes: org.themes.map(themeName),
    audiences: org.audiences,
    short: getOrganizationText(org, 'youth_short', 'nl') || getOrganizationText(org, 'professional_summary', 'nl') || FALLBACK,
    phone: org.contact.phone || org.contact.phone_bonaire || org.contact.phone_whatsapp || org.contact.fallback_phone || '',
    whatsapp: org.contact.phone_whatsapp || '',
    email: org.contact.email || ''
  };
}

function themeById(idOrSlug){
  return get_themes().find(theme => theme.id === idOrSlug || theme.slug === idOrSlug);
}

function themeName(idOrSlug, lang = currentLanguage()){
  const theme = themeById(idOrSlug);
  return getThemeText(theme, 'name', lang) || idOrSlug || FALLBACK;
}

function audienceThemeName(idOrSlug, audience, lang = currentLanguage()){
  const theme = themeById(idOrSlug);
  return getAudienceThemeText(theme, audience, lang) || idOrSlug || FALLBACK;
}

function themeShort(idOrSlug, lang = currentLanguage()){
  const theme = themeById(idOrSlug);
  return getThemeText(theme, 'short', lang) || '';
}

function themeSlug(idOrSlug){
  const theme = themeById(idOrSlug);
  return theme?.slug || idOrSlug;
}

function themeVisual(idOrSlug){
  const theme = themeById(idOrSlug);
  const id = theme?.id || idOrSlug;
  return THEME_VISUALS[id] || { color: theme?.color || '#03A8AF', icon: '' };
}

function themeIconMarkup(themeId, className, label = ''){
  const visual = themeVisual(themeId);
  if(!visual.icon || !visual.icon.includes('assets/theme-icons/web/') || !visual.icon.endsWith('.webp')) return '';
  return `<img class="${escapeHtml(className)}" src="${escapeHtml(assetUrl(visual.icon))}" width="52" height="52" loading="lazy" alt="${escapeHtml(label)}" />`;
}

function escapeHtml(value){
  return String(value ?? '').replace(/[&<>"']/g, ch => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[ch]));
}

function normalizeText(value){
  return String(value || '').toLowerCase();
}

function searchSynonymsForOrganization(org){
  const themeWords = org.themes.flatMap(id => {
    const theme = themeById(id);
    return SEARCH_SYNONYMS.themes[theme?.id || id] || [];
  });
  const orgWords = SEARCH_SYNONYMS.organizations[org.slug] || [];
  return [...themeWords, ...orgWords];
}

function searchHaystack(org){
  const lang = currentLanguage();
  const contact = org.contact || {};
  const fields = [
    getOrganizationText(org, 'name', lang),
    getOrganizationText(org, 'type', lang),
    getOrganizationText(org, 'age_range', lang),
    getOrganizationText(org, 'youth_short', lang),
    getOrganizationText(org, 'youth_where_can_you_go', lang),
    getOrganizationText(org, 'professional_summary', lang),
    getOrganizationText(org, 'professional_referral_or_access', lang),
    org.audiences.map(audienceLabel).join(' '),
    contact.website,
    contact.email,
    contact.phone,
    contact.phone_whatsapp,
    ...(org.search_keywords_nl || []),
    ...searchSynonymsForOrganization(org),
    ...org.themes.map(id => `${themeName(id, lang)} ${audienceThemeName(id, 'youth', lang)} ${audienceThemeName(id, 'professional', lang)} ${themeShort(id, lang)}`)
  ];
  return normalizeText(fields.join(' '));
}

function filter_organizations(filters = {}){
  const q = normalizeText(filters.q);
  return get_organizations().filter(org => {
    const activeOk = filters.includeArchived || org.active;
    const themeOk = !filters.theme || org.themes.some(id => themeSlug(id) === filters.theme || id === filters.theme);
    const audienceOk = !filters.audience || org.audiences.includes(filters.audience);
    const islandOk = !filters.island || org.island === filters.island;
    const qOk = !q || searchHaystack(org).includes(q);
    return activeOk && themeOk && audienceOk && islandOk && qOk;
  });
}

function renderCurrentPage(){
  const id = document.body.dataset.page;
  if(id === 'audience-choice') renderAudienceChoice();
  if(id === 'audience-home') renderAudienceHome(document.body.dataset.audience);
  if(id === 'organization-list') {
    populateKadenaFilters(true);
    renderOrganizationList(document.body.dataset.audience);
  }
  if(id === 'organization-detail') renderOrganizationDetail(document.body.dataset.audience);
}

function initShell(){
  document.documentElement.lang = currentLanguage();
  document.body.dataset.audience = document.body.dataset.audience || currentAudience();
  document.querySelectorAll('[data-audience-link]').forEach(link => {
    link.addEventListener('click', () => setAudiencePreference(link.dataset.audienceLink));
  });
  document.querySelectorAll('[data-language-slot]').forEach(slot => {
    slot.innerHTML = languageSwitcher();
  });
  document.querySelectorAll('[data-language-select]').forEach(select => {
    select.value = currentLanguage();
    select.addEventListener('change', event => setLanguage(event.target.value));
  });
  bindCardNavigation();
  syncShellText();
  applyStaticTranslations();
  updateLanguageFlag();
}

function languageSwitcher(){
  const lang = currentLanguage();
  return `<label class="language-switch">
    <span class="language-switch__icon" aria-hidden="true">${iconSvg('globe')}</span>
    <span class="language-switch__flag" data-language-flag style="background-image:url('${escapeHtml(assetUrl(LANGUAGE_FLAGS[lang]))}')"></span>
    <span class="sr-only" data-language-label>${escapeHtml(ui('languageLabel'))}</span>
    <select data-language-select aria-label="${escapeHtml(ui('languageLabel'))}">
      <option value="nl">Nederlands</option>
      <option value="pap">Papiamentu</option>
      <option value="en">English</option>
      <option value="es">Español</option>
    </select>
  </label>`;
}

function updateLanguageFlag(){
  const lang = currentLanguage();
  document.querySelectorAll('[data-language-flag]').forEach(flag => {
    flag.style.backgroundImage = `url("${assetUrl(LANGUAGE_FLAGS[lang] || LANGUAGE_FLAGS.nl)}")`;
  });
}

function applyStaticTranslations(){
  syncShellText();
  document.querySelectorAll('[data-i18n]').forEach(item => {
    item.textContent = ui(item.dataset.i18n);
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(item => {
    item.setAttribute('placeholder', ui(item.dataset.i18nPlaceholder));
  });
  const youthSubtitle = document.querySelector('.youth-hero-copy > p');
  if(youthSubtitle) youthSubtitle.textContent = ui('youthHeroSubtitle');
  const organizationSelect = document.getElementById('organization');
  if(organizationSelect?.options?.[0]) organizationSelect.options[0].textContent = ui('noSpecificOrganization');
  updatePopularThemeChips();
  renderFormChoiceGroups(document.body.dataset.formKind);
}

function syncShellText(){
  const audience = document.body.dataset.audience || '';
  const island = currentIsland();
  document.querySelectorAll('.brand').forEach(brand => {
    if(island === 'bonaire') {
      brand.textContent = 'Kadena Hubenil';
      return;
    }
    const label = `YouthCare Compass ${island === 'statia' ? 'Statia' : 'Saba'}`;
    const logo = `assets/brand/web/youthcare-compass-${island}-450.webp`;
    brand.classList.add('island-brand');
    brand.innerHTML = `<img src="${escapeHtml(assetUrl(logo))}" alt="${escapeHtml(label)}" />`;
  });
  ensureProfessionalHeaderActions(audience);
  document.querySelectorAll('header nav [data-audience-link="youth"]').forEach(link => { link.textContent = ui('navYouth'); });
  document.querySelectorAll('header nav [data-audience-link="professional"]').forEach(link => { link.textContent = ui('navProfessional'); });
  document.querySelectorAll('header nav a').forEach(link => {
    const href = link.getAttribute('href') || '';
    const text = normalizeText(link.textContent);
    if(href.includes('feedback') || text === 'feedback' || text === 'comentarios') {
      link.textContent = ui('feedbackNav');
      link.hidden = audience === 'youth' || audience === 'professional';
    }
    if(text.includes('organisatie') || text.includes('organisashon') || text.includes('organisation') || text.includes('organizacion') || text.includes('organización')) {
      link.textContent = ui('organizationsNav');
    }
  });
  document.querySelectorAll('.professional-actions [data-i18n]').forEach(link => {
    link.textContent = ui(link.dataset.i18n);
  });
  document.querySelectorAll('[data-language-label]').forEach(label => { label.textContent = ui('languageLabel'); });
  document.querySelectorAll('[data-language-select]').forEach(select => {
    select.setAttribute('aria-label', ui('languageLabel'));
  });
  document.querySelectorAll('.site-footer').forEach(footer => {
    const footerText = island === 'bonaire'
      ? ui('footerFinal')
      : `© 2026 YouthCare Compass ${island === 'statia' ? 'Statia' : 'Saba'}`;
    footer.innerHTML = `<p>${escapeHtml(footerText)}</p>`;
  });
}

function ensureProfessionalHeaderActions(audience){
  document.querySelectorAll('.site-header').forEach(header => {
    let actions = header.querySelector('.professional-actions');
    if(audience !== 'professional') {
      if(actions) actions.hidden = true;
      return;
    }
    if(!actions) {
      const nav = header.querySelector('nav');
      actions = document.createElement('div');
      actions.className = 'header-actions professional-actions';
      const prefix = (window.SEED_BASE || '').replace(/\/$/, '');
      const base = prefix ? `${prefix}/` : '';
      actions.innerHTML = `<a href="${base}organisatie-aanmelden.html" data-i18n="registerOrganization"></a><a href="${base}feedback.html" data-i18n="feedbackNav"></a>`;
      nav?.after(actions);
    }
    actions.hidden = false;
  });
}

function updatePopularThemeChips(){
  document.querySelectorAll('[data-theme-chip]').forEach(chip => {
    const label = audienceThemeName(chip.dataset.themeChip, document.body.dataset.audience || 'youth');
    chip.textContent = label || chip.textContent || fallbackText();
  });
}

function iconSvg(name){
  const paths = {
    phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.13.96.35 1.9.67 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.32 1.84.54 2.8.67A2 2 0 0 1 22 16.92Z"/>',
    globe: '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 0 20"/><path d="M12 2a15.3 15.3 0 0 0 0 20"/>',
    mail: '<path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/>',
    chat: '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/>',
    clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    pin: '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    print: '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>',
    arrow: '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>'
  };
  return `<svg class="icon icon-${escapeHtml(name)}" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${paths[name] || paths.arrow}</svg>`;
}

function bindCardNavigation(){
  if(document.body.dataset.cardNavigationBound) return;
  document.body.dataset.cardNavigationBound = 'true';
  document.addEventListener('click', event => {
    const card = event.target.closest?.('.kh-card[data-card-href]');
    if(!card || event.target.closest('a,button,input,select,textarea')) return;
    location.href = card.dataset.cardHref;
  });
  document.addEventListener('keydown', event => {
    if(event.key !== 'Enter' && event.key !== ' ') return;
    const card = event.target.closest?.('.kh-card[data-card-href]');
    if(!card) return;
    event.preventDefault();
    location.href = card.dataset.cardHref;
  });
}

function missing(value, kind = 'fallback'){
  if(value) return escapeHtml(value);
  const key = kind === 'address' ? 'addressMissing' : kind === 'lastChecked' ? 'lastCheckedMissing' : 'fallback';
  return `<span class="muted">${escapeHtml(ui(key))}</span>`;
}

function contactLinks(contact = {}){
  const phone = contact.phone || contact.phone_bonaire || contact.fallback_phone || contact.phone_whatsapp || '';
  const email = contact.email || '';
  const website = contact.website || '';
  const emergency = contact.emergency || '';
  const link = (href, icon, label) => `<a href="${escapeHtml(href)}">${iconSvg(icon)}<span>${escapeHtml(label)}</span></a>`;
  return [
    phone ? link(`tel:${phone}`, 'phone', `${ui('callLabel')} ${phone}`) : '',
    contact.phone_whatsapp ? link(`https://wa.me/${String(contact.phone_whatsapp).replace(/\D/g,'')}`, 'chat', ui('whatsappLabel')) : '',
    emergency ? link(`tel:${emergency}`, 'phone', `${ui('emergencyLabel')}: ${emergency}`) : '',
    email ? link(`mailto:${email}`, 'mail', ui('mailLabel')) : '',
    website ? `<a href="${escapeHtml(website)}" target="_blank" rel="noopener">${iconSvg('globe')}<span>${escapeHtml(ui('websiteLabel'))}</span></a>` : ''
  ].filter(Boolean).join('');
}

function audienceLabel(audience){
  return ui(audience) || AUDIENCES[audience] || audience;
}

function initAudienceChoice(){
  loadSeedData().then(() => {
    initShell();
    renderAudienceChoice();
  });
}

function renderAudienceChoice(){
  const resume = document.getElementById('resumeChoice');
  if(!resume) return;
  resume.hidden = true;
  resume.innerHTML = '';
}

function initAudienceHome(audience){
  document.body.dataset.page = 'audience-home';
  document.body.dataset.audience = audience;
  loadSeedData().then(() => {
    setAudiencePreference(audience);
    initShell();
    renderAudienceHome(audience);
  });
}

function renderAudienceHome(audience){
  renderThemeTiles(audience);
  renderFeaturedOrganizations(audience);
  bindAudienceSearch(audience);
}

function availableThemes(audience){
  if(currentIsland() === 'bonaire') return get_themes();
  const usedThemes = new Set(
    filter_organizations({ audience, island: currentIsland() })
      .flatMap(org => org.themes)
  );
  return get_themes().filter(theme => usedThemes.has(theme.id) || usedThemes.has(theme.slug));
}

function renderThemeTiles(audience){
  const holder = document.getElementById('themeTiles');
  if(!holder) return;
  holder.innerHTML = availableThemes(audience).map(theme => {
    const name = getAudienceThemeText(theme, audience);
    const short = get_translated_field(theme, 'short');
    const href = `${audience === 'professional' ? 'organisaties/' : 'organisaties/'}?theme=${encodeURIComponent(theme.slug)}`;
    const visual = themeVisual(theme.id);
    const icon = visual.icon ? `<img class="theme-icon" src="${escapeHtml(assetUrl(visual.icon))}" width="76" height="76" loading="lazy" alt="" aria-hidden="true" />` : '<span class="theme-mark" aria-hidden="true"></span>';
    return `<a class="kh-theme-tile theme-${escapeHtml(theme.id || '')}" href="${href}" style="--tile-color:${escapeHtml(visual.color)}">${icon}<strong>${escapeHtml(name || fallbackText())}</strong>${short ? `<span>${escapeHtml(short)}</span>` : ''}</a>`;
  }).join('');
}

function renderFeaturedOrganizations(audience){
  const holder = document.getElementById('featuredOrganizations');
  if(!holder) return;
  const title = document.getElementById('featuredOrganizationsTitle');
  let rows = [];
  if(audience === 'youth') {
    const recentSlugs = safeJsonGet(STORAGE.recentYouthOrganizations);
    rows = recentSlugs
      .map(slug => get_organization_by_slug(slug))
      .filter(org => org?.active && org.audiences.includes('youth'))
      .slice(0, 4);
    if(title) title.textContent = rows.length ? ui('recentOrganizations') : ui('popularOrganizations');
  }
  if(audience === 'professional') {
    const recentSlugs = safeJsonGet(STORAGE.recentProfessionalOrganizations);
    rows = recentSlugs
      .map(slug => get_organization_by_slug(slug))
      .filter(org => org?.active && org.audiences.includes('professional'))
      .slice(0, 4);
    if(title) title.textContent = rows.length ? ui('recentOrganizations') : ui('popularOrganizations');
  }
  rows = rows.filter(org => org.island === currentIsland());
  if(!rows.length) rows = filter_organizations({ audience, island: currentIsland() }).slice(0, 4);
  holder.innerHTML = rows.map(org => organizationCard(org, audience)).join('') || empty();
}

function bindAudienceSearch(audience){
  const form = document.getElementById('audienceSearch');
  if(!form || form.dataset.bound) return;
  form.dataset.bound = 'true';
  const input = form.querySelector('input[name="q"]');
  const panel = document.getElementById('audienceSearchResults');
  let activeIndex = -1;
  let currentResults = [];

  const closePanel = () => {
    if(panel) {
      panel.hidden = true;
      panel.innerHTML = '';
    }
    activeIndex = -1;
    input?.setAttribute('aria-expanded', 'false');
    input?.removeAttribute('aria-activedescendant');
  };

  const navigateResult = result => {
    if(!result) return;
    location.href = result.href;
  };

  const setActive = index => {
    if(!panel || !currentResults.length) return;
    activeIndex = (index + currentResults.length) % currentResults.length;
    panel.querySelectorAll('[role="option"]').forEach((item, itemIndex) => {
      const active = itemIndex === activeIndex;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    input?.setAttribute('aria-activedescendant', `audience-suggestion-${activeIndex}`);
  };

  const renderSuggestions = () => {
    if(!input || !panel) return;
    const q = input.value.trim();
    if(!q) {
      closePanel();
      return;
    }
    const organizations = filter_organizations({ audience, island: currentIsland(), q }).slice(0, 5).map(org => ({
      type: ui('organizationType'),
      label: getOrganizationText(org, audience === 'professional' ? 'name' : 'youth_title') || getOrganizationText(org, 'name'),
      description: getOrganizationText(org, audience === 'professional' ? 'professional_summary' : 'youth_short'),
      chip: org.themes[0] ? audienceThemeName(org.themes[0], audience) : '',
      href: organizationHref(org, audience)
    }));
    const qNorm = normalizeText(q);
    const themes = availableThemes(audience)
      .filter(theme => normalizeText(`${getAudienceThemeText(theme, audience)} ${themeShort(theme.id)} ${theme.slug}`).includes(qNorm))
      .slice(0, 3)
      .map(theme => ({
        type: ui('themeType'),
        label: getAudienceThemeText(theme, audience),
        description: themeShort(theme.id),
        chip: ui('themeType'),
        href: `organisaties/?theme=${encodeURIComponent(theme.slug)}`
      }));
    currentResults = [...organizations, ...themes].slice(0, 7);
    input.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    if(!currentResults.length) {
      activeIndex = -1;
      panel.innerHTML = `<div class="search-empty">${escapeHtml(ui('noResults'))}</div>`;
      input.removeAttribute('aria-activedescendant');
      return;
    }
    panel.innerHTML = currentResults.map((result, index) => `
      <a id="audience-suggestion-${index}" class="search-suggestion" role="option" aria-selected="false" href="${escapeHtml(result.href)}">
        <span class="search-suggestion__type">${escapeHtml(result.type)}</span>
        <strong>${escapeHtml(result.label || fallbackText())}</strong>
        ${result.description ? `<small>${escapeHtml(result.description)}</small>` : ''}
        ${result.chip ? `<em>${escapeHtml(result.chip)}</em>` : ''}
      </a>
    `).join('');
    activeIndex = -1;
  };

  input?.addEventListener('input', renderSuggestions);
  input?.addEventListener('keydown', event => {
    if(event.key === 'Escape') {
      closePanel();
      return;
    }
    if(event.key === 'ArrowDown' && currentResults.length) {
      event.preventDefault();
      setActive(activeIndex + 1);
      return;
    }
    if(event.key === 'ArrowUp' && currentResults.length) {
      event.preventDefault();
      setActive(activeIndex - 1);
      return;
    }
    if(event.key === 'Enter' && activeIndex >= 0) {
      event.preventDefault();
      navigateResult(currentResults[activeIndex]);
    }
  });
  document.addEventListener('click', event => {
    if(!form.contains(event.target)) closePanel();
  });
  form.addEventListener('submit', event => {
    event.preventDefault();
    const q = form.querySelector('input[name="q"]')?.value || '';
    location.href = `organisaties/?q=${encodeURIComponent(q)}`;
  });
}

function initAudienceOrganizations(audience){
  document.body.dataset.page = 'organization-list';
  document.body.dataset.audience = audience;
  loadSeedData().then(() => {
    setAudiencePreference(audience);
    initShell();
    populateKadenaFilters();
    applyQueryToFilters();
    bindKadenaFilters(() => renderOrganizationList(audience));
    bindOrganizationListSuggestions(audience);
    renderOrganizationList(audience);
  });
}

function populateKadenaFilters(force = false){
  const theme = document.getElementById('theme');
  if(theme && (force || theme.options.length <= 1)) {
    const audience = document.body.dataset.audience || 'youth';
    const selectedValue = theme.value;
    theme.innerHTML = '';
    theme.add(new Option(ui('allThemes'), ''));
    availableThemes(audience).forEach(item => theme.add(new Option(getAudienceThemeText(item, audience) || fallbackText(), item.slug)));
    theme.value = selectedValue;
  }
}

function applyQueryToFilters(){
  const p = new URLSearchParams(location.search);
  ['q','theme'].forEach(id => {
    const el = document.getElementById(id);
    if(el && p.get(id)) el.value = p.get(id);
  });
}

function bindKadenaFilters(render){
  ['q','theme'].forEach(id => {
    const el = document.getElementById(id);
    if(el && !el.dataset.bound) {
      el.dataset.bound = 'true';
      el.addEventListener('input', render);
    }
  });
  const reset = document.getElementById('reset');
  if(reset && !reset.dataset.bound) {
    reset.dataset.bound = 'true';
    reset.addEventListener('click', () => {
      const q = document.getElementById('q');
      const theme = document.getElementById('theme');
      if(q) q.value = '';
      if(theme) theme.value = '';
      closeOrganizationSuggestions();
      render();
    });
  }
}

function renderOrganizationList(audience){
  const holder = document.getElementById('results');
  if(!holder) return;
  const filters = {
    q: document.getElementById('q')?.value || '',
    theme: document.getElementById('theme')?.value || '',
    audience,
    island: currentIsland()
  };
  const rows = filter_organizations(filters);
  holder.innerHTML = rows.map(org => organizationCard(org, audience)).join('') || `<div class="empty">${escapeHtml(ui('organizationEmpty'))}</div>`;
}

function closeOrganizationSuggestions(){
  const panel = document.getElementById('organizationSearchResults');
  const input = document.getElementById('q');
  if(panel) {
    panel.hidden = true;
    panel.innerHTML = '';
  }
  input?.setAttribute('aria-expanded', 'false');
  input?.removeAttribute('aria-activedescendant');
}

function bindOrganizationListSuggestions(audience){
  const input = document.getElementById('q');
  const panel = document.getElementById('organizationSearchResults');
  const themeSelect = document.getElementById('theme');
  if(!input || !panel || input.dataset.suggestionsBound) return;
  input.dataset.suggestionsBound = 'true';
  let activeIndex = -1;
  let currentResults = [];

  const setActive = index => {
    if(!currentResults.length) return;
    activeIndex = (index + currentResults.length) % currentResults.length;
    panel.querySelectorAll('[role="option"]').forEach((item, itemIndex) => {
      const active = itemIndex === activeIndex;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    input.setAttribute('aria-activedescendant', `organization-suggestion-${activeIndex}`);
  };

  const selectResult = result => {
    if(!result) return;
    if(result.kind === 'theme') {
      if(themeSelect) themeSelect.value = result.value;
      input.value = '';
      closeOrganizationSuggestions();
      renderOrganizationList(audience);
      return;
    }
    location.href = result.href;
  };

  const renderSuggestions = () => {
    const q = input.value.trim();
    if(!q) {
      closeOrganizationSuggestions();
      return;
    }
    const qNorm = normalizeText(q);
    const organizations = filter_organizations({ audience, island: currentIsland(), q }).slice(0, 5).map(org => ({
      kind: 'organization',
      type: ui('organizationType'),
      label: getOrganizationText(org, audience === 'professional' ? 'name' : 'youth_title') || getOrganizationText(org, 'name'),
      description: getOrganizationText(org, audience === 'professional' ? 'professional_summary' : 'youth_short'),
      chip: org.themes[0] ? audienceThemeName(org.themes[0], audience) : '',
      href: organizationHref(org, audience)
    }));
    const themes = availableThemes(audience)
      .filter(theme => normalizeText(`${getAudienceThemeText(theme, audience)} ${themeShort(theme.id)} ${theme.slug} ${(SEARCH_SYNONYMS.themes[theme.id] || []).join(' ')}`).includes(qNorm))
      .slice(0, 4)
      .map(theme => ({
        kind: 'theme',
        type: ui('themeType'),
        label: getAudienceThemeText(theme, audience),
        description: themeShort(theme.id),
        chip: ui('themeType'),
        value: theme.slug
      }));
    currentResults = [...organizations, ...themes].slice(0, 8);
    input.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    if(!currentResults.length) {
      activeIndex = -1;
      panel.innerHTML = `<div class="search-empty">${escapeHtml(ui('organizationEmpty'))}</div>`;
      input.removeAttribute('aria-activedescendant');
      return;
    }
    panel.innerHTML = currentResults.map((result, index) => {
      const tag = result.href ? 'a' : 'button';
      const action = result.href ? `href="${escapeHtml(result.href)}"` : 'type="button"';
      return `<${tag} id="organization-suggestion-${index}" class="search-suggestion" role="option" aria-selected="false" ${action} data-suggestion-index="${index}">
        <span class="search-suggestion__type">${escapeHtml(result.type)}</span>
        <strong>${escapeHtml(result.label || fallbackText())}</strong>
        ${result.description ? `<small>${escapeHtml(result.description)}</small>` : ''}
        ${result.chip ? `<em>${escapeHtml(result.chip)}</em>` : ''}
      </${tag}>`;
    }).join('');
    panel.querySelectorAll('[data-suggestion-index]').forEach(item => {
      item.addEventListener('click', event => {
        const result = currentResults[Number(item.dataset.suggestionIndex)];
        if(result?.kind === 'theme') event.preventDefault();
        selectResult(result);
      });
    });
    activeIndex = -1;
  };

  input.addEventListener('input', renderSuggestions);
  input.addEventListener('keydown', event => {
    if(event.key === 'Escape') {
      closeOrganizationSuggestions();
      return;
    }
    if(event.key === 'ArrowDown' && currentResults.length) {
      event.preventDefault();
      setActive(activeIndex + 1);
      return;
    }
    if(event.key === 'ArrowUp' && currentResults.length) {
      event.preventDefault();
      setActive(activeIndex - 1);
      return;
    }
    if(event.key === 'Enter' && activeIndex >= 0) {
      event.preventDefault();
      selectResult(currentResults[activeIndex]);
    }
  });
  document.addEventListener('click', event => {
    if(!panel.contains(event.target) && event.target !== input) closeOrganizationSuggestions();
  });
}

function organizationCard(org, audience){
  const isPro = audience === 'professional';
  const title = isPro ? getOrganizationText(org, 'name') : (getOrganizationText(org, 'youth_title') || getOrganizationText(org, 'name'));
  const summary = isPro
    ? getOrganizationText(org, 'professional_summary')
    : getOrganizationText(org, 'youth_short');
  const href = organizationHref(org, audience);
  const contact = contactLinks(org.contact);
  const primaryTheme = org.themes[0];
  const primaryColor = themeVisual(primaryTheme).color;
  const logo = org.logo_url || org.image_url || '';
  const themeIcons = org.themes
    .slice(0, 2)
    .map(id => themeIconMarkup(id, 'kh-card__theme-icon', audienceThemeName(id, audience)))
    .filter(Boolean)
    .join('');
  return `<article class="kh-card" data-card-href="${escapeHtml(href)}" role="link" tabindex="0" aria-label="${escapeHtml(`${ui('view')} ${title}`)}" style="--card-color:${escapeHtml(primaryColor)}">
    ${logo ? `<img class="kh-card__media-slot" src="${escapeHtml(logo)}" loading="lazy" alt="" aria-hidden="true" />` : '<div class="kh-card__media-slot" hidden aria-hidden="true"></div>'}
    <div class="kh-card__title-row"><h3>${escapeHtml(title)}</h3>${themeIcons ? `<div class="kh-card__theme-icons">${themeIcons}</div>` : ''}</div>
    ${summary ? `<p>${escapeHtml(summary)}</p>` : ''}
    <div class="meta">${org.themes.map(id => `<span class="tag theme-tag" style="--chip-color:${escapeHtml(themeVisual(id).color)}">${escapeHtml(audienceThemeName(id, audience))}</span>`).join('')}</div>
    ${contact ? `<div class="contact-row">${contact}</div>` : ''}
    <span class="view-link" aria-hidden="true">${iconSvg('arrow')}</span>
  </article>`;
}

function organizationHref(org, audience){
  if(currentIsland() !== 'bonaire') {
    const detail = document.body.dataset.page === 'audience-home'
      ? 'organisaties/detail.html'
      : 'detail.html';
    return `${detail}?slug=${encodeURIComponent(org.slug)}`;
  }
  const slug = `${encodeURIComponent(org.slug)}/`;
  if(document.body.dataset.page === 'audience-home') return `organisaties/${slug}`;
  return slug;
}

function hasMeaningfulValue(value){
  const text = String(value || '').trim();
  if(!text) return false;
  return !/^(nog niet ingevuld|nog aan te vullen|wordt nog aangevuld|n\/a|-)$/i.test(text);
}

function hasProfessionalContent(value){
  const text = String(value || '').trim();
  if(!hasMeaningfulValue(text)) return false;
  return !/^(demo-informatie;?\s*)?inhoud en verwijscriteria moeten voor lancering worden bevestigd\.?$/i.test(text);
}

function translatedContactAddress(contact = {}){
  return getTranslatedValue(contact.address);
}

function detailMetaRow(label, value){
  if(!hasMeaningfulValue(value)) return '';
  const icon = label === ui('address') ? iconSvg('pin') : label === ui('lastChecked') ? iconSvg('clock') : '';
  return `<dt>${icon}${escapeHtml(label)}</dt><dd>${escapeHtml(value)}</dd>`;
}

function contactActionLinks(contact = {}, options = {}){
  const phone = contact.phone || contact.phone_bonaire || contact.fallback_phone || '';
  const emergency = contact.emergency || '';
  const whatsapp = contact.phone_whatsapp || '';
  const email = contact.email || '';
  const website = contact.website || '';
  const primaryPhone = options.prioritizeEmergency && emergency ? emergency : phone;
  const links = [];
  const addTel = (number, label, className = '') => {
    if(!hasMeaningfulValue(number)) return;
    links.push(`<a class="contact-action ${className}" href="tel:${escapeHtml(number)}">${iconSvg('phone')}<span>${escapeHtml(label)} ${escapeHtml(number)}</span></a>`);
  };
  addTel(primaryPhone, ui('callLabel'), 'contact-action--primary');
  if(emergency && emergency !== primaryPhone) addTel(emergency, ui('emergencyLabel'));
  if(phone && phone !== primaryPhone) addTel(phone, ui('callLabel'));
  if(whatsapp) links.push(`<a class="contact-action" href="https://wa.me/${escapeHtml(whatsapp).replace(/\D/g,'')}">${iconSvg('chat')}<span>${escapeHtml(ui('whatsappLabel'))}</span></a>`);
  if(email) links.push(`<a class="contact-action" href="mailto:${escapeHtml(email)}">${iconSvg('mail')}<span>${escapeHtml(ui('mailLabel'))}</span></a>`);
  if(website) links.push(`<a class="contact-action" href="${escapeHtml(website)}" target="_blank" rel="noopener">${iconSvg('globe')}<span>${escapeHtml(ui('websiteLabel'))}</span></a>`);
  return links.join('');
}

function detailSectionBody(section){
  const paragraphs = Array.isArray(section.paragraphs) ? section.paragraphs : [];
  const items = Array.isArray(section.items) ? section.items : [];
  const body = getTranslatedValue(section.body);
  const html = [];
  if(body && hasMeaningfulValue(body)) html.push(renderRichText(body));
  paragraphs
    .map(getTranslatedValue)
    .filter(hasMeaningfulValue)
    .forEach(text => html.push(renderRichText(text)));
  if(items.length) {
    const listItems = items
      .map(getTranslatedValue)
      .filter(hasMeaningfulValue)
      .map(item => `<li>${escapeHtml(item)}</li>`)
      .join('');
    if(listItems) html.push(`<ul>${listItems}</ul>`);
  }
  const content = html.join('');
  return content ? `<div class="rich-text">${content}</div>` : '';
}

function profileLabel(config){
  return getTranslatedValue(config?.labels || config?.title || '');
}

function profileAnswerText(value){
  const text = getTranslatedValue(value, currentLanguage(), 'nl');
  return hasMeaningfulValue(text) ? text : '';
}

function rawProfileAnswer(value, language){
  if(value && typeof value === 'object' && !Array.isArray(value)) {
    return String(value[language] ?? '');
  }
  return language === 'nl' ? String(value ?? '') : '';
}

function editableProfileLanguages(){
  const languages = DATA.adminContext?.permissions?.editable_profile_languages;
  return Array.isArray(languages) ? languages.filter(language => LANGUAGES[language]) : [];
}

function canInlineEditProfiles(){
  return Boolean(DATA.adminContext?.authenticated && editableProfileLanguages().length);
}

function richTextInline(text){
  const formatPlainText = value => escapeHtml(value)
    .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
    .replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>');
  const linkPattern = /\[([^\]\n]+)\]\(([^)\s]+)\)/g;
  let html = '';
  let lastIndex = 0;
  let match;

  while((match = linkPattern.exec(text)) !== null) {
    html += formatPlainText(text.slice(lastIndex, match.index));
    const label = match[1];
    const url = match[2].trim();
    if(/^(?:https?:\/\/|mailto:)/i.test(url)) {
      const external = /^https?:\/\//i.test(url);
      html += `<a href="${escapeHtml(url)}"${external ? ' target="_blank"' : ''} rel="noopener noreferrer">${formatPlainText(label)}</a>`;
    } else {
      html += formatPlainText(match[0]);
    }
    lastIndex = linkPattern.lastIndex;
  }

  return html + formatPlainText(text.slice(lastIndex));
}

function renderRichText(text){
  const source = String(text ?? '').replace(/\r\n?/g, '\n');
  if(!source.trim()) return '';

  const html = [];
  let paragraph = [];
  let listType = '';
  let listItems = [];

  const flushParagraph = () => {
    if(!paragraph.length) return;
    html.push(`<p>${paragraph.map(richTextInline).join('<br>')}</p>`);
    paragraph = [];
  };
  const flushList = () => {
    if(!listType) return;
    html.push(`<${listType}>${listItems.map(item => `<li>${richTextInline(item)}</li>`).join('')}</${listType}>`);
    listType = '';
    listItems = [];
  };

  source.split('\n').forEach(line => {
    const bullet = line.match(/^\s*(?:-|•|·|â€¢|Â·)\s+(.+)$/);
    const numbered = line.match(/^\s*\d+[.)]\s+(.+)$/);
    if(bullet || numbered) {
      flushParagraph();
      const nextListType = bullet ? 'ul' : 'ol';
      if(listType && listType !== nextListType) flushList();
      listType = nextListType;
      listItems.push((bullet || numbered)[1].trim());
      return;
    }
    if(!line.trim()) {
      flushParagraph();
      flushList();
      return;
    }
    flushList();
    paragraph.push(line.trim());
  });

  flushParagraph();
  flushList();
  return html.join('');
}

function profileAnswerHtml(value){
  const html = renderRichText(profileAnswerText(value));
  return html ? `<div class="rich-text profile-rich-text">${html}</div>` : '';
}

function profileQuestionBlock(fieldConfig, value, groupKey){
  const body = profileAnswerHtml(value);
  if(!body && !canInlineEditProfiles()) return '';
  const editLabel = `Bewerk profielblok: ${profileLabel(fieldConfig)}`;
  return `<div class="detail-profile-field" data-profile-field data-profile-audience="professional" data-profile-group="${escapeHtml(groupKey)}" data-profile-key="${escapeHtml(fieldConfig.key)}">
    <div class="detail-profile-field__heading">
      <h3>${escapeHtml(profileLabel(fieldConfig))}</h3>
      ${canInlineEditProfiles() ? `<button class="admin-icon-button inline-profile-edit" type="button" data-inline-profile-edit title="${escapeHtml(editLabel)}" aria-label="${escapeHtml(editLabel)}"><span aria-hidden="true">✎</span></button>` : ''}
    </div>
    <div class="detail-profile-field__body">${body || '<p class="inline-profile-empty">Nog niet ingevuld in deze taal.</p>'}</div>
  </div>`;
}

function legacyShouldOpenDetailSection(title, index, audience){
  const normalized = normalizeText(title);
  if(audience === 'professional') {
    return index === 0 || [
      'algemene informatie',
      'verwijzing en toegankelijkheid',
      'praktische informatie'
    ].includes(normalized);
  }
  return index < 2;
}

function legacyDetailAccordionSections(org, audience){
  const key = audience === 'professional' ? 'professional_sections' : 'youth_sections';
  if(!Array.isArray(org[key])) return '';
  const sections = org[key].map((section, index) => {
    const title = getTranslatedValue(section.title);
    const body = detailSectionBody(section);
    if(!hasMeaningfulValue(title) || !body) return null;
    const id = `detail-section-${audience}-${index}`;
    const open = legacyShouldOpenDetailSection(title, index, audience);
    return `<section class="detail-section detail-accordion${open ? ' is-open' : ''}">
      <h2>
        <button class="detail-accordion__trigger" type="button" aria-expanded="${open ? 'true' : 'false'}" aria-controls="${id}">
          <span>${escapeHtml(title)}</span>
          <span class="detail-accordion__icon" aria-hidden="true"></span>
        </button>
      </h2>
      <div class="detail-accordion__panel" id="${id}"${open ? '' : ' hidden'}>${body}</div>
    </section>`;
  }).filter(Boolean);
  if(!sections.length) return '';
  const title = getTranslatedValue(PROFILE_I18N[audience]?.title) || (audience === 'professional' ? 'Uitgebreide informatie voor professionals' : 'Meer over deze organisatie');
  return `<section class="detail-expanded-info" aria-label="${escapeHtml(title)}">
    <div class="detail-expanded-info__header">
      <h2>${escapeHtml(title)}</h2>
      <button class="print-action" type="button" data-print-detail>${iconSvg('print')}<span>${escapeHtml(ui('printDetail'))}</span></button>
    </div>
    <div class="detail-accordion-list">${sections.join('')}</div>
  </section>`;
}

function youthProfileAccordionSections(org){
  if(!org.youth_profile) return '';
  let openCount = 0;
  const sections = PROFILE_I18N.youth.fields.map(field => {
    const body = profileAnswerHtml(org.youth_profile[field.key]);
    if(!body && !canInlineEditProfiles()) return null;
    const id = `detail-profile-youth-${field.key}`;
    const open = openCount < 2;
    openCount += 1;
    const editLabel = `Bewerk profielblok: ${profileLabel(field)}`;
    return `<section class="detail-section detail-accordion${open ? ' is-open' : ''}${canInlineEditProfiles() ? ' has-inline-edit' : ''}" data-profile-field data-profile-audience="youth" data-profile-group="" data-profile-key="${escapeHtml(field.key)}">
      <h2>
        <button class="detail-accordion__trigger" type="button" aria-expanded="${open ? 'true' : 'false'}" aria-controls="${id}">
          <span>${escapeHtml(profileLabel(field))}</span>
          <span class="detail-accordion__icon" aria-hidden="true"></span>
        </button>
        ${canInlineEditProfiles() ? `<button class="admin-icon-button inline-profile-edit inline-profile-edit--accordion" type="button" data-inline-profile-edit title="${escapeHtml(editLabel)}" aria-label="${escapeHtml(editLabel)}"><span aria-hidden="true">✎</span></button>` : ''}
      </h2>
      <div class="detail-accordion__panel" id="${id}"${open ? '' : ' hidden'}>${body || '<p class="inline-profile-empty">Nog niet ingevuld in deze taal.</p>'}</div>
    </section>`;
  }).filter(Boolean);
  if(!sections.length) return '';
  const title = getTranslatedValue(PROFILE_I18N.youth.title);
  return `<section class="detail-expanded-info" aria-label="${escapeHtml(title)}">
    <div class="detail-expanded-info__header">
      <h2>${escapeHtml(title)}</h2>
      <button class="print-action" type="button" data-print-detail>${iconSvg('print')}<span>${escapeHtml(ui('printDetail'))}</span></button>
    </div>
    <div class="detail-accordion-list">${sections.join('')}</div>
  </section>`;
}

function professionalProfileAccordionSections(org){
  if(!org.professional_profile) return '';
  const hasProfileContent = canInlineEditProfiles() || PROFILE_I18N.professional.groups.some(group => {
    const profileGroup = org.professional_profile[group.key] || {};
    return group.fields.some(field => field.key !== 'organisation_name' && profileAnswerHtml(profileGroup[field.key]));
  });
  if(!hasProfileContent) return '';
  const sections = PROFILE_I18N.professional.groups.map(group => {
    const profileGroup = org.professional_profile[group.key] || {};
    const body = group.fields
      .filter(field => field.key !== 'organisation_name')
      .map(field => profileQuestionBlock(field, profileGroup[field.key], group.key))
      .filter(Boolean)
      .join('');
    if(!body) return null;
    const id = `detail-profile-professional-${group.key}`;
    const open = ['general','referral','practical'].includes(group.key);
    return `<section class="detail-section detail-accordion${open ? ' is-open' : ''}">
      <h2>
        <button class="detail-accordion__trigger" type="button" aria-expanded="${open ? 'true' : 'false'}" aria-controls="${id}">
          <span>${escapeHtml(profileLabel(group))}</span>
          <span class="detail-accordion__icon" aria-hidden="true"></span>
        </button>
      </h2>
      <div class="detail-accordion__panel" id="${id}"${open ? '' : ' hidden'}>${body}</div>
    </section>`;
  }).filter(Boolean);
  if(!sections.length) return '';
  const title = getTranslatedValue(PROFILE_I18N.professional.title);
  return `<section class="detail-expanded-info" aria-label="${escapeHtml(title)}">
    <div class="detail-expanded-info__header">
      <h2>${escapeHtml(title)}</h2>
      <button class="print-action" type="button" data-print-detail>${iconSvg('print')}<span>${escapeHtml(ui('printDetail'))}</span></button>
    </div>
    <div class="detail-accordion-list">${sections.join('')}</div>
  </section>`;
}

function detailAccordionSections(org, audience){
  const profileSections = audience === 'professional'
    ? professionalProfileAccordionSections(org)
    : youthProfileAccordionSections(org);
  return profileSections || legacyDetailAccordionSections(org, audience);
}

function initDetailAccordions(holder){
  holder.querySelectorAll('.detail-accordion__trigger').forEach(button => {
    button.addEventListener('click', () => {
      const panel = document.getElementById(button.getAttribute('aria-controls'));
      const expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      button.closest('.detail-accordion')?.classList.toggle('is-open', !expanded);
      if(panel) panel.hidden = expanded;
    });
  });
  holder.querySelectorAll('[data-print-detail]').forEach(button => {
    button.addEventListener('click', () => window.print());
  });
}

function initOrganizationDetail(audience){
  document.body.dataset.page = 'organization-detail';
  document.body.dataset.audience = audience;
  loadSeedData().then(() => {
    setAudiencePreference(audience);
    initShell();
    renderOrganizationDetail(audience);
    fetchAdminContext().then(() => renderOrganizationDetail(audience));
  });
}

function slugFromLocation(){
  const p = new URLSearchParams(location.search);
  if(p.get('slug')) return p.get('slug');
  const parts = location.pathname.split('/').filter(Boolean);
  return parts[parts.length - 1] === 'detail.html' ? '' : parts[parts.length - 1];
}

function adminOrganizationUrl(slug, target, audience = ''){
  const params = new URLSearchParams({ slug, target });
  if(audience) params.set('audience', audience);
  return `${assetBase()}admin/organization_go.php?${params.toString()}`;
}

function publicAdminLink(label, href, className = ''){
  return `<a class="admin-control admin-edit-link${className ? ` ${className}` : ''}" href="${escapeHtml(href)}">${escapeHtml(label)}</a>`;
}

function addSectionAdminIcon(section, label, href){
  if(!section || section.querySelector('.admin-section-edit')) return;
  const heading = section.querySelector(':scope > h2');
  if(!heading) return;
  section.classList.add('admin-control-host');
  const link = document.createElement('a');
  link.className = 'admin-control admin-icon-button admin-section-edit';
  link.href = href;
  link.title = label;
  link.setAttribute('aria-label', label);
  link.innerHTML = '<span aria-hidden="true">✎</span>';
  heading.insertAdjacentElement('afterend', link);
}

function profileFieldConfig(audience, groupKey, fieldKey){
  if(groupKey === '__translation') {
    if(audience === 'youth' && fieldKey === 'youth_short') {
      return {
        key: 'youth_short',
        labels: { nl: 'Korte tekst jongerenpagina', pap: 'Teksto kòrtiku página di hóbennan', en: 'Short youth page intro', es: 'Texto breve de la página juvenil' }
      };
    }
    if(audience === 'professional' && fieldKey === 'professional_summary') {
      return {
        key: 'professional_summary',
        labels: { nl: 'Korte tekst professionalpagina', pap: 'Teksto kòrtiku página di profesionalnan', en: 'Short professional page intro', es: 'Texto breve de la página profesional' }
      };
    }
    return null;
  }
  if(audience === 'youth') {
    return PROFILE_I18N.youth.fields.find(field => field.key === fieldKey) || null;
  }
  const group = PROFILE_I18N.professional.groups.find(item => item.key === groupKey);
  return group?.fields.find(field => field.key === fieldKey && field.key !== 'organisation_name') || null;
}

function profileValue(org, audience, groupKey, fieldKey){
  if(groupKey === '__translation') {
    return Object.fromEntries(Object.entries(org.translations || {}).map(([language, translation]) => [
      language,
      translation?.[fieldKey] ?? ''
    ]));
  }
  if(audience === 'youth') return org.youth_profile?.[fieldKey];
  return org.professional_profile?.[groupKey]?.[fieldKey];
}

function setProfileValue(org, audience, groupKey, fieldKey, language, answerText){
  if(groupKey === '__translation') {
    org.translations = org.translations || {};
    org.translations[language] = org.translations[language] || {};
    org.translations[language][fieldKey] = answerText;
    return;
  }
  if(audience === 'youth') {
    org.youth_profile = org.youth_profile || {};
    org.youth_profile[fieldKey] = org.youth_profile[fieldKey] || {};
    org.youth_profile[fieldKey][language] = answerText;
    return;
  }
  org.professional_profile = org.professional_profile || {};
  org.professional_profile[groupKey] = org.professional_profile[groupKey] || {};
  org.professional_profile[groupKey][fieldKey] = org.professional_profile[groupKey][fieldKey] || {};
  org.professional_profile[groupKey][fieldKey][language] = answerText;
}

function ensureRichTextEditor(){
  if(window.KadenaRichTextEditor) return Promise.resolve(window.KadenaRichTextEditor);
  if(DATA.richTextEditorPromise) return DATA.richTextEditorPromise;
  DATA.richTextEditorPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = `${assetBase()}assets/richtext-editor.js`;
    script.onload = () => resolve(window.KadenaRichTextEditor);
    script.onerror = reject;
    document.head.appendChild(script);
  });
  return DATA.richTextEditorPromise;
}

function closeInlineProfileEditor(){
  const modal = document.querySelector('[data-inline-profile-modal]');
  if(!modal) return;
  document.body.classList.remove('has-inline-profile-modal');
  const returnFocus = modal._returnFocus;
  modal.remove();
  if(returnFocus?.isConnected) returnFocus.focus();
}

async function openInlineProfileEditor(button, org, audience, groupKey, fieldKey){
  const context = DATA.adminContext;
  const languages = editableProfileLanguages();
  const fieldConfig = profileFieldConfig(audience, groupKey, fieldKey);
  if(!context?.authenticated || !languages.length || !fieldConfig) return;

  closeInlineProfileEditor();
  const initialLanguage = languages.includes(currentLanguage()) ? currentLanguage() : languages[0];
  const adminHref = adminOrganizationUrl(org.slug, 'profile', audience);
  const modal = document.createElement('div');
  modal.className = 'inline-profile-modal-backdrop';
  modal.dataset.inlineProfileModal = '';
  modal.setAttribute('role', 'presentation');
  modal.innerHTML = `<section class="inline-profile-modal" role="dialog" aria-modal="true" aria-labelledby="inline-profile-title">
      <div class="inline-profile-modal__header">
      <div>
        <p class="inline-profile-kicker">${groupKey === '__translation' ? 'Korte intro bewerken' : 'Profieltekst bewerken'}</p>
        <h2 id="inline-profile-title">${escapeHtml(profileLabel(fieldConfig))}</h2>
      </div>
      <button class="inline-profile-close" type="button" aria-label="Sluiten" data-inline-profile-close>×</button>
    </div>
    <form data-inline-profile-form>
      <label class="inline-profile-language">
        Taal
        <select data-inline-profile-language>
          ${languages.map(language => `<option value="${escapeHtml(language)}"${language === initialLanguage ? ' selected' : ''}>${escapeHtml(LANGUAGES[language])}</option>`).join('')}
        </select>
      </label>
      <label>
        Tekst
        <textarea rows="11" maxlength="100000" data-inline-profile-text></textarea>
      </label>
      <p class="inline-profile-status" role="status" aria-live="polite" data-inline-profile-status></p>
      <div class="inline-profile-actions">
        <button class="button inline-profile-cancel" type="button" data-inline-profile-close>Annuleren</button>
        <a class="button" href="${escapeHtml(adminHref)}">Open uitgebreid in admin</a>
        <button type="submit" data-inline-profile-save>Opslaan</button>
      </div>
    </form>
  </section>`;
  modal._returnFocus = button;
  document.body.appendChild(modal);
  document.body.classList.add('has-inline-profile-modal');

  const textarea = modal.querySelector('[data-inline-profile-text]');
  const languageSelect = modal.querySelector('[data-inline-profile-language]');
  const form = modal.querySelector('[data-inline-profile-form]');
  const status = modal.querySelector('[data-inline-profile-status]');
  const saveButton = modal.querySelector('[data-inline-profile-save]');
  const loadLanguageValue = () => {
    textarea.value = rawProfileAnswer(profileValue(org, audience, groupKey, fieldKey), languageSelect.value);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
  };
  loadLanguageValue();
  languageSelect.addEventListener('change', loadLanguageValue);
  modal.querySelectorAll('[data-inline-profile-close]').forEach(close => close.addEventListener('click', closeInlineProfileEditor));
  modal.addEventListener('click', event => {
    if(event.target === modal) closeInlineProfileEditor();
  });
  modal.addEventListener('keydown', event => {
    if(event.key === 'Escape') closeInlineProfileEditor();
  });

  try {
    const editor = await ensureRichTextEditor();
    editor?.enhanceTextarea(textarea, {
      renderPreview: value => renderRichText(value) || '<p class="inline-profile-empty">Nog geen tekst om te tonen.</p>'
    });
  } catch(error) {
    console.warn('De rich-texttoolbar kon niet worden geladen.', error);
  }
  textarea.focus();

  form.addEventListener('submit', async event => {
    event.preventDefault();
    status.className = 'inline-profile-status';
    status.textContent = '';
    saveButton.disabled = true;
    saveButton.textContent = 'Opslaan…';
    try {
      const response = await fetch(`${assetBase()}admin/api/update_profile_field.php`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-Token': context.csrf_token || ''
        },
        body: JSON.stringify({
          organization_slug: org.slug,
          audience,
          group: groupKey,
          field: fieldKey,
          language: languageSelect.value,
          answer_text: textarea.value
        })
      });
      const result = await response.json().catch(() => ({}));
      if(response.status === 401 || result.error === 'session_expired') {
        status.classList.add('is-error');
        status.textContent = 'Je beheersessie is verlopen. Log opnieuw in.';
        return;
      }
      if(!response.ok || !result.ok) throw new Error(result.error || `HTTP ${response.status}`);

      setProfileValue(org, audience, groupKey, fieldKey, languageSelect.value, String(result.answer_text ?? textarea.value));
      const savedLanguage = languageSelect.value;
      status.classList.add('is-success');
      status.textContent = result.changed === false ? 'Er waren geen wijzigingen.' : 'Wijziging opgeslagen.';
      window.setTimeout(() => {
        closeInlineProfileEditor();
        if(savedLanguage !== currentLanguage()) {
          const url = new URL(location.href);
          if(url.searchParams.has('lang')) {
            url.searchParams.set('lang', savedLanguage);
            history.replaceState(null, '', url);
          }
          setLanguage(savedLanguage);
        } else {
          renderOrganizationDetail(audience);
        }
      }, 450);
    } catch(error) {
      console.warn('Inline profieltekst opslaan is mislukt.', error);
      status.classList.add('is-error');
      status.textContent = 'Opslaan is niet gelukt. Open dit veld in de admin of probeer opnieuw.';
    } finally {
      saveButton.disabled = false;
      saveButton.textContent = 'Opslaan';
    }
  });
}

function renderPublicAdminMode(audience){
  const context = DATA.adminContext;
  const holder = document.getElementById('organizationDetail');
  const org = get_organization_by_slug(slugFromLocation());
  if(!holder || !org || !context?.authenticated) return;

  const permissions = context.permissions || {};
  const article = holder.querySelector('.detail-shell');
  if(!article) return;

  document.body.classList.add('is-admin-mode');
  holder.querySelectorAll('.admin-public-toolbar, .admin-section-edit').forEach(item => item.remove());

  const actions = [];
  if(permissions.can_view_admin) {
    actions.push(publicAdminLink('Open in admin', adminOrganizationUrl(org.slug, 'view')));
  }
  if(permissions.can_edit_basic) {
    actions.push(publicAdminLink('Basisgegevens bewerken', adminOrganizationUrl(org.slug, 'basic')));
  }
  if(permissions.can_edit_youth_profile) {
    actions.push(publicAdminLink('Jongerenprofiel bewerken', adminOrganizationUrl(org.slug, 'profile', 'youth')));
  }
  if(permissions.can_edit_professional_profile) {
    actions.push(publicAdminLink('Professionalprofiel bewerken', adminOrganizationUrl(org.slug, 'profile', 'professional')));
  }

  if(actions.length) {
    article.insertAdjacentHTML('afterbegin', `<aside class="admin-public-toolbar" aria-label="Beheeropties">
      <div><span class="admin-public-badge">Beheer</span><strong>${escapeHtml(getOrganizationText(org, 'name') || org.slug)}</strong></div>
      <nav class="admin-public-actions">${actions.join('')}</nav>
    </aside>`);
  }

  if(permissions.can_edit_basic) {
    const contactSection = audience === 'professional'
      ? holder.querySelector('.professional-contact-card')
      : holder.querySelector('.youth-detail-side > .detail-section:first-child');
    addSectionAdminIcon(contactSection, 'Basisgegevens/contact bewerken', adminOrganizationUrl(org.slug, 'basic'));
  }

  holder.querySelectorAll('[data-inline-summary-edit]').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      const fieldBlock = button.closest('[data-profile-field]');
      if(!fieldBlock) return;
      openInlineProfileEditor(
        button,
        org,
        fieldBlock.dataset.profileAudience || audience,
        fieldBlock.dataset.profileGroup || '',
        fieldBlock.dataset.profileKey || ''
      );
    });
  });

  holder.querySelectorAll('[data-inline-profile-edit]').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      const fieldBlock = button.closest('[data-profile-field]');
      if(!fieldBlock) return;
      openInlineProfileEditor(
        button,
        org,
        fieldBlock.dataset.profileAudience || audience,
        fieldBlock.dataset.profileGroup || '',
        fieldBlock.dataset.profileKey || ''
      );
    });
  });
}

function detailHeaderSummaryMarkup(org, audience, text){
  const field = audience === 'professional' ? 'professional_summary' : 'youth_short';
  if(!text && !canInlineEditProfiles()) return '';
  const label = audience === 'professional' ? 'Korte tekst professionalpagina' : 'Korte tekst jongerenpagina';
  return `<div class="detail-header-summary-wrap" data-profile-field data-profile-audience="${escapeHtml(audience)}" data-profile-group="__translation" data-profile-key="${escapeHtml(field)}">
    ${text ? `<div class="rich-text detail-header-summary">${renderRichText(text)}</div>` : '<div class="rich-text detail-header-summary inline-profile-empty">Nog niet ingevuld in deze taal.</div>'}
    ${canInlineEditProfiles() ? `<button class="admin-control admin-icon-button inline-summary-edit" type="button" data-inline-summary-edit title="${escapeHtml(label)}" aria-label="${escapeHtml(label)}"><span aria-hidden="true">✎</span></button>` : ''}
  </div>`;
}

function renderOrganizationDetail(audience){
  const holder = document.getElementById('organizationDetail');
  if(!holder) return;
  const org = get_organization_by_slug(slugFromLocation());
  if(!org) {
    holder.innerHTML = `<div class="empty">Organisatie niet gevonden.</div>`;
    return;
  }
  recordRecentOrganization(org, audience);

  const isPro = audience === 'professional';
  const title = isPro ? getOrganizationText(org, 'name') : (getOrganizationText(org, 'youth_title') || getOrganizationText(org, 'name'));
  const primaryText = isPro ? getOrganizationText(org, 'professional_summary') : getOrganizationText(org, 'youth_where_can_you_go');
  const secondaryTitle = isPro ? ui('referral') : ui('contactHow');
  const secondaryText = isPro ? getOrganizationText(org, 'professional_referral_or_access') : getOrganizationText(org, 'youth_how_it_works');
  const notes = getOrganizationText(org, 'professional_notes') || '';
  const backHref = location.pathname.endsWith('/detail.html') ? './' : '../';

  if(!isPro) {
    const summary = getOrganizationText(org, 'youth_short') || primaryText || getOrganizationText(org, 'type');
    const headerSummary = summary;
    const primaryTheme = org.themes[0];
    const primaryColor = themeVisual(primaryTheme).color;
    const primaryThemeLabel = primaryTheme ? audienceThemeName(primaryTheme, audience) : '';
    const primaryThemeIcon = primaryTheme ? themeIconMarkup(primaryTheme, 'detail-theme-icon', '') : '';
    const contactActions = contactActionLinks(org.contact, { prioritizeEmergency: true });
    const contactFallback = contactActions || `<span class="muted">${escapeHtml(ui('fallback'))}</span>`;
    const address = translatedContactAddress(org.contact);
    const audienceText = org.audiences.map(audienceLabel).filter(Boolean).join(', ');
    const metaRows = [
      detailMetaRow(ui('address'), address),
      detailMetaRow('Toegang', getTranslatedValue(org.access_label)),
      detailMetaRow(ui('lastChecked'), org.last_checked_at),
      detailMetaRow(ui('forWhom'), audienceText)
    ].filter(Boolean).join('');
    const serviceLabels = Array.isArray(org.service_labels) && org.service_labels.length
      ? `<section class="detail-section"><h2>${escapeHtml(ui('goodToKnow'))}</h2><div class="meta">${org.service_labels.map(label => `<span class="tag gray">${escapeHtml(label)}</span>`).join('')}</div></section>`
      : '';

    holder.innerHTML = `<article class="detail-shell youth-detail" style="--detail-color:${escapeHtml(primaryColor)}">
      <a class="back-link" href="${backHref}">${escapeHtml(ui('backToOrganizations'))}</a>
      <div class="detail-header youth-detail-header">
        <div class="detail-header__copy">
          ${primaryThemeLabel ? `<span class="detail-theme-badge">${escapeHtml(primaryThemeLabel)}</span>` : ''}
          <h1>${escapeHtml(title)}</h1>
          ${detailHeaderSummaryMarkup(org, 'youth', headerSummary)}
        </div>
        ${primaryThemeIcon}
        ${contactActions ? `<div class="detail-header__actions">${contactActions}</div>` : ''}
      </div>
      <section class="detail-grid youth-detail-grid">
        <div class="detail-main youth-detail-main">
          ${detailAccordionSections(org, 'youth') || [
            primaryText ? `<section class="detail-section"><h2>${escapeHtml(ui('youthDescription'))}</h2><div class="rich-text">${renderRichText(primaryText)}</div></section>` : '',
            secondaryText ? `<section class="detail-section"><h2>${escapeHtml(ui('contactHow'))}</h2><div class="rich-text">${renderRichText(secondaryText)}</div></section>` : ''
          ].filter(Boolean).join('')}
        </div>
        <aside class="detail-side youth-detail-side">
          <section class="detail-section">
            <h2>${escapeHtml(ui('contact'))}</h2>
            <div class="contact-stack contact-actions">${contactFallback}</div>
          </section>
          <section class="detail-section">
            <h2>${escapeHtml(ui('themes'))}</h2>
            <div class="meta">${org.themes.map(id => `<span class="tag theme-tag" style="--chip-color:${escapeHtml(themeVisual(id).color)}">${escapeHtml(audienceThemeName(id, audience))}</span>`).join('')}</div>
          </section>
          ${metaRows ? `<section class="detail-section detail-facts"><dl>${metaRows}</dl></section>` : ''}
          ${serviceLabels}
        </aside>
      </section>
    </article>`;
    initDetailAccordions(holder);
    if(DATA.adminContext?.authenticated) renderPublicAdminMode(audience);
    return;
  }

  const primaryTheme = org.themes[0];
  const primaryColor = themeVisual(primaryTheme).color;
  const primaryThemeLabel = primaryTheme ? audienceThemeName(primaryTheme, audience) : '';
  const typeLabel = getOrganizationText(org, 'type') || primaryThemeLabel;
  const headerDescription = primaryText || typeLabel;
  const contactActions = contactActionLinks(org.contact);
  const accordionSections = detailAccordionSections(org, 'professional');
  const contentSections = accordionSections || [
    hasProfessionalContent(primaryText) ? `<section class="detail-section"><h2>${escapeHtml(ui('professionalDescription'))}</h2><div class="rich-text">${renderRichText(primaryText)}</div></section>` : '',
    hasProfessionalContent(secondaryText) ? `<section class="detail-section"><h2>${escapeHtml(secondaryTitle)}</h2><div class="rich-text">${renderRichText(secondaryText)}</div></section>` : '',
    hasProfessionalContent(notes) ? `<section class="detail-section"><h2>${escapeHtml(ui('notes'))}</h2><div class="rich-text">${renderRichText(notes)}</div></section>` : ''
  ].filter(Boolean).join('');
  const address = translatedContactAddress(org.contact);
  const audienceText = org.audiences.map(audienceLabel).filter(Boolean).join(', ');
  const ageText = getOrganizationText(org, 'age_range');
  const factRows = [
    detailMetaRow(ui('address'), address),
    detailMetaRow(ui('audience'), audienceText),
    detailMetaRow(ui('age'), ageText),
    detailMetaRow('Toegang', getTranslatedValue(org.access_label)),
    detailMetaRow(ui('lastChecked'), org.last_checked_at)
  ].filter(Boolean).join('');
  const serviceLabels = Array.isArray(org.service_labels) && org.service_labels.length
    ? `<section class="detail-section"><h2>${escapeHtml(ui('serviceLabels'))}</h2><div class="meta">${org.service_labels.map(label => `<span class="tag gray">${escapeHtml(label)}</span>`).join('')}</div></section>`
    : '';
  const themeList = org.themes.length
    ? `<section class="detail-section"><h2>${escapeHtml(ui('themes'))}</h2><div class="meta">${org.themes.map(id => `<span class="tag theme-tag" style="--chip-color:${escapeHtml(themeVisual(id).color)}">${escapeHtml(audienceThemeName(id, audience))}</span>`).join('')}</div></section>`
    : '';

  holder.innerHTML = `<article class="detail-shell professional-detail" style="--detail-color:${escapeHtml(primaryColor)}">
    <a class="back-link" href="${backHref}">${escapeHtml(ui('backToOrganizations'))}</a>
    <div class="detail-header professional-detail-header">
      <div class="detail-header__copy">
        ${primaryThemeLabel ? `<span class="detail-theme-badge professional-theme-badge">${escapeHtml(primaryThemeLabel)}</span>` : ''}
        <h1>${escapeHtml(title)}</h1>
        ${detailHeaderSummaryMarkup(org, 'professional', primaryText)}
        ${!primaryText && typeLabel ? `<div class="rich-text detail-header-summary">${renderRichText(typeLabel)}</div>` : ''}
      </div>
      ${contactActions ? `<div class="detail-header__actions professional-header-actions">${contactActions}</div>` : ''}
    </div>
    <section class="detail-grid professional-detail-grid">
      <div class="detail-main professional-detail-main">
        ${contentSections}
      </div>
      <aside class="detail-side professional-detail-side">
        ${contactActions ? `<section class="detail-section professional-contact-card"><h2>${escapeHtml(ui('contact'))}</h2><div class="contact-stack contact-actions">${contactActions}</div></section>` : ''}
        ${factRows ? `<section class="detail-section detail-facts"><dl>${factRows}</dl></section>` : ''}
        ${themeList}
        ${serviceLabels}
      </aside>
    </section>
  </article>`;
  initDetailAccordions(holder);
  if(DATA.adminContext?.authenticated) renderPublicAdminMode(audience);
}

function recordRecentOrganization(org, audience){
  if(!org?.slug) return;
  const key = audience === 'professional' ? STORAGE.recentProfessionalOrganizations : audience === 'youth' ? STORAGE.recentYouthOrganizations : '';
  if(!key) return;
  const current = safeJsonGet(key);
  safeJsonSet(key, [org.slug, ...current.filter(slug => slug !== org.slug)].slice(0, 4));
}

function initFeedbackForm(kind){
  loadSeedData().then(() => {
    initShell();
    const select = document.getElementById('organization');
    if(select && select.options.length <= 1) {
      select.options[0].textContent = ui('noSpecificOrganization');
      filter_organizations({ includeArchived: false, island: currentIsland() }).forEach(org => select.add(new Option(org.name, org.slug)));
    }
    renderFormChoiceGroups(kind);
    const audience = document.getElementById('audience');
    if(audience) audience.value = currentAudience() || '';
    const language = document.getElementById('language');
    if(language) language.value = currentLanguage();
    const form = document.querySelector('[data-local-form]');
    if(form) form.addEventListener('submit', event => handleDemoSubmit(event, kind));
  });
}

function renderFormChoiceGroups(kind){
  if(kind !== 'organization_signup') return;
  const themeHolder = document.getElementById('themeChoices');
  if(themeHolder) {
    themeHolder.innerHTML = get_themes().map(theme => {
      const name = getAudienceThemeText(theme, 'youth');
      return `<label class="choice-chip"><input type="checkbox" name="themes" value="${escapeHtml(theme.id)}"><span>${escapeHtml(name || fallbackText())}</span></label>`;
    }).join('');
  }
  const audienceHolder = document.getElementById('audienceChoices');
  if(audienceHolder) {
    audienceHolder.innerHTML = ['youth','parents','professional'].map(value => (
      `<label class="choice-chip"><input type="checkbox" name="target_audiences" value="${escapeHtml(value)}"><span>${escapeHtml(audienceLabel(value))}</span></label>`
    )).join('');
  }
}

function handleDemoSubmit(event, kind){
  event.preventDefault();
  const form = event.currentTarget;
  const data = Object.fromEntries(new FormData(form).entries());
  const requiredMessage = form.querySelector('[name="message"], [name="description"]');
  if(requiredMessage && !requiredMessage.value.trim()) {
    form.querySelector('[data-form-status]').textContent = ui('requiredError');
    return;
  }
  const payload = {
    kind,
    submitted_at: new Date().toISOString(),
    audience: data.audience || currentAudience() || 'onbekend',
    language: data.language || currentLanguage(),
    values: data
  };
  console.info(`Formulier ontvangen voor ${CONTACT_TO_EMAIL}.`, payload);
  try {
    const rows = JSON.parse(localStorage.getItem(STORAGE.submissions) || '[]');
    rows.push(payload);
    localStorage.setItem(STORAGE.submissions, JSON.stringify(rows.slice(-25)));
  } catch(error) {}
  form.reset();
  renderFormChoiceGroups(kind);
  const language = document.getElementById('language');
  if(language) language.value = currentLanguage();
  const audience = document.getElementById('audience');
  if(audience) audience.value = currentAudience() || '';
  form.querySelector('[data-form-status]').textContent = kind === 'feedback' ? ui('feedbackSuccess') : ui('signupSuccess');
}

function empty(){
  return `<div class="empty">${escapeHtml(ui('noResults'))}</div>`;
}

/* Legacy page initializers kept so the original pages do not break. */
async function initHome(){
  initAudienceChoice();
}

async function initOrganizations(){
  document.body.dataset.page = 'organization-list';
  document.body.dataset.audience = 'youth';
  await loadSeedData();
  initShell();
  populateKadenaFilters();
  applyQueryToFilters();
  bindKadenaFilters(() => renderOrganizationList('youth'));
  renderOrganizationList('youth');
}

async function initServices(){
  await loadSeedData();
  initShell();
  const holder = document.getElementById('results');
  if(holder) holder.innerHTML = filter_organizations({ audience: 'youth', island: currentIsland() }).slice(0, 8).map(org => organizationCard(org, 'youth')).join('') || empty();
}

async function initEvents(){
  await loadSeedData();
  initShell();
  const holder = document.getElementById('results');
  if(holder) holder.innerHTML = '<div class="empty">Activiteiten worden later gekoppeld aan Kadena Hubenil.</div>';
}
