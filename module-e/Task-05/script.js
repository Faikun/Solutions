const clockElement = document.getElementById("clock");
function formatPart(value) {
  return String(value).padStart(2, "0");
}
function renderTime() {
  const now = new Date();
  clockElement.textContent = `${formatPart(now.getHours())}:${formatPart(now.getMinutes())}:${formatPart(now.getSeconds())}`;
}
renderTime();
setInterval(renderTime, 1000);
