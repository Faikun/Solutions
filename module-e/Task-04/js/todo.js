const form = document.getElementById('todo-form');
const titleInput = document.getElementById('title');
const descriptionInput = document.getElementById('description');
const categoryInput = document.getElementById('category');
const list = document.getElementById('todo-list');
const filterButtons = document.querySelectorAll('.filter');
const STORAGE_KEY = 'worldskills-task-todos';
let tasks = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
let currentFilter = 'All';
function saveTasks() { localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks)); }
function createId() { return Date.now().toString(); }
function getFilteredTasks() { return currentFilter === 'All' ? tasks : tasks.filter(task => task.category === currentFilter); }
function renderTasks() {
  const filteredTasks = getFilteredTasks();
  if (filteredTasks.length === 0) { list.innerHTML = '<div class="empty">No tasks in this category yet.</div>'; return; }
  list.innerHTML = filteredTasks.map(task => `
    <article class="task ${task.completed ? 'done' : ''}">
      <div class="task-top"><div><h3>${task.title}</h3><p>${task.description}</p></div><span class="badge">${task.category}</span></div>
      <div class="row"><label><input type="checkbox" ${task.completed ? 'checked' : ''} onchange="toggleTask('${task.id}')"> Completed</label><button class="delete-btn" onclick="deleteTask('${task.id}')">Delete</button></div>
    </article>`).join('');
}
form.addEventListener('submit', (event) => { event.preventDefault(); tasks.unshift({ id: createId(), title: titleInput.value.trim(), description: descriptionInput.value.trim(), category: categoryInput.value, completed: false }); saveTasks(); renderTasks(); form.reset(); });
window.toggleTask = function(taskId) { tasks = tasks.map(task => task.id === taskId ? { ...task, completed: !task.completed } : task); saveTasks(); renderTasks(); };
window.deleteTask = function(taskId) { tasks = tasks.filter(task => task.id !== taskId); saveTasks(); renderTasks(); };
filterButtons.forEach(button => button.addEventListener('click', () => { currentFilter = button.dataset.filter; filterButtons.forEach(item => item.classList.remove('active')); button.classList.add('active'); renderTasks(); }));
renderTasks();
