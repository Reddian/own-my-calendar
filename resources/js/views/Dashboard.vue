<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="dashboard-grid">
      <div class="dashboard-card">
        <h2>Calendar Overview</h2>
        <div class="card-content">
          <p>View and manage your connected calendars</p>
          <router-link to="/calendar" class="btn-primary">Manage Calendars</router-link>
        </div>
      </div>

      <div class="dashboard-card">
        <h2>Settings</h2>
        <div class="card-content">
          <p>Configure your account and preferences</p>
          <router-link to="/settings" class="btn-primary">Go to Settings</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
  setup() {
    return {};
  }
};
</script>

<style lang="scss" scoped>
.dashboard {
  h1 {
    margin-bottom: 2rem;
  }
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.dashboard-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;

  h2 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #2c3e50;
  }

  .card-content {
    p {
      margin-bottom: 1rem;
      color: #666;
    }
  }
}

.btn-primary {
  display: inline-block;
  padding: 0.5rem 1rem;
  background-color: #42b983;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: background-color 0.2s;

  &:hover {
    background-color: #3aa876;
  }
}
</style> 