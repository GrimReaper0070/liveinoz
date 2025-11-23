// Data and constants
const UI = {
  ES: {
    title: "Elige tu salón",
    city: "Ciudad",
    search: "Buscar",
    online: "en línea",
    members: "miembros",
    categories: {
      social: "Social",
      daily: "Vida diaria",
      work: "Trabajo y dinero",
      fitness: "Fitness",
      hobbies: "Hobbies",
    },
  },
  EN: {
    title: "Choose your salon",
    city: "City",
    search: "Search",
    online: "online",
    members: "members",
    categories: {
      social: "Social",
      daily: "Daily Life",
      work: "Work & Money",
      fitness: "Fitness",
      hobbies: "Hobbies",
    },
  },
};

const STATE_TO_CITY = {
  "New South Wales": "Sydney",
  Victoria: "Melbourne",
  Queensland: "Brisbane",
  "South Australia": "Adelaide",
  "Western Australia": "Perth",
  Tasmania: "Hobart",
  "Northern Territory": "Darwin",
  "Australian Capital Territory": "Canberra",
};

const ROOMS = [
  { id: "fut", title: { ES: "Fútbol", EN: "Football" }, icon: "⚽", cat: "social", online: 235, members: 3100, color: "#00e5ff" },
  { id: "junt", title: { ES: "Juntadas", EN: "Meetups" }, icon: "🥂", cat: "social", online: 120, members: 900, color: "#ff3fd8" },
  { id: "asado", title: { ES: "Sale Asado", EN: "BBQ Meetups" }, icon: "🍖", cat: "social", online: 76, members: 950, color: "#ff8a5c" },
  { id: "mate", title: { ES: "Materos", EN: "Mate lovers" }, icon: "🧉", cat: "social", online: 58, members: 800, color: "#00ffb0" },
  { id: "playa", title: { ES: "Vamos a la playa", EN: "Let's go to the beach" }, icon: "🏖️", cat: "social", online: 47, members: 780, color: "#7dc6ff" },
  { id: "mus", title: { ES: "Música & Bailes", EN: "Music & Dance" }, icon: "🎶", cat: "social", online: 102, members: 1700, color: "#9ad1ff" },
  { id: "lat", title: { ES: "Latinas Unidas", EN: "Latinas United" }, icon: "👩‍🦰", cat: "social", online: 81, members: 1200, color: "#ff6b8b" },
  { id: "ar", title: { ES: "Argentinos", EN: "Argentinians" }, icon: "🇦🇷", cat: "social", online: 140, members: 2100, color: "#00e5ff" },
  { id: "co", title: { ES: "Colombianos", EN: "Colombians" }, icon: "🇨🇴", cat: "social", online: 95, members: 1500, color: "#ff3fd8" },
  { id: "es", title: { ES: "Españoles", EN: "Spaniards" }, icon: "🇪🇸", cat: "social", online: 88, members: 1400, color: "#ffa03c" },
  { id: "br", title: { ES: "Brasileños", EN: "Brazilians" }, icon: "🇧🇷", cat: "social", online: 110, members: 1750, color: "#00ffb0" },
  { id: "cl", title: { ES: "Chilenos", EN: "Chileans" }, icon: "🇨🇱", cat: "social", online: 64, members: 980, color: "#5599ff" },
  { id: "mx", title: { ES: "Mexicanos", EN: "Mexicans" }, icon: "🇲🇽", cat: "social", online: 120, members: 1850, color: "#ff6b8b" },
  { id: "pe", title: { ES: "Peruanos", EN: "Peruvians" }, icon: "🇵🇪", cat: "social", online: 71, members: 1100, color: "#c077ff" },
  { id: "rooms", title: { ES: "Alojamiento / Rooms", EN: "Housing / Rooms" }, icon: "🏠", cat: "daily", online: 96, members: 2000, color: "#c077ff" },
  { id: "visas", title: { ES: "Visas & Papeles", EN: "Visas & Docs" }, icon: "📄", cat: "daily", online: 73, members: 1500, color: "#00ffb0" },
  { id: "trans", title: { ES: "Caronas & Transporte", EN: "Rides & Transport" }, icon: "🚌", cat: "daily", online: 55, members: 980, color: "#5599ff" },
  { id: "ofertas", title: { ES: "Compras & Ofertas", EN: "Shopping & Deals" }, icon: "🛒", cat: "daily", online: 89, members: 1600, color: "#ffa03c" },
  { id: "lost", title: { ES: "Perdidos y Encontrados", EN: "Lost & Found" }, icon: "🧭", cat: "daily", online: 42, members: 870, color: "#7dc6ff" },
  { id: "newbies", title: { ES: "Recién llegados", EN: "New arrivals" }, icon: "👋", cat: "daily", online: 83, members: 1250, color: "#ffd866" },
  { id: "jobs", title: { ES: "Trabajos & Gigs", EN: "Jobs & Gigs" }, icon: "💼", cat: "work", online: 310, members: 4800, color: "#7dc6ff" },
  { id: "empre", title: { ES: "Emprendedores", EN: "Entrepreneurs" }, icon: "💡", cat: "work", online: 66, members: 1100, color: "#00e5ff" },
  { id: "gym", title: { ES: "Gimnasio & Fitness", EN: "Gym & Fitness" }, icon: "🏋️‍♂️", cat: "fitness", online: 64, members: 1100, color: "#5599ff" },
  { id: "run", title: { ES: "Running & Maratones", EN: "Running & Marathons" }, icon: "🏃‍♂️", cat: "fitness", online: 41, members: 820, color: "#00ffb0" },
  { id: "calis", title: { ES: "Calistenia / Street", EN: "Calisthenics / Street" }, icon: "🤸‍♂️", cat: "fitness", online: 39, members: 730, color: "#7dffd6" },
  { id: "tenis", title: { ES: "Tenis / Pádel", EN: "Tennis / Padel" }, icon: "🎾", cat: "fitness", online: 29, members: 650, color: "#9ad1ff" },
  { id: "surf", title: { ES: "Surf", EN: "Surf" }, icon: "🏄‍♂️", cat: "fitness", online: 22, members: 510, color: "#00e5ff" },
  { id: "gamers", title: { ES: "Gamers & Tech", EN: "Gamers & Tech" }, icon: "🎮", cat: "hobbies", online: 140, members: 2100, color: "#ffa03c" },
  { id: "cine", title: { ES: "Cine & Series", EN: "Movies & Series" }, icon: "🎬", cat: "hobbies", online: 72, members: 1300, color: "#ff6b8b" },
  { id: "foto", title: { ES: "Fotografía", EN: "Photography" }, icon: "📷", cat: "hobbies", online: 51, members: 900, color: "#c077ff" },
  { id: "arte", title: { ES: "Arte & Diseño", EN: "Art & Design" }, icon: "🎨", cat: "hobbies", online: 45, members: 820, color: "#00e5ff" },
  { id: "weed", title: { ES: "Fumamos (🌿)", EN: "Weed (🌿)" }, icon: "🌿", cat: "hobbies", online: 88, members: 1300, color: "#7dffcf", badges: ["🔞"] },
];

