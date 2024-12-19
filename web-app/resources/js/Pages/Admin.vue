<template>
  <v-app>
    <!-- Top Navigation Bar -->
    <v-app-bar app color="white" dark style="min-height: 80px;">
      <v-toolbar-title class="font-weight-bold">
        <div class="d-flex align-center mt-3">
          <img src="/img/image2.png" alt="Logo" class="logo" style="margin-left: 50px" />
          <span class="ml-3 text-teal-500 font-weight-bold">Admin Dashboard</span>
        </div>
      </v-toolbar-title>

      <v-spacer></v-spacer>
      <v-btn icon>
        <v-icon>mdi-bell</v-icon>
      </v-btn>
      <v-btn text color="secondary" class="logout-btn mt-3" @click="handleSignOut" style="margin-right: 80px;">
        Logout
      </v-btn>
    </v-app-bar>

    <!-- Sidebar Navigation -->
    <v-navigation-drawer app color="background" dark permanent style="padding-top: 50px;">
      <v-list dense>
        <v-list-item-group color="accent" style="font-size: 20px;">
          <v-list-item @click="currentView = 'dashboard'">
            <v-icon class="mr-2" color="accent">mdi-view-dashboard</v-icon>
            <v-list-item-title>Dashboard</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'users'">
            <v-icon class="mr-2" color="accent">mdi-account</v-icon>
            <v-list-item-title>Users</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'usersLog'">
            <v-icon class="mr-2" color="accent">mdi-clipboard-text</v-icon>
            <v-list-item-title>Users Log</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'reportedItems'">
            <v-icon class="mr-2" color="accent">mdi-flag</v-icon>
            <v-list-item-title>Reported Items</v-list-item-title>
          </v-list-item>
          <v-list-item @click="currentView = 'claims'">
            <v-icon class="mr-2" color="accent">mdi-flag</v-icon>
            <v-list-item-title>Claims</v-list-item-title>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main class="bg-light-gray" style="margin-top: 50px;">
      <v-container fluid>
        <!-- Different views based on navigation -->
        <div v-if="currentView === 'dashboard'">
          <Dashboard />
        </div>

        <div v-else-if="currentView === 'users'">
          <UsersView />
        </div>

        <div v-else-if="currentView === 'usersLog'">
          <UsersLog />
        </div>

        <div v-else-if="currentView === 'reportedItems'">
          <ReportedItems />
        </div>

        <div v-else-if="currentView === 'claims'" class="space-y-4">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Claims Management</h2>
            <div class="flex space-x-2">
              <v-btn v-for="filter in ['all', 'Pending', 'Approved', 'Rejected']" :key="filter"
                :color="currentFilter === filter ? 'primary' : ''" @click="currentFilter = filter" small outlined>
                {{ filter }}
              </v-btn>
            </div>
          </div>

          <!-- Claims List -->
          <div class="grid gap-4">
            <ClaimedItem v-for="claim in filteredClaims" :key="claim.id" :item="claim.item" :status="claim.claim_status"
              :proofImage="claim.proof_image" :claimId="claim.id" @status-updated="handleStatusUpdate" />
          </div>
        </div>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import Dashboard from '@/Components/Dashboard.vue';
import UsersView from '@/Components/UsersView.vue';
import UsersLog from '@/Components/UsersLog.vue';
import ReportedItems from '@/Components/ReportedItems.vue';
import ClaimedItem from '@/Components/ClaimedItem.vue';
import { Link, router } from '@inertiajs/vue3';

const handleSignOut = () => {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      router.visit('/');
    }
  });
};

const currentView = ref('dashboard');
const currentFilter = ref('all');
const claims = ref([
  // Sample data - replace with actual API call
  {
    id: 1,
    item: {
      name: 'Lost Phone',
      description: 'iPhone 12 Pro Max',
      location: 'Library',
      created_at: '2023-12-19',
      image_url: '/img/sample-item.jpg'
    },
    claim_status: 'Pending', // Correct claim status here
    proof_image: '/img/sample-proof.jpg'
  },
  // Add more sample claims as needed
]);

const filteredClaims = computed(() => {
  if (currentFilter.value === 'all') {
    return claims.value;
  }
  return claims.value.filter(claim => claim.claim_status === currentFilter.value);
});

// Handle claim status updates
const handleStatusUpdate = ({ id, status }) => {
  const claimIndex = claims.value.findIndex(claim => claim.id === id);
  if (claimIndex !== -1) {
    claims.value[claimIndex].claim_status = status;
  }
};
</script>

<style scoped>
.bg-light-gray {
  background-color: #4fb9af;
}

.logo {
  height: 40px;
  width: 40px;
}

:root {
  --v-primary-base: #181C14;
  --v-secondary-base: #FF7F50;
  --v-background-base: #ECDFCC;
  --v-card-base: #FFFFFF;
  --v-text-base: #333333;
  --v-accent-base: #697565;
  --v-coral: #FF7F50;
  --v-teal: #008080;
}
</style>
