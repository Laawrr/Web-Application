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

    <!-- Pie Chart -->
    <v-row>
      <v-col cols="12" sm="6">
        <v-card outlined>
          <v-card-title>Item Statistics</v-card-title>
          <pie-chart ref="pieChart" :data="pieChartData" />
        </v-card>
      </v-col>

      <!-- Bar Graph -->
      <v-col cols="12" sm="6">
        <v-card outlined>
          <v-card-title>Item Overview</v-card-title>
          <bar-chart ref="barChart" :data="barChartData" />
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { Pie, Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);

export default {
  name: 'Dashboard',
  components: {
    PieChart: Pie,
    BarChart: Bar
  },
  data() {
    return {
      stats: {
        totalUsers: 1,
        lostItems: 2,
        foundItems: 1,
        claims: 0,
      },
      pieChartData: {
        labels: ['Lost Items', 'Found Items', 'Claims'],
        datasets: [{
          label: 'Item Statistics',
          data: [0, 0, 0],  // Initial data, will be updated on fetch
          backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
          hoverOffset: 4
        }]
      },
      barChartData: {
        labels: ['Lost Items', 'Found Items', 'Claims'],
        datasets: [{
          label: 'Item Overview',
          data: [0, 0, 0],  // Initial data, will be updated on fetch
          backgroundColor: '#42A5F5',
          borderColor: '#1E88E5',
          borderWidth: 1
        }]
      }
    };
  },
  mounted() {
    this.fetchDashboardData();
  },
  watch: {
    stats: {
      handler(newStats) {
        console.log('Stats updated:', newStats);
        // Update chart data
        this.pieChartData.datasets[0].data = [newStats.lostItems, newStats.foundItems, newStats.claims];
        this.barChartData.datasets[0].data = [newStats.lostItems, newStats.foundItems, newStats.claims];
        // Force charts to update
        this.$nextTick(() => {
          this.$refs.pieChart.chart.update();
          this.$refs.barChart.chart.update();
        });
      },
      deep: true
    }
  },
  methods: {
    async fetchDashboardData() {
      try {
        const response = await fetch('/admin/dashboard');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        // Update the stats and charts
        this.stats = {
          totalUsers: data.totalUsers,
          lostItems: data.lostItems,
          foundItems: data.foundItems,
          claims: data.claims,
        };
        
        // Update pie and bar chart data
        this.pieChartData.datasets[0].data = [data.lostItems, data.foundItems, data.claims];
        this.barChartData.datasets[0].data = [data.lostItems, data.foundItems, data.claims];
        
        this.$nextTick(() => {
          this.$refs.pieChart.chart.update();
          this.$refs.barChart.chart.update();
        });
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
