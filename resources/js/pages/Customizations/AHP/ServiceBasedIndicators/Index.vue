<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Button from '@/components/ui/button/Button.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';

const props = defineProps<{
  project: IProject,
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: props.project.app_title,
    href: '/project/' + props.project.project_id,
  },
  {
    title: 'Custom Packages',
    href: '/packages/project/' + props.project.project_id + '/dashboard',
  },
  {
    title: 'Service Based Indicators',
    href: ''
  }
];

// Define color variables
const cardColors = {
  maternal: { bg: 'bg-pink-50', border: 'border-pink-500', text: 'text-pink-700' },
  reproductive: { bg: 'bg-purple-50', border: 'border-purple-500', text: 'text-purple-700' },
  hiv: { bg: 'bg-teal-50', border: 'border-teal-500', text: 'text-teal-700' },
  mental: { bg: 'bg-indigo-50', border: 'border-indigo-500', text: 'text-indigo-700' },
  prep: { bg: 'bg-amber-50', border: 'border-amber-500', text: 'text-amber-700' },
};
</script>

<template>

  <Head title="AHP Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-7xl mx-auto mt-10">
      <!-- Header -->
      <div class="mb-10 text-center">
        <h1
          class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3">
          Facility-Based Service Delivery Indicators
        </h1>
        <p class="text-gray-600 max-w-3xl mx-auto">
          Comprehensive analytics platform for monitoring and improving healthcare service delivery metrics across key
          health domains
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Maternal Health Card -->
        <Card class=" delay-100 transition-all duration-300 ease-in-out">
          <CardHeader>
            <div class="flex items-start">
              <div class="p-3 rounded-lg mr-4 bg-pink-100">
                <i class="fas fa-baby text-pink-600 text-xl"></i>
              </div>
              <CardTitle class="text-xl font-bold text-gray-800">
                Maternal Health (Adolescent-focused)
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-gray-600 mb-4">
              Track adolescent-focused maternal health services including ANC bookings, follow-ups, and postnatal care
              visits.
            </p>
            <Link :href="route('packages.service_based_indicators.maternal_health', [props.project.project_id])">
            <Button class="w-full cursor-pointer text-pink-600 hover:bg-pink-300 font-bold"
              :class="cardColors.maternal.bg">
              View Analysis <i class="fas fa-arrow-right ml-2"></i>
            </Button>
            </Link>

          </CardContent>
        </Card>

        <!-- Sexual and Reproductive Health Card -->
        <Card class=" delay-200 transition-all duration-300 ease-in-out">
          <CardHeader>
            <div class="flex items-start">
              <div class="p-3 rounded-lg mr-4 bg-purple-100">
                <i class="fas fa-heart text-purple-600 text-xl"></i>
              </div>
              <CardTitle class="text-xl font-bold text-gray-800">
                Sexual and Reproductive Health
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-gray-600 mb-4">
              Monitor STI screenings, treatments, and contraception services for comprehensive reproductive health
              tracking.
            </p>
            <Link
              :href="route('packages.service_based_indicators.sexual_and_repro_health', [props.project.project_id])">
            <Button class="w-full cursor-pointer text-purple-600 hover:bg-purple-300 font-bold"
              :class="cardColors.reproductive.bg">
              View Analysis <i class="fas fa-arrow-right ml-2"></i>
            </Button>
            </Link>
          </CardContent>
        </Card>

        <!-- HIV Services Card -->
        <Card class=" delay-300 transition-all duration-300 ease-in-out">
          <CardHeader>
            <div class="flex items-start">
              <div class="p-3 rounded-lg mr-4 bg-teal-100">
                <i class="fas fa-virus text-teal-600 text-xl"></i>
              </div>
              <CardTitle class="text-xl font-bold text-gray-800">
                HIV Services
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-gray-600 mb-4">
              Comprehensive HIV services tracking including testing, ART initiation, retention, and viral load
              suppression.
            </p>
            <Button class="w-full cursor-pointer text-teal-600 hover:bg-teal-300 font-bold" :class="cardColors.hiv.bg">
              View Analysis <i class="fas fa-arrow-right ml-2"></i>
            </Button>
          </CardContent>
        </Card>

        <!-- Mental Health Card -->
        <Card class=" delay-100 transition-all duration-300 ease-in-out">
          <CardHeader>
            <div class="flex items-start">
              <div class="p-3 rounded-lg mr-4 bg-indigo-100">
                <i class="fas fa-brain text-indigo-600 text-xl"></i>
              </div>
              <CardTitle class="text-xl font-bold text-gray-800">
                Mental Health
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-gray-600 mb-4">
              Mental health screenings, diagnosis of depression/anxiety, and referral tracking for comprehensive care.
            </p>
            <Button class="w-full cursor-pointer text-indigo-600 hover:bg-indigo-300 font-bold"
              :class="cardColors.mental.bg">
              View Analysis <i class="fas fa-arrow-right ml-2"></i>
            </Button>
          </CardContent>
        </Card>

        <!-- PrEP Services Card -->
        <Card class=" delay-200 transition-all duration-300 ease-in-out">
          <CardHeader>
            <div class="flex items-start">
              <div class="p-3 rounded-lg mr-4 bg-amber-100">
                <i class="fas fa-shield-alt text-amber-600 text-xl"></i>
              </div>
              <CardTitle class="text-xl font-bold text-gray-800">
                PrEP Services
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-gray-600 mb-4">
              PrEP eligibility screening, initiation, and continuation rate monitoring for effective prevention
              programs.
            </p>
            <Button class="w-full cursor-pointer text-amber-600 hover:bg-amber-300 font-bold"
              :class="cardColors.prep.bg">
              View Analysis <i class="fas fa-arrow-right ml-2"></i>
            </Button>
          </CardContent>
        </Card>

        <!-- Summary Card -->
        <Card
          class=" delay-300 transition-all duration-300 ease-in-out bg-gradient-to-br from-purple-600 to-blue-600 text-white">
          <CardContent class="flex flex-col h-full py-6">
            <div class="mb-4">
              <div class="bg-white/20 p-3 rounded-lg inline-block mb-3">
                <i class="fas fa-chart-line text-xl"></i>
              </div>
              <h2 class="text-xl font-bold mb-2">Comprehensive Analytics Dashboard</h2>
              <p class="opacity-90">
                Access real-time insights, trends, and performance metrics across all health service domains in one
                unified platform.
              </p>
            </div>

            <div class="mt-auto space-y-3">
              <div class="flex items-center">
                <i class="fas fa-sync-alt mr-2 text-purple-200"></i>
                <span>Real-time data updates</span>
              </div>
              <div class="flex items-center">
                <i class="fas fa-download mr-2 text-purple-200"></i>
                <span>Exportable reports</span>
              </div>
              <div class="flex items-center">
                <i class="fas fa-bell mr-2 text-purple-200"></i>
                <span>Customizable alerts</span>
              </div>
            </div>

            <Button class="mt-6 bg-white text-purple-600 hover:bg-purple-50 font-bold">
              Explore All Metrics <i class="fas fa-arrow-right ml-2"></i>
            </Button>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Activity -->
      <!-- <Card class="mt-12 animate__animated animate__fadeInUp">
          <CardHeader>
            <div class="flex justify-between items-center">
              <CardTitle class="text-xl font-bold text-gray-800">Recent Data Updates</CardTitle>
              <Button variant="link" class="text-blue-600 p-0">
                View All <i class="fas fa-chevron-right ml-1 text-sm"></i>
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center">
                  <div class="bg-green-100 text-green-800 p-2 rounded-lg mr-3">
                    <i class="fas fa-file-medical"></i>
                  </div>
                  <div>
                    <h3 class="font-medium">Maternal Health Data</h3>
                    <p class="text-sm text-gray-500">Updated 2 hours ago</p>
                  </div>
                </div>
                <div class="text-green-600 font-medium flex items-center">
                  +12% <i class="fas fa-arrow-up ml-1 text-xs"></i>
                </div>
              </div>
              
              <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center">
                  <div class="bg-purple-100 text-purple-800 p-2 rounded-lg mr-3">
                    <i class="fas fa-heartbeat"></i>
                  </div>
                  <div>
                    <h3 class="font-medium">HIV Screening Results</h3>
                    <p class="text-sm text-gray-500">Updated yesterday</p>
                  </div>
                </div>
                <div class="text-red-600 font-medium flex items-center">
                  -3% <i class="fas fa-arrow-down ml-1 text-xs"></i>
                </div>
              </div>
              
              <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center">
                  <div class="bg-blue-100 text-blue-800 p-2 rounded-lg mr-3">
                    <i class="fas fa-user-shield"></i>
                  </div>
                  <div>
                    <h3 class="font-medium">PrEP Eligibility Screening</h3>
                    <p class="text-sm text-gray-500">Updated 3 days ago</p>
                  </div>
                </div>
                <div class="text-green-600 font-medium flex items-center">
                  +22% <i class="fas fa-arrow-up ml-1 text-xs"></i>
                </div>
              </div>
            </div>
          </CardContent>
        </Card> -->
    </div>
  </AppLayout>
</template>

<style scoped>
/* .transition-all duration-300 ease-in-out {
  @apply transition-all duration-300 ease-in-out;
}

.transition-all duration-300 ease-in-out:hover {
  @apply transform -translate-y-1 shadow-xl;
} */

. {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.delay-100 {
  animation-delay: 0.1s;
}

.delay-200 {
  animation-delay: 0.2s;
}

.delay-300 {
  animation-delay: 0.3s;
}

/* Ensure Font Awesome icons are visible */
:deep() .fas {
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
}
</style>