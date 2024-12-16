<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline">
        Reported Items
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
        :items="items"
        :search="search"
        class="elevation-1"
      ></v-data-table>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: 'ReportedItems',
  data() {
    return {
      search: '',
      headers: [
        { text: 'Item Name', value: 'name', width: '25%' },
        { text: 'Reporter', value: 'reporter', width: '20%' },
        { text: 'Date', value: 'date', width: '15%' },
        { text: 'Status', value: 'status', width: '15%' },
        { text: 'Actions', value: 'actions', width: '25%' },
      ],
      items: [
        { name: 'Lost Phone', reporter: 'John Doe', date: '2023-12-16', status: 'Pending' },
        { name: 'Found Wallet', reporter: 'Jane Smith', date: '2023-12-15', status: 'Resolved' },
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