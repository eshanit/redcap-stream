<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <Head title="Welcome to Redcap Stream">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
   
    <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800 p-6 lg:p-8">
        <!-- Header -->
        <header class="max-w-7xl mx-auto w-full mb-12 lg:mb-16">
            <nav class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg"></div>
                    <span class="font-semibold text-slate-900 dark:text-white">Redcap Stream</span>
                </div>
               
                <div class="flex items-center gap-3">
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-6 py-2.5 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-medium hover:shadow-lg transition-all duration-300 hover:scale-105"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-6 py-2.5 rounded-xl text-slate-700 dark:text-slate-300 font-medium hover:text-slate-900 dark:hover:text-white transition-colors duration-300"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-105"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <div class="flex items-center justify-center min-h-[70vh]">
            <main class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Text Content -->
                <div class="space-y-8">
                    <div class="space-y-4">
                        <div class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium mb-2">
                            Research Data Analytics Platform
                        </div>
                        <h1 class="text-4xl lg:text-6xl font-bold text-slate-900 dark:text-white leading-tight">
                            Transform Your
                            <span class="bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text text-transparent">Research Data</span>
                        </h1>
                        <p class="text-xl text-slate-600 dark:text-slate-300 leading-relaxed">
                            Redcap Stream is a powerful analytics platform that transforms your Redcap research data into actionable insights.
                            Generate comprehensive reports, visualize trends, and accelerate your research with automated data processing and analysis.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <Link
                            :href="route('register')"
                            class="px-8 py-4 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-semibold hover:shadow-xl transition-all duration-300 hover:scale-105 text-center"
                        >
                            Start Analyzing
                        </Link>
                        <Link
                            :href="route('login')"
                            class="px-8 py-4 rounded-xl bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-semibold hover:shadow-lg transition-all duration-300 text-center"
                        >
                            Register
                        </Link>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-8 pt-8 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900 dark:text-white">95%</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Time Saved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900 dark:text-white">5+</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Studies</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900 dark:text-white">99.9%</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Uptime</div>
                        </div>
                    </div>
                </div>

                <!-- Visual Element -->
                <div class="relative">
                    <div class="relative z-10 bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-200 dark:border-slate-700">
                        <div class="space-y-6">
                            <!-- Mock chart -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <div class="h-3 bg-slate-200 dark:bg-slate-600 rounded-full w-3/4"></div>
                                    <div class="h-3 bg-slate-200 dark:bg-slate-600 rounded-full w-1/5"></div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="h-3 bg-red-500 rounded-full w-4/5"></div>
                                    <div class="h-3 bg-red-500 rounded-full w-1/4"></div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="h-3 bg-orange-500 rounded-full w-2/3"></div>
                                    <div class="h-3 bg-orange-500 rounded-full w-1/3"></div>
                                </div>
                            </div>
                           
                            <!-- Mock data cards -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-4">
                                    <div class="text-sm text-slate-500 dark:text-slate-400">Data Sources</div>
                                    <div class="text-xl font-bold text-slate-900 dark:text-white">24</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-4">
                                    <div class="text-sm text-slate-500 dark:text-slate-400">Reports Generated</div>
                                    <div class="text-xl font-bold text-slate-900 dark:text-white">1,284</div>
                                </div>
                            </div>
                           
                            <!-- Report preview -->
                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">Recent Report</div>
                                    <div class="text-xs text-slate-500">Cardiovascular Study</div>
                                </div>
                                <div class="h-2 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-red-500 to-orange-500 w-3/4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    <!-- Background decorative elements -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl opacity-20 blur-xl"></div>
                    <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl opacity-20 blur-xl"></div>
                </div>
            </main>
        </div>

        <!-- Features Section -->
        <section class="max-w-7xl mx-auto w-full mt-24">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-4">Powerful Research Analytics</h2>
                <p class="text-xl text-slate-600 dark:text-slate-300 max-w-3xl mx-auto">Streamline your research workflow with automated data processing, visualization, and reporting.</p>
            </div>
           
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Automated Reporting</h3>
                    <p class="text-slate-600 dark:text-slate-300">Generate comprehensive reports from your Redcap data with customizable templates and automated scheduling.</p>
                </div>
               
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Data Visualization</h3>
                    <p class="text-slate-600 dark:text-slate-300">Transform complex research data into intuitive charts, graphs, and dashboards for better insights.</p>
                </div>
               
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Data Security</h3>
                    <p class="text-slate-600 dark:text-slate-300">Enterprise-grade security with encrypted data transfer and storage, compliant with research ethics standards.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="max-w-7xl mx-auto w-full mt-24 text-center text-slate-500 dark:text-slate-400 text-sm">
            <p>© 2024 Redcap Stream. Advanced research analytics for accelerating health discoveries.</p>
        </footer>
    </div>
</template>