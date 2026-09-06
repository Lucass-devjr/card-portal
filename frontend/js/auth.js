const API_BASE = '/backend/index.php';

async function apiFetch(path, options = {}) {
  const res = await fetch(API_BASE + path, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    ...options,
  });
  const data = await res.json();
  if (!res.ok) throw { status: res.status, ...data };
  return data;
}

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('login-form');
  const errorEl = document.getElementById('login-error');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.textContent = '';

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !password) {
      errorEl.textContent = 'Preencha todos os campos.';
      return;
    }

    try {
      await apiFetch('/api/login', {
        method: 'POST',
        body: JSON.stringify({ username, password }),
      });
      window.location.href = 'dashboard.html';
    } catch (err) {
      errorEl.textContent = err.error || 'Erro ao fazer login.';
    }
  });
});
