<template>
  <v-container>
    <!-- Overview Cards -->
    <v-row>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Total Users</v-card-title>
          <v-card-text class="stat-value">{{ stats.totalUsers }}</v-card-text>
          <v-icon color="white" large>mdi-account-multiple</v-icon>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Lost Items</v-card-title>
          <v-card-text class="stat-value">{{ stats.lostItems }}</v-card-text>
          <v-icon color="white" large>mdi-cart</v-icon>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Found Items</v-card-title>
          <v-card-text class="stat-value">{{ stats.foundItems }}</v-card-text>
          <v-icon color="white" large>mdi-trending-up</v-icon>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Total Claims</v-card-title>
          <v-card-text class="stat-value">{{ stats.claims }}</v-card-text>
          <v-icon color="white" large>mdi-bell</v-icon>
        </v-card>
      </v-col>
    </v-row>

    <!-- User Activity Section -->
    <v-row>
      <v-col cols="12">
        <v-card class="user-card" outlined>
          <v-card-title>Notifications</v-card-title>
          <v-card-subtitle>Howdy, {{ user.name }}!</v-card-subtitle>
          <v-card-text>
            Last login: {{ lastLogin }} from <strong>{{ ipAddress }}</strong>
          </v-card-text>
          <v-chip color="primary" text-color="white">{{ userStatus }}</v-chip>
        </v-card>
      </v-col>
    </v-row>

    <!-- Activity List -->
    <v-row>
      <v-col cols="12" sm="4" v-for="(activity, index) in activities" :key="index">
        <v-card class="activity-card" outlined>
          <v-list-item>
            <v-list-item-avatar color="secondary">
              <v-icon>mdi-account-circle</v-icon>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title>{{ activity.name }}</v-list-item-title>
              <v-list-item-subtitle>{{ activity.date }}</v-list-item-subtitle>
            </v-list-item-content>
            <v-chip :color="activity.statusColor" dark>
              {{ activity.status }}
            </v-chip>
          </v-list-item>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'Dashboard',
  data() {
    return {
      stats: {
        totalUsers: 512,
        lostItems: 7770,
        foundItems: 256,
        claims: 24,
      },
      user: {
        name: 'John Doe'
      },
      lastLogin: '12 mins ago',
      ipAddress: '127.0.0.1',
      userStatus: 'Verified',
      activities: [
        { name: "Howell Hand", date: "Mar 3, 2021", status: "+70%", statusColor: "primary" },
        { name: "Hope Howe", date: "Dec 1, 2021", status: "+82%", statusColor: "secondary" },
        { name: "Nelson Jerde", date: "May 18, 2021", status: "-40%", statusColor: "coral" },
      ],
    }
  },
  mounted() {
    this.fetchDashboardData();
  },
  methods: {
    async fetchDashboardData() {
      try {
        const response = await fetch('/admin/dashboard');
        const data = await response.json();
        this.stats = data;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    }
  }
}
</script>

<style scoped>
.card-overview {
  padding: 15px;
  text-align: center;
  background-color: #FFFFFF;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  color: #333333;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #181C14;
}

.user-card {
  padding: 20px;
  border-radius: 8px;
  background-color: #FFFFFF;
}

.activity-card {
  background-color: #FFFFFF;
  border-radius: 8px;
}

.teal-bg {
  background: var(--v-teal) !important;
  color: white !important;
}

.coral-bg {
  background: var(--v-coral) !important;
  color: white !important;
}

.olive-bg {
  background: var(--v-accent-base) !important;
  color: white !important;
}
</style>
