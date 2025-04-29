<template>
  <div class="app-layout">
    <nav class="main-nav">
      <div class="nav-brand">
        <router-link to="/">Own My Calendar</router-link>
      </div>
      <div class="nav-links" v-if="isAuthenticated">
        <router-link to="/" class="nav-link">Dashboard</router-link>
        <router-link to="/calendar" class="nav-link">Calendar</router-link>
        <router-link to="/settings" class="nav-link">Settings</router-link>
      </div>
      <div class="nav-auth">
        <template v-if="isAuthenticated">
          <button @click="logout" class="btn-logout">Logout</button>
        </template>
        <template v-else>
          <router-link to="/login" class="btn-login">Login</router-link>
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
  background-color: #2c3e50;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;

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

    .nav-link {
      color: white;
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      transition: background-color 0.2s;

      &:hover {
        background-color: rgba(255, 255, 255, 0.1);
      }

      &.router-link-active {
        background-color: rgba(255, 255, 255, 0.2);
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
      transition: background-color 0.2s;
    }

    .btn-login {
      background-color: #42b983;
      color: white;
      text-decoration: none;

      &:hover {
        background-color: #3aa876;
      }
    }

    .btn-logout {
      background-color: #e74c3c;
      color: white;

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
</style> 