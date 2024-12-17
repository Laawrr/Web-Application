<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline">
        Users Management
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
        :items="filteredUsers"
        :search="search"
        class="elevation-1"
      >
        <template v-slot:item.created_at="{ item }">
          <td>{{ formatDate(item.created_at) }}</td>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UsersView',
  data() {
    return {
      search: '',
      headers: [
        { text: 'Name', value: 'name', width: '25%' },
        { text: 'Email', value: 'email', width: '30%' },
        { text: 'Role', value: 'role', width: '15%' },
        { text: 'Date-created', value: 'created_at', width: '15%' },
      ],
      users: [],
    };
  },

  computed: {
    // Filter users based on search query
    filteredUsers() {
      return this.users.filter(user =>
        user.name.toLowerCase().includes(this.search.toLowerCase()) ||
        user.email.toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },

  created() {
    this.fetchLogs();
  },

  methods: {
    fetchLogs() {
      axios.get('/admin/users')
        .then(response => {
          this.users = response.data.users; // Assuming 'users' is the correct key
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },

    // Method to format date in a readable format
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', options); // Adjust locale if needed
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
