document.addEventListener('DOMContentLoaded', function () {
  setButtonLinks();
  markActiveLinks();
  setupPriceRanges();
  setupContactForm();
  setupImageFallbacks();
});

function setButtonLinks() {
  var publishLinks = document.querySelectorAll('[data-role="publish-link"]');
  var viewLinks = document.querySelectorAll('[data-role="view-link"]');

  publishLinks.forEach(function (link) {
    link.href = window.APP_CONFIG.publishLink;
  });

  viewLinks.forEach(function (link) {
    link.href = window.APP_CONFIG.viewLink;
  });
}

function markActiveLinks() {
  var currentPage = document.body.getAttribute('data-page');
  var links = document.querySelectorAll('[data-page-link]');

  links.forEach(function (link) {
    if (link.getAttribute('data-page-link') === currentPage) {
      link.classList.add('active-link');
    }
  });
}

function setupPriceRanges() {
  var cards = document.querySelectorAll('[data-service]');

  cards.forEach(function (card) {
    var serviceName = card.getAttribute('data-service');
    var range = card.querySelector('input[type="range"]');
    var daysOutput = card.querySelector('[data-days-output]');
    var priceOutput = card.querySelector('[data-price-output]');
    var options = window.APP_CONFIG.serviceOptions[serviceName];

    if (!range || !options) return;

    function updateCard() {
      var optionIndex = Number(range.value);
      var option = options[optionIndex];
      daysOutput.textContent = option.days + ' дн.';
      priceOutput.textContent = '$' + option.price.toFixed(2);
    }

    range.addEventListener('input', updateCard);
    updateCard();
  });
}

function setupContactForm() {
  var form = document.querySelector('[data-contact-form]');
  if (!form) return;

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var message = form.querySelector('[data-form-message]');
    if (message) {
      message.classList.add('show');
      message.textContent = 'Форма работает как демонстрация. Для реальной отправки можно подключить серверную обработку.';
    }
    form.reset();
  });
}

function setupImageFallbacks() {
  var images = document.querySelectorAll('[data-image-fallback]');

  images.forEach(function (image) {
    image.addEventListener('error', function () {
      var fallbackId = image.getAttribute('data-image-fallback');
      var fallback = document.getElementById(fallbackId);
      image.style.display = 'none';
      if (fallback) {
        fallback.classList.add('show');
      }
    });
  });
}
