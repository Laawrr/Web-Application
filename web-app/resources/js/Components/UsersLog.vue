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
        v-model:page="page"
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

        <template v-slot:header.user.name>
          <span class="font-weight-black">User</span>
        </template>
        <template v-slot:header.action>
          <span class="font-weight-black">Action</span>
        </template>
        <template v-slot:header.action_time>
          <span class="font-weight-black">Time</span>
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

        <template v-slot:bottom>
          <div class="d-flex align-center justify-center pa-4 gap-4">
            <v-btn
              style="background-color: #4fb9af; color: white"
              variant="flat"
              :disabled="page === 1"
              @click="page--"
            >
              Previous
            </v-btn>
            <span>Page {{ page }} of {{ Math.ceil(logs.length / 10) }}</span>
            <v-btn
              style="background-color: #4fb9af; color: white"
              variant="flat"
              :disabled="page >= Math.ceil(logs.length / 10)"
              @click="page++"
            >
              Next
            </v-btn>
          </div>
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
      page: 1,
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
          // Sort logs by action_time in descending order
          this.logs.sort((a, b) => new Date(b.action_time) - new Date(a.action_time));
        })
        .catch(error => {
          console.error('Error fetching activity logs:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    formatDate(dateString) {
      const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };
      const date = new Date(dateString);
      return date.toLocaleString('en-US', options);
    },
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}

:deep(.v-data-table-header) {
  background-color: #f5f5f5 !important;
}

:deep(.v-data-table-header th) {
  color: black !important;
  font-weight: 700 !important;
  font-size: 14px !important;
  text-transform: none !important;
  letter-spacing: 0 !important;
  padding: 12px 16px !important;
  border-bottom: 2px solid rgba(0, 0, 0, 0.12) !important;
}

:deep(.v-data-table tbody td) {
  color: rgba(0, 0, 0, 0.87) !important;
  padding: 8px 16px !important;
}

.text-h5 {
  color: rgba(0, 0, 0, 0.87);
  font-weight: 500;
}

.font-weight-black {
  font-weight: 900 !important;
  color: black !important;
}
</style>