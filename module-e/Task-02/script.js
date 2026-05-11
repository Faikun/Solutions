const lines = [
  "ligne n°1",
  "bus n°24",
  "tramway n°3",
  "ligne n°2",
  "bus n°20",
  "tramway n°312",
  "ligne n°130",
  "bus n°28",
  "tramway n°20",
  "ligne n°101",
];
function getLineNumber(line) {
  const match = line.match(/\d+/);
  return match ? Number(match[0]) : 0;
}
const sortedLines = [...lines].sort(
  (a, b) => getLineNumber(a) - getLineNumber(b),
);
console.log(sortedLines);
