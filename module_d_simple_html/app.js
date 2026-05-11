/*
  Очень простая SPA-версия Module D.
  Здесь почти вся разметка уже написана в HTML.
  JavaScript только:
  - переключает страницы по hash;
  - отправляет запросы в API;
  - заполняет данные в готовые блоки;
  - хранит токен на клиенте.
*/

const API_BASE = localStorage.getItem('apiBase') || 'http://127.0.0.1:8000/api';

const state = {
  token: localStorage.getItem('token') || '',
  user: JSON.parse(localStorage.getItem('user') || 'null'),
  categories: [],
  currentAdvert: null,
};

const pages = Array.from(document.querySelectorAll('.page'));
const appMessage = document.getElementById('appMessage');
const appLoader = document.getElementById('appLoader');

function showMessage(text, type = 'success') {
  appMessage.textContent = text;
  appMessage.className = `message ${type}`;
  appMessage.hidden = false;
}

function clearMessage() {
  appMessage.hidden = true;
  appMessage.textContent = '';
}

function setLoading(isLoading) {
  appLoader.hidden = !isLoading;
}

function saveAuth(token, user) {
  state.token = token || '';
  state.user = user || null;
  localStorage.setItem('token', state.token);
  localStorage.setItem('user', JSON.stringify(state.user));
  updateAuthLinks();
}

function logout() {
  saveAuth('', null);
  location.hash = '#/login';
  showMessage('Вы вышли из системы.');
}

function updateAuthLinks() {
  document.querySelectorAll('[data-auth-only]').forEach((el) => {
    el.classList.toggle('hidden', !state.token);
  });
  document.querySelectorAll('[data-guest-only]').forEach((el) => {
    el.classList.toggle('hidden', !!state.token);
  });
}

async function api(path, options = {}) {
  const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
  if (state.token) headers.Authorization = `Bearer ${state.token}`;

  const response = await fetch(`${API_BASE}${path}`, { ...options, headers });
  const text = await response.text();
  const data = text ? JSON.parse(text) : null;

  if (!response.ok) {
    throw new Error(data?.message || 'Ошибка запроса к API');
  }

  return data;
}

function showPage(id) {
  pages.forEach((page) => page.classList.toggle('active', page.id === id));
  clearMessage();
}

function getHashParts() {
  const hash = location.hash.replace('#', '') || '/';
  const cleaned = hash.startsWith('/') ? hash : `/${hash}`;
  return cleaned.split('/').filter(Boolean);
}

function requireAuth(nextHash = '#/login') {
  if (!state.token) {
    location.hash = nextHash;
    showMessage('Сначала войдите в систему.', 'error');
    return false;
  }
  return true;
}

function formatDate(value) {
  if (!value) return '—';
  return new Date(value).toLocaleString('ru-RU');
}

function fillCategorySelect(select, includeAll = false) {
  const current = select.value;
  select.innerHTML = includeAll ? '<option value="">Все категории</option>' : '';
  state.categories.forEach((category) => {
    const option = document.createElement('option');
    option.value = category.id;
    option.textContent = category.name;
    select.appendChild(option);
  });
  select.value = current;
}

async function loadCategories() {
  state.categories = await api('/categories');
  fillCategorySelect(document.getElementById('homeCategory'), true);
  fillCategorySelect(document.getElementById('advertCategory'), false);
}

function advertCardTemplate(advert, mine = false) {
  const services = (advert.advantage_services || advert.services || []).map((item) => item.type || item.service_type).join(', ');
  return `
    <article class="card advert-card">
      <h3>${advert.title}</h3>
      <p>${advert.text}</p>
      <div class="meta">
        <span>Цена: ${advert.price}</span>
        <span>Категория: ${advert.category?.name || advert.category_name || '—'}</span>
        <span>Дата: ${formatDate(advert.created_at || advert.published_at)}</span>
        ${mine ? `<span>Статус: ${advert.status}</span>` : ''}
        ${services ? `<span>Услуги: ${services}</span>` : ''}
      </div>
      <div class="actions-row" style="margin-top: 12px;">
        <a href="#/details/${advert.id}"><button type="button">Открыть</button></a>
        ${mine ? `<button type="button" onclick="openEdit(${advert.id})">Редактировать</button>` : ''}
        ${mine ? `<a href="#/services/${advert.id}"><button type="button" class="secondary-button">Услуги</button></a>` : ''}
      </div>
    </article>
  `;
}

async function renderHome() {
  showPage('page-home');
  setLoading(true);
  try {
    const form = document.getElementById('homeFilterForm');
    const params = new URLSearchParams(new FormData(form));
    const data = await api(`/adverts?${params.toString()}`);
    document.getElementById('homeList').innerHTML = data.map((advert) => advertCardTemplate(advert)).join('') || '<div class="card">Ничего не найдено.</div>';
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    setLoading(false);
  }
}

