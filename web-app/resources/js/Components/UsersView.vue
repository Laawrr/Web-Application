<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline">
        User Activity Log
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
          class="mx-4"
          style="max-width: 300px;"
        ></v-text-field>
      </v-card-title>
      <v-divider></v-divider>
      <v-data-table
        :headers="headers"
        :items="logs"
        :search="search"
        class="elevation-1"
      ></v-data-table>
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
        { text: 'Name', value: 'user.name', width: '20%' }, 
        { text: 'Action', value: 'action', width: '20%' },
        { text: 'Time', value: 'action_time', width: '20%' },
      ],
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
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}
</style>
