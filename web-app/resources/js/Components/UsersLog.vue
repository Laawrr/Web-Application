<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-data-table
        :headers="headers"
        :items="logs"
        :search="search"
        :loading="loading"
        :items-per-page="10"
        class="elevation-1"
      >
        <template v-slot:top>
          <v-card-title class="d-flex justify-space-between align-center">
            <div>Users Activity Log</div>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              hide-details
              class="ml-2"
              style="max-width: 300px;"
            ></v-text-field>
          </v-card-title>
        </template>

        <template v-slot:header="{ props }">
          <tr>
            <th class="text-left">User</th>
            <th class="text-left">Action</th>
            <th class="text-left">Time</th>
          </tr>
        </template>

        <template v-slot:item="{ item }">
          <tr>
            <td>{{ item.user.name }}</td>
            <td>{{ item.action }}</td>
            <td>{{ formatDate(item.action_time) }}</td>
          </tr>
        </template>

        <template v-slot:progress>
          <v-progress-linear
            color="#4fb9af"
            height="2"
            indeterminate
          ></v-progress-linear>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UsersLog',
  data() {
    return {
      search: '',
      headers: [
        { 
          text: 'User',
          value: 'user.name',
          width: '20%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        { 
          text: 'Action',
          value: 'action',
          width: '40%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        { 
          text: 'Time',
          value: 'action_time',
          width: '20%',
          align: 'start',
          sortable: true,
        },
      ],
      logs: [],
      loading: true,
    };
  },
  created() {
    this.fetchLogs();
  },
  methods: {
    fetchLogs() {
      this.loading = true;
      axios.get('/admin/users-log')
        .then(response => {
          this.logs = response.data.activityLog;
        })
        .catch(error => {
          console.error('Error fetching activity logs:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    formatDate(date) {
      return new Date(date).toLocaleString();
    },
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}

:deep(.v-data-table-header th) {
  color: black !important;
  font-weight: bold !important;
  font-size: 1rem !important;
  text-transform: none !important;
  background-color: #f5f5f5 !important;
  padding: 12px 16px !important;
}

:deep(.v-data-table tbody td) {
  color: black !important;
  padding: 12px 16px !important;
}

:deep(.v-data-table tbody tr:nth-child(even)) {
  background-color: #f9f9f9;
}

:deep(.v-data-table tbody tr:hover) {
  background-color: #f0f0f0;
}
</style>