async function renderMyAdverts() {
  if (!requireAuth()) return;
  showPage('page-my-adverts');
  setLoading(true);
  try {
    const form = document.getElementById('myAdvertsFilterForm');
    const params = new URLSearchParams(new FormData(form));
    const data = await api(`/user/adverts?${params.toString()}`);
    document.getElementById('myAdvertsList').innerHTML = data.map((advert) => advertCardTemplate(advert, true)).join('') || '<div class="card">У вас пока нет объявлений.</div>';
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    setLoading(false);
  }
}

function maskedPhone(phone) {
  if (!phone) return 'Телефон не указан';
  return phone.slice(0, 4) + ' ••••••••';
}

function renderGallery(photos = []) {
  if (!photos.length) return '<div class="gallery-main">Нет изображений</div>';
  const normalized = photos.map((item) => typeof item === 'string' ? item : item.url);
  return `
    <div class="gallery-main"><img id="mainGalleryImage" src="${normalized[0]}" alt="Фото объявления"></div>
    <div class="gallery-thumbs">
      ${normalized.map((src, index) => `<img src="${src}" class="${index === 0 ? 'active' : ''}" data-gallery-src="${src}" alt="Миниатюра">`).join('')}
    </div>
  `;
}

async function renderDetails(id) {
  showPage('page-details');
  setLoading(true);
  try {
    const advert = await api(`/adverts/${id}`);
    state.currentAdvert = advert;
    const authorPhone = advert.author?.phone || advert.user?.phone || '';
    const services = advert.advantage_services || advert.services || [];
    document.getElementById('advertDetailsBox').innerHTML = `
      <h1>${advert.title}</h1>
      <p>${advert.text}</p>
      <div class="meta">
        <span>Цена: ${advert.price}</span>
        <span>Категория: ${advert.category?.name || '—'}</span>
        <span>Дата: ${formatDate(advert.published_at || advert.created_at)}</span>
        <span>Автор: ${advert.author?.name || advert.user?.name || '—'}</span>
      </div>
      <div style="margin: 16px 0;">${renderGallery(advert.photos || [])}</div>
      <div class="service-box">
        <strong>Телефон продавца:</strong>
        <div id="phoneBox" class="muted" style="margin-top: 8px;">${maskedPhone(authorPhone)}</div>
        <button type="button" id="showPhoneButton" style="margin-top: 10px; width: auto;">Показать телефон</button>
      </div>
      <div class="service-box">
        <strong>Купленные услуги:</strong>
        <div style="margin-top: 8px;">${services.length ? services.map((item) => `<div>${item.type || item.service_type} — до ${formatDate(item.expires_at)}</div>`).join('') : 'Услуг пока нет.'}</div>
        <div class="actions-row" style="margin-top: 12px;"><a href="#/services/${advert.id}"><button type="button">Купить / продлить</button></a></div>
      </div>
    `;

    document.getElementById('showPhoneButton').addEventListener('click', () => {
      document.getElementById('phoneBox').textContent = authorPhone || 'Телефон не указан';
    });

    document.querySelectorAll('[data-gallery-src]').forEach((thumb) => {
      thumb.addEventListener('click', () => {
        document.getElementById('mainGalleryImage').src = thumb.dataset.gallerySrc;
        document.querySelectorAll('[data-gallery-src]').forEach((img) => img.classList.remove('active'));
        thumb.classList.add('active');
      });
    });
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    setLoading(false);
  }
}

async function renderProfile() {
  if (!requireAuth()) return;
  showPage('page-profile');
  setLoading(true);
  try {
    const user = await api('/profile');
    state.user = user;
    localStorage.setItem('user', JSON.stringify(user));
    document.getElementById('profileName').value = user.name || '';
    document.getElementById('profileEmail').value = user.email || '';
    document.getElementById('profilePhone').value = user.phone || '';
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    setLoading(false);
  }
}

function renderCreate() {
  if (!requireAuth()) return;
  showPage('page-create');
  document.getElementById('advertFormTitle').textContent = 'Создать объявление';
}

async function openEdit(id) {
  if (!requireAuth()) return;
  location.hash = '#/create';
  showPage('page-create');
  setLoading(true);
  try {
    const advert = await api(`/adverts/${id}`);
    document.getElementById('advertFormTitle').textContent = 'Редактировать объявление';
    document.getElementById('advertId').value = advert.id;
    document.getElementById('advertTitle').value = advert.title || '';
    document.getElementById('advertText').value = advert.text || '';
    document.getElementById('advertPrice').value = advert.price || '';
    document.getElementById('advertCategory').value = advert.category_id || advert.category?.id || '';
    const photos = (advert.photos || []).map((item) => typeof item === 'string' ? item : item.url).join(', ');
    document.getElementById('advertPhotos').value = photos;
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    setLoading(false);
  }
}
window.openEdit = openEdit;

