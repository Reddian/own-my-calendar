<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="card tasks-card">
          <div class="card-header">
            <h1 class="text-center">Tasks</h1>
          </div>
          <div class="card-body">
            <!-- Task Filters -->
            <div class="task-filters mb-4">
              <div class="row align-items-center">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="statusFilter">Status</label>
                    <select 
                      id="statusFilter" 
                      class="form-select" 
                      v-model="filters.status"
                      @change="fetchTasks"
                    >
                      <option value="all">All Tasks</option>
                      <option value="pending">Pending</option>
                      <option value="completed">Completed</option>
                      <option value="overdue">Overdue</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="priorityFilter">Priority</label>
                    <select 
                      id="priorityFilter" 
                      class="form-select" 
                      v-model="filters.priority"
                      @change="fetchTasks"
                    >
                      <option value="all">All Priorities</option>
                      <option value="high">High</option>
                      <option value="medium">Medium</option>
                      <option value="low">Low</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="dateFilter">Due Date</label>
                    <select 
                      id="dateFilter" 
                      class="form-select" 
                      v-model="filters.date"
                      @change="fetchTasks"
                    >
                      <option value="all">All Dates</option>
                      <option value="today">Today</option>
                      <option value="week">This Week</option>
                      <option value="month">This Month</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <button 
                    class="btn btn-primary w-100 mt-4"
                    @click="showAddTaskModal = true"
                  >
                    <i class="fas fa-plus me-2"></i>Add Task
                  </button>
                </div>
              </div>
            </div>

            <!-- Tasks List -->
            <div class="tasks-list">
              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-else-if="tasks.length === 0" class="text-center py-4">
                <p class="text-muted">No tasks found</p>
              </div>
              <div v-else>
                <div 
                  v-for="task in tasks" 
                  :key="task.id"
                  class="task-item"
                  :class="{
                    'completed': task.status === 'completed',
                    'overdue': task.status === 'overdue'
                  }"
                >
                  <div class="task-checkbox">
                    <input 
                      type="checkbox" 
                      :checked="task.status === 'completed'"
                      @change="toggleTaskStatus(task)"
                    >
                  </div>
                  <div class="task-content">
                    <div class="task-header">
                      <h4 class="task-title">{{ task.title }}</h4>
                      <div class="task-priority" :class="task.priority">
                        {{ task.priority }}
                      </div>
                    </div>
                    <p class="task-description">{{ task.description }}</p>
                    <div class="task-footer">
                      <div class="task-due-date">
                        <i class="fas fa-calendar-alt"></i>
                        {{ task.due_date }}
                      </div>
                      <div class="task-actions">
                        <button 
                          class="btn btn-sm btn-outline-primary"
                          @click="editTask(task)"
                        >
                          <i class="fas fa-edit"></i>
                        </button>
                        <button 
                          class="btn btn-sm btn-outline-danger"
                          @click="deleteTask(task)"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Task Modal -->
    <div v-if="showAddTaskModal" class="modal fade show" style="display: block;" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingTask ? 'Edit Task' : 'Add New Task' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveTask">
              <div class="mb-3">
                <label for="taskTitle" class="form-label">Title</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="taskTitle" 
                  v-model="taskForm.title"
                  required
                >
              </div>
              <div class="mb-3">
                <label for="taskDescription" class="form-label">Description</label>
                <textarea 
                  class="form-control" 
                  id="taskDescription" 
                  v-model="taskForm.description"
                  rows="3"
                ></textarea>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="taskPriority" class="form-label">Priority</label>
                  <select 
                    class="form-select" 
                    id="taskPriority" 
                    v-model="taskForm.priority"
                    required
                  >
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="taskDueDate" class="form-label">Due Date</label>
                  <input 
                    type="date" 
                    class="form-control" 
                    id="taskDueDate" 
                    v-model="taskForm.due_date"
                    required
                  >
                </div>
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Task</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'Tasks',
  setup() {
    const tasks = ref([]);
    const loading = ref(false);
    const showAddTaskModal = ref(false);
    const editingTask = ref(null);
    const filters = ref({
      status: 'all',
      priority: 'all',
      date: 'all'
    });

    const taskForm = ref({
      title: '',
      description: '',
      priority: 'medium',
      due_date: ''
    });

    const fetchTasks = async () => {
      loading.value = true;
      try {
        const params = {
          status: filters.value.status,
          priority: filters.value.priority,
          date: filters.value.date
        };

        const response = await axios.get('/api/tasks', { params });
        tasks.value = response.data;
      } catch (error) {
        console.error('Failed to fetch tasks:', error);
      } finally {
        loading.value = false;
      }
    };

    const toggleTaskStatus = async (task) => {
      try {
        const newStatus = task.status === 'completed' ? 'pending' : 'completed';
        await axios.put(`/api/tasks/${task.id}/status`, { status: newStatus });
        task.status = newStatus;
      } catch (error) {
        console.error('Failed to update task status:', error);
      }
    };

    const editTask = (task) => {
      editingTask.value = task;
      taskForm.value = { ...task };
      showAddTaskModal.value = true;
    };

    const deleteTask = async (task) => {
      if (confirm('Are you sure you want to delete this task?')) {
        try {
          await axios.delete(`/api/tasks/${task.id}`);
          tasks.value = tasks.value.filter(t => t.id !== task.id);
        } catch (error) {
          console.error('Failed to delete task:', error);
        }
      }
    };

    const saveTask = async () => {
      try {
        if (editingTask.value) {
          await axios.put(`/api/tasks/${editingTask.value.id}`, taskForm.value);
        } else {
          await axios.post('/api/tasks', taskForm.value);
        }
        closeModal();
        fetchTasks();
      } catch (error) {
        console.error('Failed to save task:', error);
      }
    };

    const closeModal = () => {
      showAddTaskModal.value = false;
      editingTask.value = null;
      taskForm.value = {
        title: '',
        description: '',
        priority: 'medium',
        due_date: ''
      };
    };

    onMounted(() => {
      fetchTasks();
    });

    return {
      tasks,
      loading,
      showAddTaskModal,
      editingTask,
      filters,
      taskForm,
      fetchTasks,
      toggleTaskStatus,
      editTask,
      deleteTask,
      saveTask,
      closeModal
    };
  }
};
</script>

<style scoped>
.tasks-card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
  color: white;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  padding: 20px;
}

.task-item {
  display: flex;
  padding: 15px;
  border-bottom: 1px solid #eee;
  background: white;
  transition: all 0.3s ease;
}

.task-item:hover {
  background: #f8f9fa;
}

.task-item.completed {
  opacity: 0.7;
}

.task-item.completed .task-title {
  text-decoration: line-through;
}

.task-item.overdue {
  border-left: 4px solid #dc3545;
}

.task-checkbox {
  margin-right: 15px;
  display: flex;
  align-items: center;
}

.task-content {
  flex: 1;
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.task-title {
  margin: 0;
  font-size: 1.1rem;
}

.task-priority {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: bold;
}

.task-priority.high {
  background: #ffebee;
  color: #c62828;
}

.task-priority.medium {
  background: #fff3e0;
  color: #ef6c00;
}

.task-priority.low {
  background: #e8f5e9;
  color: #2e7d32;
}

.task-description {
  color: #666;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.task-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.task-due-date {
  color: #666;
  font-size: 0.8rem;
}

.task-due-date i {
  margin-right: 5px;
}

.task-actions {
  display: flex;
  gap: 5px;
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style> 