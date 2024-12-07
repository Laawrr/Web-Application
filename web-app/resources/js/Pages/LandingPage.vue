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
                        <Link href="/" class="text-gray-600 hover:text-teal-500">Home</Link>
                        <a href="#features" class="text-gray-600 hover:text-teal-500">Features</a>
                        <a href="#contact" class="text-gray-600 hover:text-teal-500">Contact</a>
                        <button @click="showLoginModal = true"
                            class="bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition-colors">
                            Login / Sign Up
                        </button>
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

                        <!-- Form Container -->
                        <div class="relative">
                            <transition name="slide-fade" mode="out-in">
                                <form v-if="tab === 'login'" @submit.prevent="login" class="space-y-4" key="login">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" v-model="loginForm.email" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Password</label>
                                        <input type="password" v-model="loginForm.password" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>
                                    <div class="flex flex-col items-start space-y-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" v-model="loginForm.remember" id="remember"
                                                class="mr-2">
                                            <label for="remember" class="text-sm text-gray-600">Remember me</label>
                                        </div>
                                        <button type="submit" :disabled="processing"
                                            class="w-full bg-teal-500 text-white py-2 rounded-md hover:bg-teal-600 transition-all duration-200 ease-in-out flex items-center justify-center">
                                            <i v-if="processing" class="fas fa-spinner animate-spin mr-2"></i>
                                            {{ processing ? 'Processing...' : 'Login' }}
                                        </button>
                                    </div>
                                </form>

                                <form v-else @submit.prevent="register" class="space-y-4" key="register">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" v-model="registerForm.name" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" v-model="registerForm.email" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Password</label>
                                        <input type="password" v-model="registerForm.password" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                        <input type="password" v-model="registerForm.password_confirmation" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>
                                    <p v-if="passwordError" class="text-sm text-red-600">{{ passwordError }}</p>
                                    <button type="submit" :disabled="processing || !isPasswordValid"
                                        class="w-full bg-teal-500 text-white py-2 rounded-md hover:bg-teal-600 transition-all duration-200 ease-in-out flex items-center justify-center">
                                        <i v-if="processing" class="fas fa-spinner animate-spin mr-2"></i>
                                        {{ processing ? 'Processing...' : 'Register' }}
                                    </button>

                                    <div class="text-center">
                                        <p class="text-sm text-gray-600 mb-2">or</p>
                                        <button @click="googleLogin"
                                            class="w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-50 transition-all duration-200 ease-in-out flex items-center justify-center hover:shadow-md">
                                            <i class="fab fa-google mr-2"></i>
                                            Continue with Google
                                        </button>
                                    </div>
                                </form>
                            </transition>

                            <!-- Google Login and Close Buttons -->
                            <div class="mt-4 space-y-4">


                                <div>
                                    <p v-if="error" class="text-sm text-red-600 mb-4">{{ error }}</p>
                                    <button @click="showLoginModal = false"
                                        class="w-full border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50 transition-all duration-200 ease-in-out hover:shadow-md">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const showLoginModal = ref(false);
const tab = ref('login');
const error = ref('');
const processing = ref(false);
const currentSlide = ref(0);

const slides = [
    'img/image5.png',
    'img/image3.png',
    'img/image4.png',
    'img/image6.png'
];

const features = ref([
    {
        title: "Smart Search",
        description: "Find lost items quickly with our advanced search system",
        icon: "fas fa-search"
    },
    {
        title: "Location Tracking",
        description: "Track and locate items with precise location data",
        icon: "fas fa-map-marker-alt"
    },
    {
        title: "Community Support",
        description: "Connect with helpful community members",
        icon: "fas fa-users"
    }
]);

const loginForm = ref({
    email: '',
    password: '',
    remember: false
});

const registerForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const passwordError = computed(() => {
    if (!registerForm.value.password || !registerForm.value.password_confirmation) {
        return '';
    }
    if (registerForm.value.password !== registerForm.value.password_confirmation) {
        return 'Passwords do not match';
    }
    return '';
});

const isPasswordValid = computed(() => {
    return registerForm.value.password &&
        registerForm.value.password_confirmation &&
        registerForm.value.password === registerForm.value.password_confirmation;
});

const googleLogin = () => {
    window.location.href = route('auth.google');
};

const login = () => {
    if (processing.value) return;

    processing.value = true;
    error.value = '';

    router.post(route('login'), loginForm.value, {
        onSuccess: () => {
            showLoginModal.value = false;
            router.visit(route('dashboard'));
        },
        onError: (errors) => {
            error.value = Object.values(errors)[0];
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
        preserveScroll: true
    });
};

const register = () => {
    if (processing.value || !isPasswordValid.value) return;

    processing.value = true;
    error.value = '';

    router.post(route('register'), registerForm.value, {
        onSuccess: () => {
            showLoginModal.value = false;
            router.visit(route('dashboard'));
        },
        onError: (errors) => {
            error.value = Object.values(errors)[0];
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
        preserveScroll: true
    });
};

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.length;
};

onMounted(() => {
    // Auto-advance slides every 5 seconds
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
