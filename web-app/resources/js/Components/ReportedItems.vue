<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline d-flex align-center">
        <v-spacer></v-spacer>
        <span>Reported Items</span>
        <v-spacer></v-spacer>
      </v-card-title>
      <v-text-field
        v-model="search"
        append-icon="mdi-magnify"
        label="Search"
        single-line
        hide-details
        class="mx-4"
        style="max-width: 300px;"
      ></v-text-field>
      <v-divider></v-divider>
      <v-data-table
        :headers="[
          { title: 'Item Name', key: 'item_name' },
          { title: 'Reporter', key: 'user.name' },
          { title: 'Status', key: 'status' },
          { title: 'Type', key: 'type' },
          { title: 'Date', key: 'created_at' }
        ]"
        :items="items"
        :search="search"
        :items-per-page="10"
      >
        <template v-slot:bottom="props">
          <div class="d-flex align-center justify-center pa-4 gap-4">
            <v-btn
              style="background-color: #4fb9af; color: white; text-align: center"
              variant="flat"
              :disabled="page === 1"
              @click="page--"
            >
              Previous
            </v-btn>
            <span>Page {{ page }} of {{ Math.ceil(items.length / 10) }}</span>
            <v-btn
              style="background-color: #4fb9af; color: white; text-align: center"
              variant="flat"
              :disabled="page >= Math.ceil(items.length / 10)"
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
  name: 'ReportedItems',
  data() {
    return {
      search: '',
      page: 1,
      items: [],
    };
  },
  created() {
    this.fetchItems();
  },
  methods: {
    fetchItems() {
      axios.get('/admin/reported-items')
        .then(response => {
          this.items = [...response.data.lostItems, ...response.data.foundItems];
        })
        .catch(error => {
          console.error('Error fetching reported items:', error);
        });
    },
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
}
</style>
