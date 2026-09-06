const EDITIONS = {
  'Magic: The Gathering': [
    'Dominaria', 'War of the Spark', 'Throne of Eldraine', 'The Hobbit', 'Marvel Super Heroes'
  ],
  'Pokémon TCG': [
    'Base Set', 'Sword & Shield', 'Scarlet & Violet', '30th Celebration', 'Chaos Rising'
  ],
  'Yu-Gi-Oh!': [
    'Legend of Blue Eyes White Dragon', 'Metal Raiders', 'Starter Deck Yugi', 'Rise of the Duelist', 'Blazing Dominion'
  ],
};

const RARITIES = {
  'Magic: The Gathering': ['Common', 'Uncommon', 'Rare', 'Mythic Rare'],
  'Pokémon TCG': ['Common', 'Uncommon', 'Rare', 'Holographic Rare', 'Ultra Rare', 'Secret Rare', 'Special Art Rare'],
  'Yu-Gi-Oh!': ['Common', 'Rare', 'Super Rare', 'Ultra Rare', 'Secret Rare', 'Ghost Rare', 'Starlight Rare'],
};

function populateSelect(selectEl, items, placeholder) {
  selectEl.innerHTML = '';
  const opt = document.createElement('option');
  opt.value = '';
  opt.textContent = placeholder;
  selectEl.appendChild(opt);
  items.forEach(item => {
    const o = document.createElement('option');
    o.value = item;
    o.textContent = item;
    selectEl.appendChild(o);
  });
}

function onGameChange(gameSelect, editionSelect, raritySelect) {
  const game = gameSelect.value;
  const group = document.getElementById('edition-group');
  if (group) group.classList.add('is-loading');
  
  setTimeout(() => {
    populateSelect(editionSelect, EDITIONS[game] || [], 'Selecione a edição');
    populateSelect(raritySelect, RARITIES[game] || [], 'Selecione a raridade');
    if (group) group.classList.remove('is-loading');
  }, 300);
}
