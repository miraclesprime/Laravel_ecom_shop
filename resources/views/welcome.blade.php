<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="ShoppyCart - Smart Shopping Platform">
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/shoppycart-logo.svg') }}">

        <title>ShoppyCart - Smart Shopping Made Easy</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <!-- Navigation -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('assets/shoppycart-logo.svg') }}" alt="ShoppyCart" class="h-8 w-8">
                            <span class="text-xl font-bold text-gray-900 dark:text-white">ShoppyCart</span>
                        </div>
                        @if (Route::has('login'))
                            <div class="flex items-center gap-3">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-700 dark:hover:text-indigo-300">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 font-semibold hover:text-indigo-600 dark:hover:text-indigo-400">
                                        Sign In
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                                            Get Started
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                        🛒 ShoppyCart
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-4">
                        Smart Shopping Platform with Real-time Inventory
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 mb-10">
                        Browse products, manage your cart, and get instant notifications when items go low on stock.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                                    Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                                    Start Shopping Free
                                </a>
                                <a href="{{ route('login') }}" class="px-8 py-3 border-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400 font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/10">
                                    Sign In
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="bg-gray-50 dark:bg-gray-800 py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center rounded-lg p-6 bg-white dark:bg-gray-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                            <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">
                                {{ \App\Models\Product::count() }}
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 font-medium">Products Available</p>
                        </div>
                        <div class="text-center rounded-lg p-6 bg-white dark:bg-gray-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                            <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">
                                {{ \App\Models\Sale::count() }}
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 font-medium">Completed Orders</p>
                        </div>
                        <div class="text-center rounded-lg p-6 bg-white dark:bg-gray-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                                {{ \App\Models\User::count() }}
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 font-medium">Active Users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">
                    Key Features
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-600">
                        <div class="text-3xl mb-4">🛍️</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Product Catalog
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Browse unlimited products with detailed information and real-time stock status.
                        </p>
                    </div>

                    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-600">
                        <div class="text-3xl mb-4">🛒</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Smart Cart
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Add and manage items with instant synchronization powered by Livewire.
                        </p>
                    </div>

                    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-600">
                        <div class="text-3xl mb-4">🔔</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Smart Alerts
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Get instant notifications for low stock items and daily sales reports.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tech Stack Section -->
            <div class="bg-gray-50 dark:bg-gray-800 py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-12">
                        Built with Modern Technology
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div class="text-center rounded-lg p-4 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white dark:hover:bg-gray-900 hover:shadow-sm">
                            <div class="text-4xl mb-3">🎗️</div>
                            <p class="font-semibold text-gray-900 dark:text-white">Laravel 12</p>
                        </div>
                        <div class="text-center rounded-lg p-4 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white dark:hover:bg-gray-900 hover:shadow-sm">
                            <div class="text-4xl mb-3">⚡</div>
                            <p class="font-semibold text-gray-900 dark:text-white">Livewire</p>
                        </div>
                        <div class="text-center rounded-lg p-4 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white dark:hover:bg-gray-900 hover:shadow-sm">
                            <div class="text-4xl mb-3">🎨</div>
                            <p class="font-semibold text-gray-900 dark:text-white">Tailwind CSS</p>
                        </div>
                        <div class="text-center rounded-lg p-4 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white dark:hover:bg-gray-900 hover:shadow-sm">
                            <div class="text-4xl mb-3">🚀</div>
                            <p class="font-semibold text-gray-900 dark:text-white">Alpine.js</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-indigo-600 dark:bg-indigo-700 py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                        Ready to Start Shopping?
                    </h2>
                    <p class="text-lg text-indigo-100 mb-8">
                        Join today and experience smart shopping with real-time inventory updates.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100">
                                    Go Shopping
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100">
                                    Create Account
                                </a>
                                <a href="{{ route('login') }}" class="px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10">
                                    Sign In
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-gray-900 dark:bg-black text-gray-400 py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <img src="{{ asset('assets/shoppycart-logo.svg') }}" alt="ShoppyCart" class="h-8 w-8">
                            <span class="font-bold text-white">ShoppyCart</span>
                        </div>
                        <p class="text-sm mb-4">Smart Shopping Platform with Real-time Inventory</p>
                        <p class="text-xs">
                            &copy; {{ date('Y') }} ShoppyCart. Built by <span class="font-semibold text-white">Jan Nowak</span>
                        </p>
                        <p class="text-xs mt-2">
                            Laravel {{ Illuminate\Foundation\Application::VERSION }} | PHP {{ PHP_VERSION }}
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
