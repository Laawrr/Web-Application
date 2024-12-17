<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline d-flex align-center">
        <v-spacer></v-spacer>
        <span>Users Management</span>
        <v-spacer></v-spacer>
        <v-btn
          style="background-color: #4fb9af; color: white"
          variant="flat"
          class="ml-2 d-flex align-center"
          @click="showAddDialog = true"
        >
          <span>Add User</span>
        </v-btn>
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
          { title: 'Name', key: 'name' },
          { title: 'Email', key: 'email' },
          { title: 'Role', key: 'role' },
          { title: 'Date Created', key: 'created_at' },
          { title: 'Actions', key: 'actions' }
        ]"
        :items="filteredUsers"
        :search="search"
        class="elevation-1"
        :items-per-page="10"
        v-model:page="page"
      >
        <template v-slot:item.actions="{ item }">
          <div class="d-flex align-center">
            <font-awesome-icon
              icon="fa-solid fa-pen-to-square"
              class="mr-5 action-icon"
              style="color: #4fb9af; cursor: pointer;"
              @click="editUser(item)"
            />
            <font-awesome-icon
              icon="fa-solid fa-trash"
              class="action-icon"
              style="color: #FF5252; cursor: pointer;"
              @click="deleteUser(item)"
            />
          </div>
        </template>
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
            <span>Page {{ page }} of {{ Math.ceil(filteredUsers.length / 10) }}</span>
            <v-btn
              style="background-color: #4fb9af; color: white; text-align: center"
              variant="flat"
              :disabled="page >= Math.ceil(filteredUsers.length / 10)"
              @click="page++"
            >
              Next
            </v-btn>
          </div>
        </template>
        <template v-slot:item.created_at="{ item }">
          <td>{{ formatDate(item.created_at) }}</td>
        </template>
      </v-data-table>

      <!-- Add User Dialog -->
      <v-dialog v-model="showAddDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 pa-4">Add New User</v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    label="Name"
                    v-model="newUser.name"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    label="Email"
                    v-model="newUser.email"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="showAddDialog = false"
            >
              Cancel
            </v-btn>
            <v-btn
              style="background-color: #4fb9af; color: white; text-align: center"
              variant="flat"
              @click="addUser"
            >
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Edit User Dialog -->
      <v-dialog v-model="showEditDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 pa-4">Edit User</v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    label="Name"
                    v-model="editedUser.name"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    label="Email"
                    v-model="editedUser.email"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="showEditDialog = false"
            >
              Cancel
            </v-btn>
            <v-btn
              style="background-color: #4fb9af; color: white; text-align: center"
              variant="flat"
              @click="saveEdit"
            >
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
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
        { 
          text: 'Name',
          value: 'name',
          width: '25%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        { 
          text: 'Email',
          value: 'email',
          width: '30%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        { 
          text: 'Role',
          value: 'role',
          width: '15%',
          align: 'start',
          sortable: true,
        },
        { 
          text: 'Date Created',
          value: 'created_at',
          width: '15%',
          align: 'start',
          sortable: true,
        },
        { 
          text: 'Actions',
          value: 'actions',
          width: '10%',
          align: 'center',
          sortable: false,
        },
      ],
      users: [],
      page: 1,
      showAddDialog: false,
      newUser: {
        name: '',
        email: '',
      },
      showEditDialog: false,
      editedUser: {
        name: '',
        email: '',
      },
    };
  },

  computed: {
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
          this.users = response.data.users;
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },

    formatDate(dateString) {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', options);
    },

    addUser() {
      console.log('Add user:', this.newUser);
      this.showAddDialog = false;
    },

    editUser(user) {
      this.editedUser = { ...user };
      this.showEditDialog = true;
    },

    saveEdit() {
      console.log('Save edit:', this.editedUser);
      this.showEditDialog = false;
    },

    deleteUser(user) {
      // Add delete confirmation dialog here
      console.log('Delete user:', user);
    },
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
}
</style>
