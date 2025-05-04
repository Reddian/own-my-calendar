<template>
  <div class="onboarding-form-container">
    <h2>Welcome! Let's set up your profile.</h2>
    <p>Understanding your goals helps us tailor your experience.</p>
    <form @submit.prevent="submitOnboarding">
      <div class="mb-3">
        <label for="mt_everest" class="form-label">What is your Mt Everest? (Your ultimate goal or aspiration)</label>
        <textarea id="mt_everest" class="form-control" v-model="profileData.mt_everest" rows="3" required></textarea>
      </div>
      <div class="mb-3">
        <label for="money_making_activities" class="form-label">What are your top 5 money making activities?</label>
        <textarea id="money_making_activities" class="form-control" v-model="profileData.money_making_activities" rows="5" required></textarea>
      </div>
      <div class="mb-3">
        <label for="energy_renewal_activities" class="form-label">Through what activities do you renew your energy?</label>
        <textarea id="energy_renewal_activities" class="form-control" v-model="profileData.energy_renewal_activities" rows="3" required></textarea>
      </div>

      <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

      <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
        <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        {{ isSubmitting ? "Saving..." : "Save Profile & Continue" }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";
import { useStore } from "vuex";

const store = useStore();
const emit = defineEmits(["onboarding-complete"]);

const profileData = reactive({
  mt_everest: "",
  money_making_activities: "",
  energy_renewal_activities: "",
});
const isSubmitting = ref(false);
const errorMessage = ref(null);

// Fetch existing profile data if available (for settings page usage)
async function fetchProfileData() {
  try {
    const response = await axios.get("/api/user/profile-onboarding");
    if (response.data.profile) {
      profileData.mt_everest = response.data.profile.mt_everest || "";
      profileData.money_making_activities = response.data.profile.money_making_activities || "";
      profileData.energy_renewal_activities = response.data.profile.energy_renewal_activities || "";
    }
  } catch (error) {
    console.error("Error fetching onboarding profile data:", error);
    // Non-critical error, form will just be empty
  }
}

async function submitOnboarding() {
  isSubmitting.value = true;
  errorMessage.value = null;
  try {
    await axios.post("/api/user/profile-onboarding", profileData);
    // Update user state in Vuex to mark onboarding as complete
    await store.dispatch("user/fetchUser"); 
    emit("onboarding-complete");
  } catch (error) {
    console.error("Error submitting onboarding data:", error);
    errorMessage.value = error.response?.data?.message || "Failed to save profile. Please try again.";
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  // Fetch data when component mounts, useful if used in settings
  fetchProfileData(); 
});

</script>

<style scoped>
.onboarding-form-container {
  max-width: 600px;
  margin: 2rem auto;
  padding: 2rem;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h2 {
  font-family: "Playfair Display", serif;
  color: var(--primary-purple);
  text-align: center;
  margin-bottom: 1rem;
}

p {
  text-align: center;
  color: var(--medium-text);
  margin-bottom: 2rem;
}

.form-label {
  font-weight: bold;
  color: var(--dark-text);
}

.form-control {
  margin-bottom: 1rem; /* Add space below textareas */
}

.btn-primary {
  width: 100%;
  padding: 0.75rem;
  font-size: 1.1rem;
}
</style>

