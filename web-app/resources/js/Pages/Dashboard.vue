<template>
    <div>
      <!-- You can add HeaderBar and FooterBar here if needed -->
      
      
      <!-- The DashboardPage component is inserted here -->
      <DashboardPage />
      
      
    </div>
  </template>
  
  <script>
  // Import your components
  import HeaderBar from '@/components/HeaderBar.vue';
  import FooterBar from '@/components/FooterBar.vue';
  import DashboardPage from '@/components/DashboardPage.vue';
  
  export default {
    components: {
      HeaderBar,
      FooterBar,
      DashboardPage, // Register the DashboardPage component
    },
  };
  </script>
  
  <style scoped>
  /* You can add styles here if needed */
  </style>
  