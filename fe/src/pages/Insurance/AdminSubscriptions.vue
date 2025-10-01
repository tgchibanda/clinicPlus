<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Insurance Subscriptions</h1>
        <p class="text-gray-600 mt-2">Manage and monitor all insurance subscriptions</p>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Name, phone, or ID"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              @change="fetchSubscriptions"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="active">Active</option>
              <option value="lapsed">Lapsed</option>
              <option value="closed">Closed</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              @change="fetchSubscriptions"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              @change="fetchSubscriptions"
            />
          </div>
        </div>

        <div class="mt-4 flex justify-between items-center">
          <button
            @click="clearFilters"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900"
          >
            Clear Filters
          </button>
          <button
            @click="exportReport"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm"
          >
            Export CSV
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-sm text-gray-600 mb-1">Total Subscriptions</div>
          <div class="text-3xl font-bold text-gray-900">{{ stats.total }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-sm text-gray-600 mb-1">Active</div>
          <div class="text-3xl font-bold text-green-600">{{ stats.active }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-sm text-gray-600 mb-1">Lapsed</div>
          <div class="text-3xl font-bold text-orange-600">{{ stats.lapsed }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-sm text-gray-600 mb-1">Total Revenue</div>
          <div class="text-3xl font-bold text-blue-600">${{ stats.revenue }}</div>
        </div>
      </div>

      <!-- Subscriptions Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Due</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="8" class="px-6 py-8 text-gray-500">Loading...</td>
              </tr>
              <tr v-else-if="subscriptions.length === 0" class="text-center">
                <td colspan="8" class="px-6 py-8 text-gray-500">No subscriptions found</td>
              </tr>
              <tr
                v-else
                v-for="subscription in subscriptions"
                :key="subscription.id"
                class="hover:bg-gray-50 cursor-pointer"
                @click="viewSubscription(subscription)"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  #{{ subscription.id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ subscription.patient.first_name }} {{ subscription.patient.last_name }}
                  </div>
                  <div class="text-sm text-gray-500">{{ subscription.patient.phone }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ subscription.plan?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="statusClass(subscription.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ subscription.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(subscription.started_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(subscription.next_due_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                  ${{ subscription.total_paid_amount }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button
                    @click.stop="viewSubscription(subscription)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 rounded disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 border border-gray-300 rounded disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View/Edit Modal -->
    <div
      v-if="selectedSubscription"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
          <h2 class="text-2xl font-bold">Subscription #{{ selectedSubscription.id }}</h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="p-6">
          <!-- Subscription Details -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Subscription Details</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Patient:</span>
                <span class="ml-2 font-medium">
                  {{ selectedSubscription.patient.first_name }} {{ selectedSubscription.patient.last_name }}
                </span>
              </div>
              <div>
                <span class="text-gray-600">Phone:</span>
                <span class="ml-2 font-medium">{{ selectedSubscription.patient.phone }}</span>
              </div>
              <div>
                <span class="text-gray-600">Plan:</span>
                <span class="ml-2 font-medium">{{ selectedSubscription.plan?.name }}</span>
              </div>
              <div>
                <span class="text-gray-600">Status:</span>
                <span :class="statusClass(selectedSubscription.status)" class="ml-2 px-2 py-1 text-xs font-semibold rounded-full">
                  {{ selectedSubscription.status }}
                </span>
            </div>
            </div>
            </div>
            </div>
            </div>
            </div>
            </div>
            </template>
            