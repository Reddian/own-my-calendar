<template>
  <div class="app-layout">
    <nav class="main-nav">
      <div class="nav-brand">
        <router-link to="/">Own My Calendar</router-link>
      </div>
      <div class="nav-links" v-if="isAuthenticated">
        <router-link to="/" class="nav-link" exact-active-class="active">
          <i class="fas fa-home"></i>
          Dashboard
        </router-link>
        <router-link to="/calendar" class="nav-link" active-class="active">
          <i class="fas fa-calendar-alt"></i>
          Calendar
        </router-link>
        <router-link to="/tasks" class="nav-link" active-class="active">
          <i class="fas fa-tasks"></i>
          Tasks
        </router-link>
        <router-link to="/analytics" class="nav-link" active-class="active">
          <i class="fas fa-chart-line"></i>
          Analytics
        </router-link>
        <router-link to="/history" class="nav-link" active-class="active">
          <i class="fas fa-history"></i>
          History
        </router-link>
        <router-link to="/extension" class="nav-link" active-class="active">
          <i class="fas fa-puzzle-piece"></i>
          Extension
        </router-link>
        <router-link to="/settings" class="nav-link" active-class="active">
          <i class="fas fa-cog"></i>
          Settings
        </router-link>
      </div>
      <div class="nav-auth">
        <template v-if="isAuthenticated">
          <button @click="logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            Logout
          </button>
        </template>
        <template v-else>
          <router-link to="/login" class="btn-login">
            <i class="fas fa-sign-in-alt"></i>
            Login
          </router-link>
        </template>
      </div>
    </nav>

    <main class="main-content">
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'AppLayout',
  setup() {
    const router = useRouter();
    const isAuthenticated = computed(() => !!localStorage.getItem('auth_token'));

    const logout = () => {
      localStorage.removeItem('auth_token');
      router.push('/login');
    };

    return {
      isAuthenticated,
      logout
    };
  }
};
</script>

<style lang="scss" scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-nav {
  background: linear-gradient(135deg, #7e57ff, #43c6b9);
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

  .nav-brand {
    font-size: 1.5rem;
    font-weight: bold;

    a {
      color: white;
      text-decoration: none;
    }
  }

  .nav-links {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;

    .nav-link {
      color: white;
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      &:hover {
        background-color: rgba(255, 255, 255, 0.1);
      }

      &.active {
        background-color: rgba(255, 255, 255, 0.2);
      }

      i {
        font-size: 1rem;
      }
    }
  }

  .nav-auth {
    .btn-login,
    .btn-logout {
      padding: 0.5rem 1rem;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: white;
      text-decoration: none;
    }

    .btn-login {
      background-color: #42b983;

      &:hover {
        background-color: #3aa876;
      }
    }

    .btn-logout {
      background-color: #e74c3c;

      &:hover {
        background-color: #c0392b;
      }
    }
  }
}

.main-content {
  flex: 1;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

@media (max-width: 768px) {
  .main-nav {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;

    .nav-links {
      flex-direction: column;
      width: 100%;
      gap: 0.5rem;

      .nav-link {
        justify-content: center;
      }
    }

    .nav-auth {
      width: 100%;
      display: flex;
      justify-content: center;
    }
  }
}
</style> 