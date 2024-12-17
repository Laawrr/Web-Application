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
          <div class="pa-4">
            <div class="text-h5 mb-4">Users Activity Log</div>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              hide-details
              style="max-width: 300px;"
            ></v-text-field>
          </div>
        </template>

        <template v-slot:headers="{ columns }">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="text-subtitle-1 font-weight-bold"
            >
              {{ column.title }}
            </th>
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
  color: rgba(0, 0, 0, 0.87) !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  background: white !important;
  text-transform: none !important;
  letter-spacing: 0 !important;
  border-bottom: thin solid rgba(0, 0, 0, 0.12) !important;
}

:deep(.v-data-table tbody td) {
  color: rgba(0, 0, 0, 0.87) !important;
}

.text-h5 {
  color: rgba(0, 0, 0, 0.87);
  font-weight: 500;
}
</style>