async function renderServices(id) {
  if (!requireAuth()) return;
  showPage('page-services');
  setLoading(true);
  try {
    const services = await api(`/adverts/${id}/services`);
    document.getElementById('servicesBox').innerHTML = `
      <p class="page-text">Простая учебная страница покупки услуг. Для каждой услуги можно выбрать срок и отправить запрос в API.</p>
      <div class="service-box">
        <strong>Уже подключено:</strong>
        <div style="margin-top: 8px;">${services.length ? services.map((item) => `<div>${item.type} — до ${formatDate(item.expires_at)}</div>`).join('') : 'Услуг пока нет.'}</div>
      </div>
      <form id="serviceBuyForm" class="simple-form" style="margin-top: 14px;">
        <input type="hidden" name="advert_id" value="${id}">
        <label for="serviceType">Тип услуги</label>
        <select id="serviceType" name="type">
          <option value="vip">VIP</option>
          <option value="top">TOP</option>
        </select>
        <label for="serviceDays">Срок</label>
        <select id="serviceDays" name="days">
          <option value="3">3 дня</option>
          <option value="7">7 дней</option>
        </select>
        <button type="submit">Купить / продлить</button>
      </form>
    `;

    document.getElementById('serviceBuyForm').addEventListener('submit', async (event) => {
      event.preventDefault();
      const formData = Object.fromEntries(new FormData(event.target).entries());
      try {
        await api(`/adverts/${id}/services`, { method: 'POST', body: JSON.stringify(formData) });
        showMessage('Услуга успешно сохранена.');
        renderServices(id);
      } catch (error) {
        showMessage(error.message, 'error');
      }
    });
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    setLoading(false);
  }
}

function resetAdvertForm() {
  document.getElementById('advertForm').reset();
  document.getElementById('advertId').value = '';
  document.getElementById('advertFormTitle').textContent = 'Создать объявление';
}

async function handleRoute() {
  updateAuthLinks();
  const parts = getHashParts();
  const page = parts[0] || '';

  switch (page) {
    case 'login':
      showPage('page-login');
      break;
    case 'register':
      showPage('page-register');
      break;
    case 'profile':
      await renderProfile();
      break;
    case 'my-adverts':
      await renderMyAdverts();
      break;
    case 'create':
      renderCreate();
      break;
    case 'details':
      await renderDetails(parts[1]);
      break;
    case 'services':
      await renderServices(parts[1]);
      break;
    default:
      await renderHome();
      break;
  }
}

// --- Обработчики форм ---
document.getElementById('homeFilterForm').addEventListener('submit', (event) => {
  event.preventDefault();
  renderHome();
});

document.getElementById('homeResetButton').addEventListener('click', () => {
  document.getElementById('homeFilterForm').reset();
  renderHome();
});

document.getElementById('loginForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const payload = Object.fromEntries(new FormData(event.target).entries());
  try {
    const data = await api('/auth/login', { method: 'POST', body: JSON.stringify(payload) });
    saveAuth(data.token, data.user);
    showMessage('Вы успешно вошли.');
    location.hash = '#/profile';
  } catch (error) {
    showMessage(error.message, 'error');
  }
});

document.getElementById('registerForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const payload = Object.fromEntries(new FormData(event.target).entries());
  try {
    const data = await api('/auth/register', { method: 'POST', body: JSON.stringify(payload) });
    saveAuth(data.token, data.user);
    showMessage('Регистрация прошла успешно.');
    location.hash = '#/profile';
  } catch (error) {
    showMessage(error.message, 'error');
  }
});

document.getElementById('profileForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    const payload = Object.fromEntries(new FormData(event.target).entries());
    const user = await api('/profile', { method: 'PUT', body: JSON.stringify(payload) });
    saveAuth(state.token, user);
    showMessage('Профиль обновлён.');
  } catch (error) {
    showMessage(error.message, 'error');
  }
});

document.getElementById('myAdvertsFilterForm').addEventListener('submit', (event) => {
  event.preventDefault();
  renderMyAdverts();
});

document.getElementById('advertForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const payload = Object.fromEntries(new FormData(event.target).entries());
  payload.photos = payload.photos.split(',').map((item) => item.trim()).filter(Boolean);

  try {
    if (payload.id) {
      await api(`/adverts/${payload.id}`, { method: 'PUT', body: JSON.stringify(payload) });
      showMessage('Объявление обновлено.');
    } else {
      await api('/adverts', { method: 'POST', body: JSON.stringify(payload) });
      showMessage('Объявление создано.');
    }
    resetAdvertForm();
    location.hash = '#/my-adverts';
  } catch (error) {
    showMessage(error.message, 'error');
  }
});

document.getElementById('advertFormResetButton').addEventListener('click', resetAdvertForm);
document.getElementById('logoutButton').addEventListener('click', logout);
window.addEventListener('hashchange', handleRoute);

(async function init() {
  updateAuthLinks();
  try {
    await loadCategories();
  } catch (error) {
    showMessage('Не удалось загрузить категории. Проверьте API.', 'error');
  }
  handleRoute();
})();
