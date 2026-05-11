const box = document.getElementById("box");
const stage = document.getElementById("stage");
let isDragging = false;
let shiftX = 0;
let shiftY = 0;
function startDrag(event) {
  isDragging = true;
  box.classList.add("dragging");
  const rect = box.getBoundingClientRect();
  shiftX = event.clientX - rect.left;
  shiftY = event.clientY - rect.top;
}
function moveDrag(event) {
  if (!isDragging) return;
  const stageRect = stage.getBoundingClientRect();
  let left = event.clientX - stageRect.left - shiftX;
  let top = event.clientY - stageRect.top - shiftY;
  left = Math.max(0, Math.min(left, stage.clientWidth - box.offsetWidth));
  top = Math.max(0, Math.min(top, stage.clientHeight - box.offsetHeight));
  box.style.left = `${left}px`;
  box.style.top = `${top}px`;
}
function endDrag() {
  isDragging = false;
  box.classList.remove("dragging");
}
box.addEventListener("mousedown", startDrag);
window.addEventListener("mousemove", moveDrag);
window.addEventListener("mouseup", endDrag);
box.addEventListener("dragstart", (event) => {
  event.preventDefault();
});
