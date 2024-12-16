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
        :items="users"
        :search="search"
        class="elevation-1"
      ></v-data-table>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: 'UsersView',
  data() {
    return {
      search: '',
      headers: [
        { text: 'Name', value: 'name', width: '25%' },
        { text: 'Email', value: 'email', width: '30%' },
        { text: 'Role', value: 'role', width: '15%' },
        { text: 'Status', value: 'status', width: '15%' },
        { text: 'Actions', value: 'actions', width: '15%' },
      ],
      users: [
        { name: 'John Doe', email: 'john@example.com', role: 'Admin', status: 'Active' },
        { name: 'Jane Smith', email: 'jane@example.com', role: 'User', status: 'Active' },
      ],
    }
  },
}
</script>

<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}
</style>