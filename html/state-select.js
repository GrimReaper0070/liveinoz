const STATES = [
  { code: 'NSW', name: 'New South Wales', color: '#ff3fd8' },
  { code: 'VIC', name: 'Victoria', color: '#00e5ff' },
  { code: 'QLD', name: 'Queensland', color: '#7dc6ff' },
  { code: 'SA',  name: 'South Australia', color: '#ff6b8b' },
  { code: 'WA',  name: 'Western Australia', color: '#ff9a3c' },
  { code: 'TAS', name: 'Tasmania', color: '#c077ff' },
  { code: 'NT',  name: 'Northern Territory', color: '#00ffb0' },
  { code: 'ACT', name: 'Australian Capital Territory', color: '#e7fbff' },
];

const grid = document.getElementById('grid');

function renderStates() {
  grid.innerHTML = '';

  STATES.forEach(state => {
    const card = document.createElement('div');
    card.className = 'card';
    card.onclick = () => onPick(state);

    card.innerHTML = `
      <div class="neon-outline" style="border-color: ${state.color}; box-shadow: 0 0 10px ${state.color}, 0 0 20px ${state.color}, 0 0 30px ${state.color};"></div>
      <div class="content">
        <div class="code">${state.code}</div>
        <div class="name">${state.name}</div>
      </div>
    `;

    grid.appendChild(card);
  });
}

function onPick(state) {
  console.log('Selected state:', state);
  // In a real app, navigate to salon list with state
}

renderStates();
