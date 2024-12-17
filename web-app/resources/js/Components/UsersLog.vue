<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-data-table
        :headers="[
          { title: 'User', key: 'user.name' },
          { title: 'Action', key: 'action' },
          { title: 'Time', key: 'action_time' }
        ]"
        :items="logs"
        :search="search"
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
      logs: [],
    };
  },
  created() {
    this.fetchLogs();
  },
  methods: {
    fetchLogs() {
      axios.get('/admin/users-log')
        .then(response => {
          this.logs = response.data.activityLog;
        })
        .catch(error => {
          console.error('Error fetching activity logs:', error);
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
}
</style>