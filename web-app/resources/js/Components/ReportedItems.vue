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
        :items="formattedItems"
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
        { text: 'Item Name', value: 'item_name', width: '25%' },  
        { text: 'Reporter', value: 'user.name', width: '20%' }, 
        { text: 'Date', value: 'date', width: '15%' },
        { text: 'Status', value: 'claim_status', width: '15%' },
      ],
      items: [],
    };
  },
  computed: {
    formattedItems() {
      return this.items.map(item => {
        return {
          ...item,
          date: item.found_date || item.lost_date, 
        };
      });
    },
  },
  created() {
    this.fetchLogs();
  },
  methods: {
    fetchLogs() {
      axios.get('/admin/reported-items') 
        .then(response => {
          this.items = [...response.data.lostItems, ...response.data.foundItems];
        })
        .catch(error => {
          console.error('Error fetching activity logs:', error);
        });
    }
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}
</style>
