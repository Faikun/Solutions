const xInput = document.getElementById("x");
const yInput = document.getElementById("y");
const spreadInput = document.getElementById("spread");
const blurInput = document.getElementById("blur");
const colorInput = document.getElementById("color");
const previewBox = document.getElementById("preview-box");
const shadowValue = document.getElementById("shadow-value");
const copyBtn = document.getElementById("copy-btn");
const rangeInputs = [xInput, yInput, spreadInput, blurInput];
function getShadowString() {
  return `${xInput.value}px ${yInput.value}px ${blurInput.value}px ${spreadInput.value}px ${colorInput.value}`;
}
function renderShadow() {
  const shadow = getShadowString();
  previewBox.style.boxShadow = shadow;
  shadowValue.textContent = shadow;
  rangeInputs.forEach((input) => {
    input.style.accentColor = colorInput.value;
  });
}
rangeInputs.forEach((input) => input.addEventListener("input", renderShadow));
colorInput.addEventListener("input", renderShadow);
copyBtn.addEventListener("click", async () => {
  try {
    await navigator.clipboard.writeText(getShadowString());
    copyBtn.textContent = "Copied!";
    setTimeout(() => {
      copyBtn.textContent = "Copy";
    }, 1200);
  } catch {
    alert("Не удалось скопировать значение.");
  }
});
renderShadow();
