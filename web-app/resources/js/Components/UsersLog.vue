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
export default {
  name: 'UsersLog',
  data() {
    return {
      search: '',
      headers: [
        { text: 'User', value: 'user', width: '20%' },
        { text: 'Action', value: 'action', width: '20%' },
        { text: 'Date', value: 'date', width: '20%' },
        { text: 'Details', value: 'details', width: '40%' },
      ],
      logs: [
        { user: 'John Doe', action: 'Login', date: '2023-12-16', details: 'Successful login' },
        { user: 'Jane Smith', action: 'Update Profile', date: '2023-12-15', details: 'Changed password' },
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