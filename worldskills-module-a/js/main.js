(function () {
  const config = window.APP_CONFIG || {};

  const setButtonLinks = () => {
    document.querySelectorAll('[data-role="publish-link"]').forEach((link) => {
      link.setAttribute('href', config.buttonLinks?.publish || 'seller.html');
    });
    document.querySelectorAll('[data-role="view-link"]').forEach((link) => {
      link.setAttribute('href', config.buttonLinks?.view || 'buyer.html');
    });
  };

  const markActiveNav = () => {
    const page = document.body.dataset.page;
    if (!page) return;
    document.querySelectorAll('[data-page-link]').forEach((link) => {
      if (link.dataset.pageLink === page) link.classList.add('is-active');
    });
  };

  const attachFormStub = () => {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const notice = form.querySelector('[data-form-notice]');
      if (notice) {
        notice.textContent = 'Форма готова к интеграции с обработчиком. Сейчас отправка демонстрационная.';
        notice.classList.remove('hidden');
      }
      form.reset();
    });
  };

  const createFaqItem = (item) => {
    const details = document.createElement('details');
    details.className = 'faq-item';

    const summary = document.createElement('summary');
    summary.textContent = item.q;

    const body = document.createElement('div');
    body.className = 'faq-body';
    const p = document.createElement('p');
    p.textContent = item.a;
    body.appendChild(p);

    details.append(summary, body);
    return details;
  };

  const renderFaq = (selector, items) => {
    const container = document.querySelector(selector);
    if (!container || !Array.isArray(items)) return;
    container.innerHTML = '';
    items.forEach((item) => container.appendChild(createFaqItem(item)));
  };

  const renderSimpleList = (selector, items) => {
    const container = document.querySelector(selector);
    if (!container || !Array.isArray(items)) return;
    container.innerHTML = '';
    items.forEach((text) => {
      const li = document.createElement('li');
      li.textContent = text;
      container.appendChild(li);
    });
  };

  const formatCurrency = (value) => new Intl.NumberFormat('ru-RU').format(value) + ' ₸';

  const renderPricing = () => {
    const container = document.querySelector('[data-pricing-grid]');
    if (!container || !Array.isArray(config.fallback?.pricing)) return;

    container.innerHTML = '';

    config.fallback.pricing.forEach((item) => {
      const article = document.createElement('article');
      article.className = 'pricing-card';
      article.innerHTML = `
        <span class="card-eyebrow">Платная услуга</span>
        <h3>${item.title}</h3>
        <p>${item.description}</p>
        <div class="pricing-output">
          <div>
            <div class="muted">Итоговая стоимость</div>
            <strong data-total>${formatCurrency(item.pricePerDay * item.defaultDays)}</strong>
          </div>
          <div class="muted"><span data-days-label>${item.defaultDays}</span> дн.</div>
        </div>
        <div class="range-row">
          <label class="muted" for="${slugify(item.title)}">Количество дней</label>
          <input id="${slugify(item.title)}" type="range" min="${item.minDays}" max="${item.maxDays}" value="${item.defaultDays}" step="1" data-price="${item.pricePerDay}" aria-label="Количество дней для услуги ${item.title}">
          <div class="muted">${formatCurrency(item.pricePerDay)} за 1 день</div>
        </div>
      `;

      const input = article.querySelector('input[type="range"]');
      const total = article.querySelector('[data-total]');
      const label = article.querySelector('[data-days-label]');

      input.addEventListener('input', () => {
        const days = Number(input.value);
        total.textContent = formatCurrency(days * Number(input.dataset.price));
        label.textContent = String(days);
      });

      container.appendChild(article);
    });
  };

  const renderPolicy = () => {
    const container = document.querySelector('[data-policy-content]');
    if (!container || !Array.isArray(config.fallback?.policySections)) return;

    container.innerHTML = '';
    config.fallback.policySections.forEach((section) => {
      const article = document.createElement('article');
      const title = document.createElement('h2');
      title.textContent = section.title;
      article.appendChild(title);

      if (Array.isArray(section.paragraphs)) {
        section.paragraphs.forEach((text) => {
          const p = document.createElement('p');
          p.textContent = text;
          article.appendChild(p);
        });
      }

      if (Array.isArray(section.list)) {
        const ul = document.createElement('ul');
        ul.className = 'policy-list';
        section.list.forEach((text) => {
          const li = document.createElement('li');
          li.textContent = text;
          ul.appendChild(li);
        });
        article.appendChild(ul);
      }

      container.appendChild(article);
    });
  };

  const slugify = (text) =>
    text
      .toLowerCase()
      .replace(/[^a-zа-я0-9]+/gi, '-')
      .replace(/^-+|-+$/g, '');

  const hideBrokenMedia = () => {
    document.querySelectorAll('img').forEach((img) => {
      img.addEventListener('error', () => {
        img.closest('[data-hide-on-error]')?.classList.add('hidden');
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    setButtonLinks();
    markActiveNav();
    attachFormStub();
    renderSimpleList('[data-seller-advantages]', config.fallback?.sellerAdvantages || []);
    renderSimpleList('[data-buyer-advantages]', config.fallback?.buyerAdvantages || []);
    renderSimpleList('[data-price-advantages]', config.fallback?.priceAdvantages || []);
    renderFaq('[data-seller-faq]', config.fallback?.sellerFaq || []);
    renderFaq('[data-buyer-faq]', config.fallback?.buyerFaq || []);
    renderFaq('[data-price-faq]', config.fallback?.priceFaq || []);
    renderPricing();
    renderPolicy();
    hideBrokenMedia();
  });
})();