// Helper
const slug = (s) => (s || "").toLowerCase().normalize("NFD").replace(/\p{Diacritic}/gu, "").replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");

// State
let lang = "ES";
let cat = "social";
let query = "";
let placeLabel = "Sydney";
let placeKey = slug(placeLabel);

// DOM elements
const titleEl = document.querySelector('.title');
const cityChip = document.querySelector('.chip-city span');
const langChip = document.querySelector('.chip-lang span');
const backChip = document.querySelector('.chip-back span');
const searchInput = document.querySelector('.search-input');
const tabs = document.querySelectorAll('.tab');
const grid = document.getElementById('grid');

// Functions
function updateUI() {
  const t = UI[lang];
  titleEl.textContent = t.title;
  cityChip.textContent = placeLabel;
  langChip.textContent = lang === "ES" ? "ES | EN" : "EN | ES";
  backChip.textContent = lang === "ES" ? "← Volver" : "← Back";
  searchInput.placeholder = t.search;

  tabs.forEach(tab => {
    const catKey = tab.dataset.cat;
    tab.querySelector('.tab-text').textContent = t.categories[catKey];
  });

  renderCards();
}

function renderCards() {
  const t = UI[lang];
  const filtered = ROOMS.filter(r => r.cat === cat && (query === "" || r.title[lang].toLowerCase().includes(query.toLowerCase())));

  grid.innerHTML = '';

  filtered.forEach(room => {
    const card = document.createElement('div');
    card.className = 'card';
    card.style.color = room.color; // For neon glow

    card.innerHTML = `
      <div class="card-title">
        <span>${room.icon}</span>
        <span>${room.title[lang]}</span>
      </div>
      <div class="meta">${room.online} ${t.online} · ${room.members.toLocaleString()} ${t.members}</div>
      ${room.badges ? `<div class="badges">${room.badges.map(b => `<div class="badge"><span class="badge-text">${b}</span></div>`).join('')}</div>` : ''}
    `;

    card.addEventListener('click', () => {
      console.log(`Navigate to ${placeKey}__${room.id}`);
      // In a real app, navigate to chat room
    });

    grid.appendChild(card);
  });
}

function setActiveTab(newCat) {
  cat = newCat;
  tabs.forEach(tab => {
    if (tab.dataset.cat === cat) {
      tab.classList.add('active');
    } else {
      tab.classList.remove('active');
    }
  });
  renderCards();
}

// Event listeners
document.querySelector('.chip-lang').addEventListener('click', () => {
  lang = lang === "ES" ? "EN" : "ES";
  updateUI();
});

document.querySelector('.chip-back').addEventListener('click', () => {
  console.log('Go back');
  // In a real app, go back
});

searchInput.addEventListener('input', (e) => {
  query = e.target.value;
  renderCards();
});

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    setActiveTab(tab.dataset.cat);
  });
});

// Init
updateUI();
