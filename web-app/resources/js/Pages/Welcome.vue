<template>

    <Head title="Finder" />
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <img src="img/image2.png" alt="Logo" class="w-10 h-10">
                        <span class="text-xl font-bold text-gray-800">Lost Finder</span>
                    </div>

                    <div class="flex items-center space-x-6">
                        <router-link to="/" class="text-gray-600 hover:text-teal-500">Home</router-link>
                        <a href="#features" class="text-gray-600 hover:text-teal-500">Features</a>
                        <a href="#contact" class="text-gray-600 hover:text-teal-500">Contact</a>

                        <!-- Show Login/Sign Up if not authenticated -->
                        <button v-if="!isAuthenticated" @click="showLoginModal = true"
                            class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition-colors">
                            Login / Sign Up
                        </button>

                        <!-- Show Logout if authenticated -->
                        <form v-if="isAuthenticated" @submit.prevent="logout">
                            <button type="submit"
                                class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative">
            <!-- Carousel Background -->
            <div class="absolute inset-0 overflow-hidden">
                <transition-group name="fade">
                    <img v-for="(slide, index) in slides" :key="slide" :src="slide" v-show="currentSlide === index"
                        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000"
                        alt="Background Image">
                </transition-group>
                <!-- Overlay to ensure text readability -->
                <div class="absolute inset-0 bg-gradient-to-r from-teal-500/80 to-teal-600/80"></div>
            </div>

            <!-- Content -->
            <div class="relative text-white py-40">
                <div class="container mx-auto px-4 text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">Lost & Found Made Easy</h1>
                    <p class="text-xl mb-8 max-w-2xl mx-auto">Find what you've lost or help others recover their
                        belongings.</p>
                    <button @click="showLoginModal = true"
                        class="bg-white text-teal-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        Get Started
                    </button>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="py-16" style="background-color: #F5F5F5">
            <div class="container mx-auto px-4">
                <h2 class="text-4xl font-bold text-center mb-12" style="color: #333333">Why Choose Us</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Easy to Use Card -->
                    <div class="p-6 rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105"
                        style="background-color: #ADD8E6">
                        <div class="text-3xl mb-4" style="color: #008080"><i class="fas fa-user-friends"></i></div>
                        <h3 class="text-xl font-semibold mb-2" style="color: #333333">Easy to Use</h3>
                        <p style="color: #333333">Our platform is designed with simplicity in mind, making it easy for
                            anyone to report or find lost items.</p>
                    </div>
                    <!-- Quick Results Card -->
                    <div class="p-6 rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105"
                        style="background-color: #FFFACD">
                        <div class="text-3xl mb-4" style="color: #FF7F50"><i class="fas fa-bolt"></i></div>
                        <h3 class="text-xl font-semibold mb-2" style="color: #333333">Quick Results</h3>
                        <p style="color: #333333">Get instant notifications and quick matches for your lost items
                            through our efficient matching system.</p>
                    </div>
                    <!-- Secure Platform Card -->
                    <div class="p-6 rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105"
                        style="background-color: #ADD8E6">
                        <div class="text-3xl mb-4" style="color: #008080"><i class="fas fa-shield-alt"></i></div>
                        <h3 class="text-xl font-semibold mb-2" style="color: #333333">Secure Platform</h3>
                        <p style="color: #333333">Your data and item information are protected with top-notch security
                            measures and encryption.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 bg-[#219B9D] text-white">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-8">Ready to Get Started?</h2>
                <p class="text-xl mb-8">Join our community and start finding lost items today.</p>
                <button @click="showLoginModal = true"
                    class="bg-teal-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-teal-600 transition-colors">
                    Join Now
                </button>
            </div>
        </section>



        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-8">
            <div class="container mx-auto px-4 text-center">
                <p>&copy; 2024 Lost Finder. All rights reserved.</p>
            </div>
        </footer>

        <!-- Login Modal -->
        <transition name="modal">
            <div v-if="showLoginModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300 ease-in-out"
                        @click="showLoginModal = false"></div>
                    <div
                        class="relative bg-white rounded-lg w-full max-w-md p-6 transform transition-all duration-300 ease-in-out">
                        <!-- Tabs -->
                        <div class="flex border-b mb-6">
                            <button @click="tab = 'login'"
                                :class="['px-4 py-2 transition-colors duration-200 ease-in-out', tab === 'login' ? 'border-b-2 border-teal-500 text-teal-600' : 'text-gray-500']">
                                Login
                            </button>
                            <button @click="tab = 'register'"
                                :class="['px-4 py-2 transition-colors duration-200 ease-in-out', tab === 'register' ? 'border-b-2 border-teal-500 text-teal-600' : 'text-gray-500']">
                                Register
                            </button>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <transition name="slide-fade" mode="out-in">
                        <div>
                            <Login />
                        </div>
                    </transition>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Login from './Auth/Login.vue';

const isAuthenticated = ref(false); 
const showLoginModal = ref(false);
const tab = ref('login');
const currentSlide = ref(0);

const slides = ['img/image5.png', 'img/image3.png', 'img/image4.png', 'img/image6.png'];

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.length;
};

onMounted(() => {
    setInterval(nextSlide, 5000);
});
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 3.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all .5s ease;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

input {
    transition: all 0.2s ease-in-out;
}

input:focus {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

button {
    transition: all 0.2s ease-in-out;
}

button:hover:not(:disabled) {
    transform: translateY(-1px);
}

button:active:not(:disabled) {
    transform: translateY(0);
}

/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

/* Slide Fade Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>

<style>
@import '@fortawesome/fontawesome-free/css/all.css';

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card:hover {
    transform: translateY(-4px);
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

html {
    scroll-behavior: smooth;
}
</style>
