const API = '/backend/index.php';

async function api(path, opts = {}) {
  const res = await fetch(API + path, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    ...opts,
  });
  const data = await res.json();
  if (!res.ok) throw { status: res.status, ...data };
  return data;
}

let allCards = [];

document.addEventListener('DOMContentLoaded', () => {
  checkAuth();
  loadCards();
  setupModal();
  setupFilters();
  setupLogout();
});

async function checkAuth() {
  try {
    const user = await api('/api/me');
    document.getElementById('user-display').textContent = user.username;
  } catch {
    window.location.href = 'index.html';
  }
}

async function loadCards() {
  try {
    allCards = await api('/api/cards');
    renderCards(allCards);
  } catch (err) {
    if (err.status === 401) window.location.href = 'index.html';
  }
}

function renderCards(cards) {
  const grid = document.getElementById('cards-grid');
  const empty = document.getElementById('empty-state');

  if (cards.length === 0) {
    grid.innerHTML = '';
    empty.hidden = false;
    return;
  }

  empty.hidden = true;
  grid.innerHTML = cards.map(c => {
    let colorClass = '';
    let shortGame = '';
    let phIcon = '♠';
    let phClass = 'placeholder-default';
    
    if (c.card_game === 'Magic: The Gathering') { 
      colorClass = 'c-magic'; shortGame = 'Magic'; 
      phIcon = '♠'; phClass = 'placeholder-magic';
    }
    else if (c.card_game === 'Pokémon TCG') { 
      colorClass = 'c-pokemon'; shortGame = 'Pokémon'; 
      phIcon = '★'; phClass = 'placeholder-pokemon';
    }
    else if (c.card_game === 'Yu-Gi-Oh!') { 
      colorClass = 'c-yugioh'; shortGame = 'Yu-Gi-Oh!'; 
      phIcon = '⬡'; phClass = 'placeholder-yugioh';
    }

    return `
      <div class="card" data-id="${c.id}">
        <div class="card-img-container">
          ${c.image_url ? `<img src="${escHtml(c.image_url)}" loading="lazy">` : `<div class="card-img-placeholder ${phClass}">${phIcon}</div>`}
          ${shortGame ? `<div class="card-badge ${colorClass}">${shortGame}</div>` : ''}
        </div>
        
        <div class="card-actions-overlay">
          <button class="btn-card-action btn-edit-card" onclick="openEdit(${c.id})">Editar</button>
          <button class="btn-card-action btn-delete-card" onclick="confirmDelete(${c.id})">Excluir</button>
        </div>
        
        <div class="card-info">
          <div class="card-title" title="${escHtml(c.name_en || '')}">${escHtml(c.name_en || '')}</div>
          <div class="card-subtitle">${escHtml(c.edition_name || '')} • ${escHtml(c.rarity || '')}</div>
        </div>
      </div>
    `;
  }).join('');
}

function setupFilters() {
  const searchInput = document.getElementById('search-input');
  const gameFilter = document.getElementById('game-filter');

  searchInput.addEventListener('input', applyFilters);
  gameFilter.addEventListener('change', applyFilters);
}

function applyFilters() {
  const search = document.getElementById('search-input').value.toLowerCase();
  const game = document.getElementById('game-filter').value;

  const filtered = allCards.filter(c => {
    const nameEn = c.name_en || '';
    const namePt = c.name_pt || '';
    const edition = c.edition_name || '';
    const cardGame = c.card_game || '';

    const matchSearch = !search
      || nameEn.toLowerCase().includes(search)
      || namePt.toLowerCase().includes(search)
      || edition.toLowerCase().includes(search);
    const matchGame = !game || cardGame === game;
    return matchSearch && matchGame;
  });

  renderCards(filtered);
}

function setupModal() {
  const modal = document.getElementById('card-modal');
  const closeBtn = modal.querySelector('.modal-close');
  const cancelBtn = document.getElementById('btn-cancel');
  const form = document.getElementById('card-form');
  const gameSelect = document.getElementById('card-game');
  const editionSelect = document.getElementById('card-edition');
  const raritySelect = document.getElementById('card-rarity');

  gameSelect.addEventListener('change', () => {
    onGameChange(gameSelect, editionSelect, raritySelect);
  });

  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('card-id').value;
    const payload = {
      name_en: document.getElementById('card-name-en').value.trim(),
      name_pt: document.getElementById('card-name-pt').value.trim(),
      card_game: gameSelect.value,
      edition_name: editionSelect.value,
      rarity: raritySelect.value,
      image_url: document.getElementById('card-image').value.trim() || null,
    };

    try {
      if (id) {
        await api('/api/cards/' + id, { method: 'PUT', body: JSON.stringify(payload) });
      } else {
        await api('/api/cards', { method: 'POST', body: JSON.stringify(payload) });
      }
      closeModal();
      await loadCards();
    } catch (err) {
      const errDiv = document.getElementById('form-error');
      errDiv.textContent = (err.errors || [err.error || 'Erro ao salvar']).join(', ');
    }
  });
}

function openCreate() {
  document.getElementById('modal-title').textContent = 'Nova Carta';
  document.getElementById('card-form').reset();
  document.getElementById('card-id').value = '';
  document.getElementById('form-error').textContent = '';

  document.getElementById('card-edition').innerHTML = '<option value="">Selecione o jogo primeiro</option>';
  document.getElementById('card-rarity').innerHTML = '<option value="">Selecione o jogo primeiro</option>';

  document.getElementById('card-modal').hidden = false;
}

async function openEdit(id) {
  try {
    const card = await api('/api/cards/' + id);
    document.getElementById('modal-title').textContent = 'Editar Carta';
    document.getElementById('card-id').value = card.id;
    document.getElementById('card-name-en').value = card.name_en || '';
    document.getElementById('card-name-pt').value = card.name_pt || '';
    document.getElementById('form-error').textContent = '';

    const gameSelect = document.getElementById('card-game');
    const editionSelect = document.getElementById('card-edition');
    const raritySelect = document.getElementById('card-rarity');

    gameSelect.value = card.card_game || '';
    await onGameChange(gameSelect, editionSelect, raritySelect);

    editionSelect.value = card.edition_name || '';
    raritySelect.value = card.rarity || '';

    document.getElementById('card-image').value = card.image_url || '';

    document.getElementById('card-modal').hidden = false;
  } catch (err) {
    alert('Erro ao carregar carta.');
  }
}

async function confirmDelete(id) {
  if (!confirm('Tem certeza que deseja excluir esta carta?')) return;
  try {
    await api('/api/cards/' + id, { method: 'DELETE' });
    await loadCards();
  } catch {
    alert('Erro ao excluir.');
  }
}

function closeModal() {
  document.getElementById('card-modal').hidden = true;
}

function setupLogout() {
  document.getElementById('btn-logout').addEventListener('click', async () => {
    await api('/api/logout', { method: 'POST' }).catch(() => {});
    window.location.href = 'index.html';
  });
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}
